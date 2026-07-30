@php
    $pdfJsBasePath = rtrim(request()->getBaseUrl(), '/').'/vendor/pdfjs';
@endphp
<script src="{{ $pdfJsBasePath }}/pdf.min.js"></script>
<script>
    window.addEventListener('load', async () => {
        const preview = document.getElementById(@json($pdfPreviewId));
        const status = document.getElementById(@json($pdfStatusId));
        if (!preview || !status) return;

        try {
            // Keep the authenticated PDF request on the exact WebView origin.
            // Laravel may be configured with "localhost" while the app is opened
            // through "127.0.0.1", which browsers correctly treat as two origins.
            const configuredPdfUrl = new URL(@json($pdfUrl), window.location.href);
            const sameOriginPdfUrl = `${configuredPdfUrl.pathname}${configuredPdfUrl.search}${configuredPdfUrl.hash}`;
            if (typeof window.pdfjsLib === 'undefined') {
                const nativeViewer = document.createElement('iframe');
                nativeViewer.src = sameOriginPdfUrl;
                nativeViewer.title = 'معاينة ملف PDF';
                nativeViewer.style.width = '100%';
                nativeViewer.style.maxWidth = '100%';
                nativeViewer.style.minHeight = 'calc(100vh - 120px)';
                nativeViewer.style.border = '0';
                nativeViewer.style.display = 'block';
                preview.replaceChildren(nativeViewer);
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = @json($pdfJsBasePath.'/pdf.worker.min.js');
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
            const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

            status.remove();

            const renderPage = async (canvas) => {
                if (canvas.dataset.rendering === '1' || canvas.dataset.rendered === '1') return;
                canvas.dataset.rendering = '1';
                const page = await pdf.getPage(Number(canvas.dataset.page));
                const baseViewport = page.getViewport({ scale: 1 });
                const viewport = page.getViewport({ scale: availableWidth / baseViewport.width });
                const context = canvas.getContext('2d');

                canvas.width = Math.floor(viewport.width * pixelRatio);
                canvas.height = Math.floor(viewport.height * pixelRatio);
                canvas.style.width = `${Math.floor(viewport.width)}px`;
                canvas.style.height = `${Math.floor(viewport.height)}px`;

                await page.render({
                    canvasContext: context,
                    viewport,
                    transform: pixelRatio === 1 ? null : [pixelRatio, 0, 0, pixelRatio, 0, 0],
                }).promise;
                canvas.dataset.rendered = '1';
                delete canvas.dataset.rendering;
            };

            const observer = 'IntersectionObserver' in window
                ? new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        observer.unobserve(entry.target);
                        renderPage(entry.target);
                    });
                }, { root: preview, rootMargin: '700px 0px' })
                : null;

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const canvas = document.createElement('canvas');
                canvas.className = 'pdf-page';
                canvas.dataset.page = String(pageNumber);
                canvas.style.width = `${availableWidth}px`;
                canvas.style.height = `${Math.round(availableWidth * 1.414)}px`;
                preview.appendChild(canvas);

                if (observer) {
                    observer.observe(canvas);
                } else {
                    renderPage(canvas);
                }
            }

            await renderPage(preview.querySelector('.pdf-page'));
        } catch (error) {
            const fallback = document.createElement('iframe');
            fallback.src = new URL(@json($pdfUrl), window.location.href).pathname
                + new URL(@json($pdfUrl), window.location.href).search;
            fallback.title = 'معاينة ملف PDF';
            fallback.style.width = '100%';
            fallback.style.maxWidth = '100%';
            fallback.style.minHeight = 'calc(100vh - 120px)';
            fallback.style.border = '0';
            fallback.style.display = 'block';
            preview.replaceChildren(fallback);
        }
    });
</script>
