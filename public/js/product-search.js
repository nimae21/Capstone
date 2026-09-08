(() => {
    const form = document.getElementById('productSearch');
    if (!form) return;
    const input = document.getElementById('productSearchInput');
    const panel = document.getElementById('productSearchResults');
    const list = document.getElementById('productSearchList');
    const status = document.getElementById('productSearchStatus');
    let timer;
    let controller;
    let revision = 0;

    function close() {
        clearTimeout(timer);
        controller?.abort();
        revision++;
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }
    function showStatus(message) {
        list.replaceChildren();
        status.textContent = message;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }
    function schedule() {
        close();
        const query = input.value.trim();
        if (query.length < 2) return;
        const current = revision;
        showStatus('Searching...');
        timer = setTimeout(async () => {
            controller = new AbortController();
            try {
                const url = new URL(form.dataset.suggestionsUrl, window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Search unavailable');
                const data = await response.json();
                if (revision !== current) return;
                showStatus(data.products.length ? 'Matching shoes' : 'No products found. Try another shoe name.');
                for (const product of data.products) {
                    const item = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = product.url;
                    if (product.image) {
                        const image = document.createElement('img');
                        image.src = product.image;
                        image.alt = '';
                        image.addEventListener('error', () => { image.hidden = true; });
                        link.append(image);
                    } else {
                        const placeholder = document.createElement('span');
                        placeholder.className = 'search-image-placeholder';
                        placeholder.setAttribute('aria-hidden', 'true');
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-shoe-prints';
                        placeholder.append(icon);
                        link.append(placeholder);
                    }
                    const name = document.createElement('span');
                    name.textContent = product.name;
                    link.append(name);
                    item.append(link);
                    list.append(item);
                }
                if (data.products.length) {
                    const item = document.createElement('li');
                    const all = document.createElement('a');
                    const url = new URL(form.action, window.location.origin);
                    url.searchParams.set('q', query);
                    all.href = url.href;
                    all.textContent = 'View all results';
                    item.append(all);
                    list.append(item);
                }
            } catch (error) {
                if (error.name !== 'AbortError' && revision === current) {
                    showStatus('Suggestions are unavailable. Press Enter to search.');
                }
            }
        }, 300);
    }
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
        const links = Array.from(list.querySelectorAll('a'));
        if (!links.length) return;
        event.preventDefault();
        const index = links.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') links[(index + 1) % links.length].focus();
        else if (index <= 0) input.focus();
        else links[index - 1].focus();
    });
    document.addEventListener('click', event => { if (!form.contains(event.target)) close(); });
    form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) close(); });
})();
