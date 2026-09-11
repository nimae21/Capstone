const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/product-search.js', 'utf8');

function setup() {
    let now = 0, next = 0;
    const timers = new Map(), requests = [];
    function element(tag = 'div') {
        const node = {
            tag, children: [], listeners: {}, hidden: false, value: '', dataset: {}, style: {},
            className: '', textContent: '', parentNode: null,
            addEventListener(name, fn) { this.listeners[name] = fn; },
            setAttribute(name, value) { this[name] = value; },
            append(...children) {
                for (const child of children) {
                    child.parentNode = this;
                    this.children.push(child);
                }
            },
            replaceChildren(...children) {
                for (const child of this.children) child.parentNode = null;
                this.children = [];
                this.append(...children);
            },
            remove() {
                if (!this.parentNode) return;
                this.parentNode.children = this.parentNode.children.filter(child => child !== this);
                this.parentNode = null;
            },
            querySelectorAll(selector) {
                const matches = [];
                const visit = current => {
                    for (const child of current.children) {
                        const classNames = String(child.className || '').split(/\s+/);
                        if ((selector === 'a' && child.tag === 'a') ||
                            (selector === '.search-skeleton' && classNames.includes('search-skeleton')) ||
                            (selector === '[data-all-results]' && Object.hasOwn(child.dataset, 'allResults'))) {
                            matches.push(child);
                        }
                        visit(child);
                    }
                };
                visit(this);
                return matches;
            },
            querySelector(selector) { return this.querySelectorAll(selector)[0] || null; },
            contains(target) {
                return this === target || this.children.some(child => child.contains(target));
            },
            focus() {},
        };
        return node;
    }
    const ids = Object.fromEntries(['productSearch', 'productSearchInput', 'productSearchResults', 'productSearchList', 'productSearchStatus'].map(id => [id, element()]));
    const form = ids.productSearch;
    form.action = 'https://store.example/search';
    form.dataset.suggestionsUrl = '/search/suggestions';
    const doc = { getElementById: id => ids[id], createElement: element, addEventListener() {}, activeElement: null };
    vm.runInNewContext(source, {
        document: doc, window: { location: { origin: 'https://store.example' } }, URL, AbortController, DOMException,
        Date: { now: () => now },
        setTimeout: (fn, delay) => { const id = ++next; timers.set(id, {fn, at: now + delay}); return id; },
        clearTimeout: id => timers.delete(id),
        fetch: (url, options) => new Promise((resolve, reject) => {
            options.signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')));
            requests.push({url, options, resolve, reject});
        }),
    });
    async function tick(ms) {
        now += ms;
        for (const [id, timer] of [...timers]) if (timer.at <= now) { timers.delete(id); timer.fn(); }
        for (let i = 0; i < 5; i++) await Promise.resolve();
    }
    function type(query) { ids.productSearchInput.value = query; ids.productSearchInput.listeners.input(); }
    async function answer(index, name) {
        requests[index].resolve({ok: true, json: async () => ({products: [{name, image: null, url: '/product/1'}], next_page: null})});
        await tick(0);
    }
    return {ids, requests, tick, type, answer};
}

test('a stalled request times out with a usable full-search link and ignores late data', async () => {
    const h = setup();
    h.type('runner'); await h.tick(250); await h.tick(10000);
    assert.match(h.ids.productSearchStatus.textContent, /taking too long/);
    assert.equal(h.requests[0].options.signal.aborted, true);
    assert.equal(h.ids.productSearchList.children[0].children[0].href, 'https://store.example/search?q=runner');
    await h.answer(0, 'Late shoe');
    assert.match(h.ids.productSearchStatus.textContent, /taking too long/);
});

test('recent searches render immediately without another request and expire after a minute', async () => {
    const h = setup();
    h.type('runner'); await h.tick(250); await h.answer(0, 'Runner');
    h.type(''); h.type(' RUNNER ');
    assert.equal(h.ids.productSearchStatus.textContent, 'Matching shoes');
    await h.tick(250); assert.equal(h.requests.length, 1);
    await h.tick(60000); h.type('runner'); await h.tick(250);
    assert.equal(h.requests.length, 2);
});

test('typing another query cancels the old request and prevents stale results', async () => {
    const h = setup();
    h.type('runner'); await h.tick(250);
    h.type('basket'); await h.tick(250);
    assert.equal(h.requests[0].options.signal.aborted, true);
    await h.answer(1, 'Basketball'); await h.answer(0, 'Runner');
    const link = h.ids.productSearchList.children[0].children[0];
    assert.equal(link.children[1].textContent, 'Basketball');
});

test('HTTP errors show a full-search fallback instead of a stuck loading state', async () => {
    const h = setup();
    h.type('runner'); await h.tick(250);
    h.requests[0].resolve({ok: false}); await h.tick(0);
    assert.match(h.ids.productSearchStatus.textContent, /Could not load products/);
    assert.equal(h.ids.productSearchList.children.length, 1);
});