@php
    $currentLocale = session('ui_locale', 'ar');
@endphp

<style>
    html[dir="ltr"] body { direction: ltr; text-align: left; }
    html[dir="ltr"] input,
    html[dir="ltr"] textarea,
    html[dir="ltr"] select { direction: ltr; text-align: left; }
    html[dir="ltr"] table th,
    html[dir="ltr"] table td { text-align: left; }
    html.ui-translating body { opacity: 0; }
    html.ui-translated body { opacity: 1; transition: opacity .14s ease-out; }
    @media (prefers-reduced-motion: reduce) {
        html.ui-translated body { transition: none; }
    }
</style>

<script>
    (() => {
        const locale = @json($currentLocale);
        const root = document.documentElement;
        const hasArabic = (value) => /[\u0600-\u06ff]/.test(value || '');

        root.lang = locale;
        root.dir = locale === 'en' ? 'ltr' : 'rtl';
        document.body?.classList.toggle('ui-ltr', locale === 'en');

        if (window.AlwrraqLocale && typeof window.AlwrraqLocale.postMessage === 'function') {
            window.AlwrraqLocale.postMessage(locale);
        }

        if (locale !== 'en') {
            root.classList.add('ui-translated');
            return;
        }

        root.classList.add('ui-translating');
        document.title = 'Alwrraq';

        const endpoint = @json(route('language.translate'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
        const ignoredTags = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'CODE', 'PRE', 'SVG', 'TEXTAREA']);
        const cache = new Map();
        const targets = new Map();
        const translatedNodes = new WeakSet();
        let observer;
        let flushTimer;

        try {
            const saved = JSON.parse(sessionStorage.getItem('alwrraq-ui-translations-v2') || '{}');
            Object.entries(saved).forEach(([source, translated]) => cache.set(source, translated));
        } catch (_) {}

        function remember(source, translated) {
            if (!translated || hasArabic(translated)) return;
            cache.set(source, translated);
            try {
                const compact = Object.fromEntries([...cache.entries()].slice(-600));
                sessionStorage.setItem('alwrraq-ui-translations-v2', JSON.stringify(compact));
            } catch (_) {}
        }

        function register(source, apply) {
            const text = String(source || '').trim();
            if (!text || !hasArabic(text)) return;
            if (cache.has(text)) {
                apply(cache.get(text));
                return;
            }
            if (!targets.has(text)) targets.set(text, []);
            targets.get(text).push(apply);
        }

        function registerTextNode(node) {
            if (!node?.parentElement || ignoredTags.has(node.parentElement.tagName) || translatedNodes.has(node)) return;
            const original = node.nodeValue || '';
            const core = original.trim();
            if (!hasArabic(core)) return;
            const leading = original.match(/^\s*/)?.[0] || '';
            const trailing = original.match(/\s*$/)?.[0] || '';
            register(core, (translated) => {
                translatedNodes.add(node);
                node.nodeValue = `${leading}${translated}${trailing}`;
            });
        }

        function registerAttributes(element) {
            if (!(element instanceof Element)) return;
            ['placeholder', 'title', 'aria-label', 'alt', 'data-label'].forEach((attribute) => {
                const original = element.getAttribute(attribute);
                if (!hasArabic(original)) return;
                register(original.trim(), (translated) => element.setAttribute(attribute, translated));
            });
            if (element.matches('input[type="button"],input[type="submit"],input[type="reset"]')) {
                const original = element.value;
                if (hasArabic(original)) register(original.trim(), (translated) => { element.value = translated; });
            }
        }

        function collect(scope = document.body) {
            if (!scope) return;
            if (scope.nodeType === Node.TEXT_NODE) {
                registerTextNode(scope);
                return;
            }
            const walker = document.createTreeWalker(scope, NodeFilter.SHOW_TEXT, {
                acceptNode: (node) => ignoredTags.has(node.parentElement?.tagName)
                    ? NodeFilter.FILTER_REJECT
                    : NodeFilter.FILTER_ACCEPT,
            });
            while (walker.nextNode()) registerTextNode(walker.currentNode);
            if (scope instanceof Element) registerAttributes(scope);
            scope.querySelectorAll?.('[placeholder],[title],[aria-label],[alt],[data-label],input[type="button"],input[type="submit"],input[type="reset"]')
                .forEach(registerAttributes);
        }

        async function translateBatch(texts) {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ texts }),
            });
            if (!response.ok) throw new Error('translation_failed');
            return (await response.json()).translations || {};
        }

        async function flush(initial = false) {
            clearTimeout(flushTimer);
            const pending = [...targets.keys()].filter((text) => !cache.has(text));
            try {
                for (let index = 0; index < pending.length; index += 50) {
                    const translations = await translateBatch(pending.slice(index, index + 50));
                    Object.entries(translations).forEach(([source, translated]) => remember(source, translated));
                }
                targets.forEach((callbacks, source) => {
                    const translated = cache.get(source);
                    if (translated) callbacks.forEach((apply) => apply(translated));
                });
                targets.clear();
            } catch (_) {
                // Keep the original Arabic text when the semantic translation service is unavailable.
            } finally {
                if (initial) {
                    root.classList.remove('ui-translating');
                    root.classList.remove('ui-auto-english');
                    root.classList.add('ui-translated');
                }
            }
        }

        collect();
        flush(true);

        observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'characterData') registerTextNode(mutation.target);
                mutation.addedNodes.forEach(collect);
            });
            clearTimeout(flushTimer);
            flushTimer = setTimeout(() => flush(false), 80);
        });
        observer.observe(document.body, { childList: true, subtree: true, characterData: true });
    })();
</script>

@include('shared.live-page-updates')
