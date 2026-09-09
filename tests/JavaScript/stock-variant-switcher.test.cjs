const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/stock-variant-switcher.js', 'utf8');

test('color switching shows only the selected sizes, including colors with colliding slugs', () => {
    const events = {}, windowEvents = {};
    const select = { value: 'Blue/White', addEventListener: (name, fn) => events[name] = fn };
    const groups = ['Blue/White', 'Blue White', 'Red'].map(color => ({dataset: {color}, hidden: false}));
    vm.runInNewContext(source, {
        document: {getElementById: () => select, querySelectorAll: () => groups},
        window: {addEventListener: (name, fn) => windowEvents[name] = fn},
    });
    assert.deepEqual(groups.map(group => group.hidden), [false, true, true]);
    select.value = 'Blue White'; events.change();
    assert.deepEqual(groups.map(group => group.hidden), [true, false, true]);
    select.value = 'Red'; windowEvents.pageshow();
    assert.deepEqual(groups.map(group => group.hidden), [true, true, false]);
});

test('a missing switcher does not break other page scripts', () => {
    assert.doesNotThrow(() => vm.runInNewContext(source, {document: {getElementById: () => null}}));
});
