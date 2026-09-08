const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/admin-theme.js', 'utf8');
function setup(saved, unavailable = false) {
    const storage = new Map(saved ? [['admin.7', saved]] : []);
    const events = {}, windowEvents = {};
    const root = {dataset: {adminThemeKey: 'admin.7'}};
    const toggle = {attrs: {}, setAttribute(k,v) {this.attrs[k]=v;}, addEventListener(k,f) {this[k]=f;}};
    const status = {};
    const chart = {defaults: {}, instances: {}, register(plugin) {this.plugin = plugin;}};
    vm.runInNewContext(source, {
        document: {documentElement: root, getElementById: id => id === 'admin-dark-mode' ? toggle : status, addEventListener: (k,f) => events[k]=f},
        window: {Chart: chart, addEventListener: (k,f) => windowEvents[k]=f},
        localStorage: {getItem(k) {if(unavailable) throw Error(); return storage.get(k);}, setItem(k,v) {if(unavailable) throw Error(); storage.set(k,v);}},
    });
    return {root,toggle,status,storage,events,windowEvents,chart};
}
test('saved dark mode applies before content loads, and switch saves light mode', () => {
    const h=setup('dark');
    assert.equal(h.root.dataset.adminTheme,'dark');
    h.events.DOMContentLoaded();
    assert.equal(h.toggle.attrs['aria-checked'],'true');
    h.toggle.click();
    assert.equal(h.root.dataset.adminTheme,'light');
    assert.equal(h.storage.get('admin.7'),'light');
    assert.equal(h.toggle.attrs['aria-checked'],'false');
});
test('blocked storage still allows a theme change and explains the unsaved preference', () => {
    const h=setup(null,true); h.events.DOMContentLoaded(); h.toggle.click();
    assert.equal(h.root.dataset.adminTheme,'dark');
    assert.match(h.status.textContent,/storage is unavailable/);
});
test('changes in another tab sync only for the current admin', () => {
    const h=setup('light'); h.events.DOMContentLoaded();
    h.windowEvents.storage({key:'admin.8',newValue:'dark'});
    assert.equal(h.root.dataset.adminTheme,'light');
    h.windowEvents.storage({key:'admin.7',newValue:'dark'});
    assert.equal(h.toggle.attrs['aria-checked'],'true');
});
test('chart labels and grid colors follow dark and light themes', () => {
    const h=setup('dark'); h.events.DOMContentLoaded();
    const c={options:{plugins:{legend:{labels:{}},title:{}},scales:{x:{ticks:{},grid:{},border:{},title:{}}}}};
    h.chart.plugin.beforeUpdate(c);
    assert.equal(c.options.scales.x.ticks.color,'#cbd5e1');
    h.toggle.click(); h.chart.plugin.beforeUpdate(c);
    assert.equal(c.options.plugins.legend.labels.color,'#64748b');
});
