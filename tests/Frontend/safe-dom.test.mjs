import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

// A DOM double that rejects HTML parsing and records real event registrations.
class Element {
    children = [];
    events = {};
    textContent = '';
    set innerHTML(_) { throw new Error('Dynamic HTML parsing is forbidden'); }
    append(...children) { this.children.push(...children); }
    replaceChildren(...children) { this.children = children; }
    addEventListener(name, callback) { this.events[name] = callback; }
}
function documentDouble() {
    const elements = new Map();
    return {
        createElement: () => new Element(),
        createTextNode: text => ({ textContent: text }),
        getElementById(id) {
            if (!elements.has(id)) elements.set(id, new Element());
            return elements.get(id);
        },
    };
}
function functionSource(path, name, nextMarker) {
    const source = readFileSync(new URL(path, import.meta.url), 'utf8');
    const start = source.indexOf(`function ${name}(`);
    assert.ok(start >= 0);
    const end = source.indexOf(nextMarker, start);
    assert.ok(end > start);
    return source.slice(start, end);
}

test('POS renders malicious product values as text and preserves quantity controls', () => {
    const document = documentDouble();
    const malicious = '<img src=x onerror=alert(1)>';
    const changes = [];
    const cart = { 7: { variantId: 7, name: malicious, size: malicious, color: malicious, price: 500, quantity: 2 } };
    const context = { document, cart, decreaseQty: id => changes.push(-id), increaseQty: id => changes.push(id) };
    const source = functionSource('../../resources/views/admin/pos/index.blade.php', 'renderCart', 'function showError');
    runInNewContext(source + '\nrenderCart();', context);
    const row = document.getElementById('cartItems').children[0];
    assert.equal(row.children[0].children[0].textContent, malicious);
    assert.ok(row.children[0].children[1].textContent.includes(malicious));
    row.children[1].children[0].events.click();
    row.children[1].children[2].events.click();
    assert.deepEqual(changes, [-7, 7]);
    assert.equal(document.getElementById('checkoutBtn').disabled, false);
    assert.equal(document.getElementById('cartTotal').textContent, '\u20b11,000.00');
    delete cart[7];
    runInNewContext('renderCart();', context);
    assert.equal(document.getElementById('checkoutBtn').disabled, true);
});

test('gallery URLs cannot become markup or inline event handlers', () => {
    const document = documentDouble();
    const url = "https://images.example/a' onclick='alert(1).jpg";
    const context = { document, productImages: [{ color: 'red', url }] };
    const source = functionSource('../../resources/views/product/show.blade.php', 'updateGalleryForColor', '/*');
    runInNewContext(source + "\nupdateGalleryForColor('red');", context);
    const thumbnail = document.getElementById('thumbnailGallery').children[0];
    assert.equal(thumbnail.children[0].src, url);
    thumbnail.events.click();
    assert.equal(document.getElementById('mainProductImage').src, url);
});