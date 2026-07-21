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
            pdfjsLib.GlobalWorkerOptions.workerSrc = @json($pdfJsBasePath.'/pdf.worker.min.js');

            // Keep the authenticated PDF request on the exact WebView origin.
            // Laravel may be configured with "localhost" while the app is opened
            // through "127.0.0.1", which browsers correctly treat as two origins.
            const configuredPdfUrl = new URL(@json($pdfUrl), window.location.href);
            const sameOriginPdfUrl = `${configuredPdfUrl.pathname}${configuredPdfUrl.search}${configuredPdfUrl.hash}`;
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
            const availableWidth = Math.max(280, preview.clientWidth - 20);
            const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

            status.remove();

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const page = await pdf.getPage(pageNumber);
                const baseViewport = page.getViewport({ scale: 1 });
                const viewport = page.getViewport({ scale: availableWidth / baseViewport.width });
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                canvas.className = 'pdf-page';
                canvas.width = Math.floor(viewport.width * pixelRatio);
                canvas.height = Math.floor(viewport.height * pixelRatio);
                canvas.style.width = `${Math.floor(viewport.width)}px`;
                canvas.style.height = `${Math.floor(viewport.height)}px`;
                preview.appendChild(canvas);

                await page.render({
                    canvasContext: context,
                    viewport,
                    transform: pixelRatio === 1 ? null : [pixelRatio, 0, 0, pixelRatio, 0, 0],
                }).promise;
            }
        } catch (error) {
            if (!status.isConnected) {
                preview.replaceChildren(status);
            }
            status.textContent = @json($pdfErrorMessage ?? 'تعذر عرض ملف PDF. حاول مرة أخرى.');
        }
    });
</script>
