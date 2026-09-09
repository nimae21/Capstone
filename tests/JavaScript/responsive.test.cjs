const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/responsive.js', 'utf8');

function node() {
    return { events: {}, attrs: {}, hidden: true,
        addEventListener(k, fn) { this.events[k] = fn; },
        setAttribute(k, v) { this.attrs[k] = v; },
        focus() { this.focused = true; },
    };
}
test('account menu opens by activation, closes outside, and returns focus on Escape', () => {
    const menu = node(), trigger = node(), dropdown = node(), events = {};
    menu.contains = target => [menu, trigger, dropdown].includes(target);
    vm.runInNewContext(source, { document: {
        querySelectorAll: () => [],
        getElementById: id => ({userMenu: menu, userMenuTrigger: trigger, userDropdown: dropdown})[id],
        addEventListener: (k, fn) => events[k] = fn,
    }});
    trigger.events.click();
    assert.equal(dropdown.hidden, false);
    assert.equal(trigger.attrs['aria-expanded'], 'true');
    events.click({target: dropdown});
    assert.equal(dropdown.hidden, false);
    menu.events.keydown({key: 'Escape'});
    assert.equal(dropdown.hidden, true);
    assert.equal(trigger.focused, true);
    trigger.events.click();
    events.click({target: {}});
    assert.equal(trigger.attrs['aria-expanded'], 'false');
});

test('closed mobile sidebar cannot receive focus and desktop navigation stays available', () => {
    const sidebar = node(), toggle = node(), events = {}, windowEvents = {};
    let mobile = true, open = false, observer;
    sidebar.classList = {contains: () => open, remove: () => open = false};
    vm.runInNewContext(source, {
        matchMedia: () => ({matches: mobile}),
        MutationObserver: class { constructor(fn) { observer = fn; } observe() {} },
        window: {addEventListener: (k, fn) => windowEvents[k] = fn},
        document: {
            querySelectorAll: () => [],
            getElementById: id => ({adminSidebar: sidebar, mobileMenuToggle: toggle})[id],
            addEventListener: (k, fn) => events[k] = fn,
            body: {classList: {remove() {}}},
        },
    });
    assert.equal(sidebar.inert, true);
    open = true; observer();
    assert.equal(sidebar.inert, false);
    assert.equal(toggle.attrs['aria-expanded'], 'true');
    events.keydown({key: 'Escape'}); observer();
    assert.equal(sidebar.inert, true);
    assert.equal(toggle.focused, true);
    mobile = false; windowEvents.resize();
    assert.equal(sidebar.inert, false);
});
