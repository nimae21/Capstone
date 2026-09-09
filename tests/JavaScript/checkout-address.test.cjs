const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/checkout-address.js', 'utf8');

function setup(reopen = false) {
    function element() {
        return {
            listeners: {}, hidden: true, disabled: false, textContent: '', dataset: {},
            addEventListener(type, callback) { this.listeners[type] = callback; },
            focus() { this.focused = true; },
            scrollIntoView() { this.scrolled = true; },
            click() { this.listeners.click(); },
        };
    }
    const ids = Object.fromEntries(['checkoutForm', 'checkoutAddressModal', 'addCheckoutAddress', 'checkoutAddressWarning',
        'checkoutAddressForm', 'checkoutAddressFormWarning', 'saveCheckoutAddress'].map(id => [id, element()]));
    const close = element(), cancel = element(), events = [];
    const modal = ids.checkoutAddressModal;
    modal.dataset.reopen = String(reopen);
    modal.showModal = () => { modal.open = true; };
    modal.close = () => { modal.open = false; modal.listeners.close(); };
    modal.querySelectorAll = () => [close, cancel];
    const fields = Object.fromEntries(['region', 'province', 'city', 'barangay'].map(name => [name, {value: name}]));
    ids.checkoutAddressForm.elements = {namedItem: name => fields[name]};
    ids.checkoutForm.querySelector = () => ids.checkoutForm.selected || null;
    const window = element();
    vm.runInNewContext(source, {
        document: {getElementById: id => ids[id], dispatchEvent: event => events.push(event.type)},
        window, Event: class {constructor(type) {this.type = type;}},
    });
    function submit(id) {
        const event = {defaultPrevented: false, preventDefault() {this.defaultPrevented = true;}};
        ids[id].listeners.submit(event);
        return event;
    }
    return {ids, fields, modal, cancel, close, window, events, submit};
}

test('missing address prevents checkout and focuses a visible warning', () => {
    const h = setup();
    assert.equal(h.submit('checkoutForm').defaultPrevented, true);
    assert.equal(h.ids.checkoutAddressWarning.hidden, false);
    assert.equal(h.ids.checkoutAddressWarning.focused, true);
    assert.match(h.ids.checkoutAddressWarning.textContent, /add or select a delivery address/);
    h.ids.checkoutForm.selected = {};
    h.ids.checkoutForm.listeners.change({target: {name: 'address_id'}});
    assert.equal(h.ids.checkoutAddressWarning.hidden, true);
    assert.equal(h.submit('checkoutForm').defaultPrevented, false);
});

test('add opens the modal, refreshes the map, and cancel restores focus without submitting', () => {
    const h = setup();
    h.ids.addCheckoutAddress.click();
    assert.equal(h.modal.open, true);
    assert.deepEqual(h.events, ['address-modal-opened']);
    h.fields.region.value = 'Retained input';
    h.cancel.click();
    assert.equal(h.modal.open, false);
    assert.equal(h.ids.addCheckoutAddress.focused, true);
    h.ids.addCheckoutAddress.click();
    assert.equal(h.fields.region.value, 'Retained input');
    assert.equal(h.ids.saveCheckoutAddress.disabled, false);
});

test('server-side errors reopen the modal', () => {
    assert.equal(setup(true).modal.open, true);
});

test('invalid fields and disabled empty address dropdowns show warnings and block saving', () => {
    const h = setup();
    h.ids.checkoutAddressForm.listeners.invalid();
    assert.equal(h.ids.checkoutAddressFormWarning.hidden, false);
    assert.match(h.ids.checkoutAddressFormWarning.textContent, /required/);
    h.fields.barangay.value = '';
    assert.equal(h.submit('checkoutAddressForm').defaultPrevented, true);
    assert.equal(h.ids.saveCheckoutAddress.disabled, false);
    assert.match(h.ids.checkoutAddressFormWarning.textContent, /barangay/);
});

test('valid save permits submission once and browser back restores the save button', () => {
    const h = setup();
    assert.equal(h.submit('checkoutAddressForm').defaultPrevented, false);
    assert.equal(h.ids.saveCheckoutAddress.disabled, true);
    assert.equal(h.submit('checkoutAddressForm').defaultPrevented, true);
    h.window.listeners.pageshow();
    assert.equal(h.ids.saveCheckoutAddress.disabled, false);
});