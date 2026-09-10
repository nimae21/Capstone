(() => {
    const form = document.getElementById('productSearch');
    if (!form) return;
    const input = document.getElementById('productSearchInput');
    const panel = document.getElementById('productSearchResults');
    const list = document.getElementById('productSearchList');
    const status = document.getElementById('productSearchStatus');
    let timer, controller, nextPage = null, revision = 0, busy = false, activeQuery = '';
    const recent = new Map();
    const cacheLifetime = 60_000;
    const requestTimeout = 10_000;
    const more = document.createElement('button');
    more.type = 'button';
    more.className = 'search-load-more';
    more.hidden = true;
    panel.append(more);
    status.setAttribute('role', 'status');
    panel.style.maxHeight = 'min(60vh, 300px)';

    function close() {
        clearTimeout(timer);
        controller?.abort();
        revision++;
        busy = false;
        panel.hidden = true;
        panel.setAttribute('aria-busy', 'false');
        input.setAttribute('aria-expanded', 'false');
    }
    function open() {
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }
    function fullResultsLink(query) {
        list.querySelector('[data-all-results]')?.remove();
        const item = document.createElement('li');
        item.dataset.allResults = '';
        const link = document.createElement('a');
        const url = new URL(form.action, window.location.origin);
        url.searchParams.set('q', query);
        link.href = url.href;
        link.textContent = 'View all results';
        item.append(link);
        list.append(item);
    }
    function skeletons() {
        for (let i = 0; i < 3; i++) {
            const item = document.createElement('li');
            item.className = 'search-skeleton';
            item.setAttribute('aria-hidden', 'true');
            const picture = document.createElement('span');
            const line = document.createElement('span');
            item.append(picture, line);
            list.append(item);
        }
    }
    function removeSkeletons() {
        list.querySelectorAll('.search-skeleton').forEach(item => item.remove());
    }
    function appendProducts(products) {
        list.querySelector('[data-all-results]')?.remove();
        for (const product of products) {
            if (Array.from(list.querySelectorAll('a')).some(link => link.href === product.url)) continue;
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = product.url;
            const placeholder = document.createElement('span');
            placeholder.className = 'search-image-placeholder';
            placeholder.setAttribute('aria-hidden', 'true');
            placeholder.textContent = '👟';
            link.append(placeholder);
            if (product.image) {
                const image = document.createElement('img');
                image.alt = ''; image.hidden = true;
                image.loading = 'lazy';
                image.decoding = 'async';
                image.width = 48;
                image.height = 48;
                image.addEventListener('load', () => { image.hidden = false; placeholder.hidden = true; });
                image.addEventListener('error', () => { image.remove(); placeholder.hidden = false; });
                image.src = product.image;
                link.append(image);
            }
            const name = document.createElement('span');
            name.textContent = product.name;
            link.append(name);
            item.append(link);
            list.append(item);
        }
    }
    function finishBatch(data, query) {
        appendProducts(data.products);
        nextPage = data.next_page;
        status.textContent = list.children.length ? 'Matching shoes' : 'No products found. Try another shoe name.';
        fullResultsLink(query);
        more.textContent = 'Load more';
        more.hidden = nextPage === null;
    }
    async function load(page = nextPage) {
        if (busy || page === null || panel.hidden) return;
        const current = revision;
        const query = activeQuery;
        busy = true;
        more.disabled = true;
        panel.setAttribute('aria-busy', 'true');
        status.textContent = 'Searching...';
        list.querySelector('[data-all-results]')?.remove();
        skeletons();
        const request = new AbortController();
        controller = request;
        const deadline = setTimeout(() => request.abort(), requestTimeout);
        try {
            const url = new URL(form.dataset.suggestionsUrl, window.location.origin);
            url.searchParams.set('q', query);
            url.searchParams.set('page', String(page));
            const response = await fetch(url, {
                signal: request.signal,
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Search unavailable');
            const data = await response.json();
            if (current !== revision) return;
            if (request.signal.aborted) throw new DOMException('Timed out', 'AbortError');
            if (!Array.isArray(data.products) || !(data.next_page === null || Number.isInteger(data.next_page))) {
                throw new Error('Invalid suggestions');
            }
            removeSkeletons();
            if (page === 1) {
                if (recent.size >= 30) recent.delete(recent.keys().next().value);
                recent.set(query.toLowerCase(), { data, expires: Date.now() + cacheLifetime });
            }
            finishBatch(data, query);
        } catch (error) {
            if (current !== revision) return;
            removeSkeletons();
            status.textContent = error.name === 'AbortError'
                ? 'Search is taking too long. Try again.'
                : 'Could not load products. Try again.';
            nextPage = page;
            more.textContent = 'Try again';
            more.hidden = false;
            fullResultsLink(query);
        } finally {
            clearTimeout(deadline);
            if (current === revision) {
                busy = false;
                more.disabled = false;
                panel.setAttribute('aria-busy', 'false');
            }
            if (controller === request) controller = null;
        }
    }
    function schedule() {
        close();
        activeQuery = input.value.trim();
        nextPage = 1;
        list.replaceChildren();
        more.hidden = true;
        more.disabled = false;
        if (activeQuery.length < 2) return;
        open();
        panel.scrollTop = 0;
        const cached = recent.get(activeQuery.toLowerCase());
        if (cached && cached.expires > Date.now()) {
            finishBatch(cached.data, activeQuery);
            return;
        }
        status.textContent = 'Searching...';
        timer = setTimeout(() => load(1), 250);
    }
    more.addEventListener('click', () => load());
    panel.addEventListener('scroll', () => {
        // Load only after a user scroll; never cascade requests on initial render.
        if (panel.scrollTop > 0 && panel.scrollTop + panel.clientHeight >= panel.scrollHeight - 24 &&
            more.textContent !== 'Try again') load();
    }, { passive: true });
    input.addEventListener('input', schedule);
    input.addEventListener('focus', () => { if (panel.hidden) schedule(); });
    form.addEventListener('submit', event => {
        input.value = input.value.trim();
        if (!input.value) { event.preventDefault(); input.focus(); }
        close();
    });
    form.addEventListener('keydown', event => {
        if (event.key === 'Escape') { input.focus(); close(); return; }
        if (panel.hidden || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        const controls = [...list.querySelectorAll('a'), ...(!more.hidden ? [more] : [])];
        if (!controls.length) return;
        event.preventDefault();
        const index = controls.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') controls[(index + 1) % controls.length].focus();
        else if (index <= 0) input.focus();
        else controls[index - 1].focus();
    });
    document.addEventListener('click', event => { if (!form.contains(event.target)) close(); });
    form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) close(); });
})();