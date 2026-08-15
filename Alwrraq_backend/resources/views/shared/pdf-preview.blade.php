@php
    $pdfJsLibraryUrl = route('document-viewer.asset', ['asset' => 'pdf.min.js'], false);
    $pdfJsWorkerUrl = route('document-viewer.asset', ['asset' => 'pdf.worker.min.js'], false);
@endphp
<script src="{{ $pdfJsLibraryUrl }}"></script>
<script>
    window.addEventListener('load', async () => {
        const preview = document.getElementById(@json($pdfPreviewId));
        const status = document.getElementById(@json($pdfStatusId));
        if (!preview || !status) return;

        let previewFailed = false;
        const showFailure = () => {
            if (previewFailed) return;
            previewFailed = true;
            const box = document.createElement('div');
            box.className = 'pdf-status';
            const message = document.createElement('p');
            message.textContent = @json($pdfErrorMessage ?? 'تعذر عرض ملف PDF. حاول مرة أخرى.');
            message.style.margin = '0 0 14px';
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.textContent = 'إعادة المحاولة';
            retry.style.cssText = 'border:0;border-radius:9px;background:#0f4c81;color:#fff;padding:10px 16px;font:inherit;font-weight:900;cursor:pointer;';
            retry.addEventListener('click', () => window.location.reload());
            box.append(message, retry);
            preview.replaceChildren(box);
        };

        try {
            // Keep the authenticated PDF request on the exact WebView origin.
            // Laravel may be configured with "localhost" while the app is opened
            // through "127.0.0.1", which browsers correctly treat as two origins.
            const configuredPdfUrl = new URL(@json($pdfUrl), window.location.href);
            const sameOriginPdfUrl = `${configuredPdfUrl.pathname}${configuredPdfUrl.search}${configuredPdfUrl.hash}`;
            if (typeof window.pdfjsLib === 'undefined') {
                showFailure();
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = @json($pdfJsWorkerUrl);
            const response = await fetch(sameOriginPdfUrl, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'application/pdf' },
            });
            if (!response.ok) {
                throw new Error(`PDF request failed: ${response.status}`);
            }

            const pdfBytes = new Uint8Array(await response.arrayBuffer());
            const pdf = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
            const previewStyle = window.getComputedStyle(preview);
            const horizontalPadding =
                (Number.parseFloat(previewStyle.paddingLeft) || 0)
                + (Number.parseFloat(previewStyle.paddingRight) || 0);
            const availableWidth = Math.max(
                1,
                Math.floor(preview.clientWidth - horizontalPadding)
            );
            const compactViewer = window.matchMedia('(max-width: 860px), (pointer: coarse)').matches
                || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            const pixelRatio = Math.min(window.devicePixelRatio || 1, compactViewer ? 1.35 : 2);

            status.remove();

            const canvases = [];
            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const canvas = document.createElement('canvas');
                canvas.className = 'pdf-page';
                // PDF.js receives positioned glyphs from the PDF itself. Letting
                // the canvas inherit RTL from the Arabic page reverses glyph runs
                // in some Chromium/WebView versions and corrupts Arabic documents.
                canvas.setAttribute('dir', 'ltr');
                canvas.style.direction = 'ltr';
                canvas.dataset.page = String(pageNumber);
                canvas.style.width = `${availableWidth}px`;
                canvas.style.height = `${Math.round(availableWidth * 1.414)}px`;
                preview.appendChild(canvas);
                canvases.push(canvas);
            }

            const renderPage = async (canvas) => {
                if (previewFailed || !canvas || canvas.dataset.rendering === '1' || canvas.dataset.rendered === '1') return;
                canvas.dataset.rendering = '1';
                const page = await pdf.getPage(Number(canvas.dataset.page));
                const baseViewport = page.getViewport({ scale: 1 });
                const viewport = page.getViewport({ scale: availableWidth / baseViewport.width });
                const context = canvas.getContext('2d');
                context.direction = 'ltr';

                canvas.width = Math.floor(viewport.width * pixelRatio);
                canvas.height = Math.floor(viewport.height * pixelRatio);
                canvas.style.width = `${Math.floor(viewport.width)}px`;
                canvas.style.height = `${Math.floor(viewport.height)}px`;

                await page.render({
                    canvasContext: context,
                    viewport,
                    transform: pixelRatio === 1 ? null : [pixelRatio, 0, 0, pixelRatio, 0, 0],
                }).promise;
                page.cleanup();
                canvas.dataset.rendered = '1';
                delete canvas.dataset.rendering;
            };

            // Render one page at a time. Rendering every page together can exhaust
            // a mobile WebView's memory and leave the PDF viewer blank.
            let renderQueue = Promise.resolve();
            const queuePage = (canvas) => {
                if (!canvas || canvas.dataset.queued === '1' || canvas.dataset.rendered === '1') return;
                canvas.dataset.queued = '1';
                renderQueue = renderQueue
                    .then(() => renderPage(canvas))
                    .catch(() => showFailure());
            };

            const previewOwnsScroll = preview.scrollHeight > preview.clientHeight + 8;
            const observer = 'IntersectionObserver' in window
                ? new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            queuePage(entry.target);
                            if (!compactViewer) observer.unobserve(entry.target);
                            return;
                        }

                        if (compactViewer && entry.target.dataset.rendered === '1') {
                            entry.target.width = 1;
                            entry.target.height = 1;
                            delete entry.target.dataset.rendered;
                            delete entry.target.dataset.queued;
                        }
                    });
                }, { root: previewOwnsScroll ? preview : null, rootMargin: '700px 0px' })
                : null;

            canvases.forEach((canvas) => {
                if (observer) {
                    observer.observe(canvas);
                } else {
                    queuePage(canvas);
                }
            });

            queuePage(canvases[0]);
            queuePage(canvases[1]);
        } catch (_) {
            showFailure();
        }
    });
</script>
