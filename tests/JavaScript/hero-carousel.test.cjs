const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');

function setup({ count = 3, reduced = false } = {}) {
    const element = () => ({
        events: {}, dataset: {}, attrs: {},
        addEventListener(name, fn) { this.events[name] = fn; },
        setAttribute(name, value) { this.attrs[name] = value; },
        classList: { toggle() {} },
    });
    const images = Array.from({ length: count }, () => Object.assign(element(), {
        dataset: { fallback: 'fallback.jpg' }, complete: true, naturalWidth: 100,
    }));
    const slides = images.map(img => Object.assign(element(), { querySelector: () => img }));
    const dots = images.map(element);
    const previous = element(), next = element(), pause = element();
    const controls = Object.assign(element(), {
        querySelector: selector => ({ '[data-previous]': previous, '[data-next]': next, '[data-pause]': pause })[selector],
        querySelectorAll: () => dots,
    });
    const carousel = Object.assign(element(), {
        querySelectorAll: selector => selector === 'img[data-fallback]' ? images : slides,
        querySelector: () => count > 1 ? controls : null,
        contains: target => target === next,
    });
    const document = Object.assign(element(), { hidden: false, querySelectorAll: () => [carousel] });
    const motion = Object.assign(element(), { matches: reduced });
    let now = 0, id = 0;
    const timers = new Map();
    const window = {
        matchMedia: () => motion,
        setTimeout: (fn, delay) => { timers.set(++id, { fn, at: now + delay }); return id; },
        clearTimeout: key => timers.delete(key),
    };
    const tick = ms => {
        now += ms;
        for (const [key, timer] of [...timers]) {
            if (timer.at <= now) { timers.delete(key); timer.fn(); }
        }
    };
    vm.runInNewContext(fs.readFileSync('public/js/hero-carousel.js', 'utf8'), { document, window });
    return { slides, dots, images, previous, next, pause, carousel, document, motion, timers, tick };
}

test('autoplay advances one slide and manual navigation wraps and resets the full interval', () => {
    const c = setup();
    c.tick(4000);
    c.next.events.click();
    assert.equal(c.dots[1].attrs['aria-current'], 'true');
    assert.equal(c.slides[0].inert, true);
    assert.equal(c.slides[1].tabIndex, 0);
    c.tick(1000);
    assert.equal(c.dots[1].attrs['aria-current'], 'true');
    c.tick(4000);
    assert.equal(c.dots[2].attrs['aria-current'], 'true');
    c.next.events.click();
    assert.equal(c.dots[0].attrs['aria-current'], 'true');
    c.previous.events.click();
    assert.equal(c.dots[2].attrs['aria-current'], 'true');
    c.dots[1].events.click();
    assert.equal(c.dots[1].attrs['aria-current'], 'true');
    assert.equal(c.timers.size, 1);
});

test('hover, focus, background tabs, explicit pause and reduced motion suspend autoplay', () => {
    const c = setup();
    c.carousel.events.mouseenter();
    assert.equal(c.timers.size, 0);
    c.carousel.events.mouseleave();
    assert.equal(c.timers.size, 1);
    c.carousel.events.focusin();
    c.next.events.click();
    assert.equal(c.timers.size, 0);
    c.carousel.events.focusout({ relatedTarget: null });
    assert.equal(c.timers.size, 1);
    c.document.hidden = true;
    c.document.events.visibilitychange();
    assert.equal(c.timers.size, 0);
    c.document.hidden = false;
    c.document.events.visibilitychange();
    c.pause.events.click();
    assert.equal(c.timers.size, 0);
    c.pause.events.click();
    assert.equal(c.timers.size, 1);
    c.motion.matches = true;
    c.motion.events.change();
    assert.equal(c.timers.size, 0);
    assert.equal(setup({ reduced: true }).timers.size, 0);
});

test('single-slide fallback has no autoplay and failed images only retry once', () => {
    const c = setup({ count: 1 });
    assert.equal(c.timers.size, 0);
    c.images[0].events.error();
    assert.equal(c.images[0].src, 'fallback.jpg');
    assert.equal(c.images[0].dataset.fallback, undefined);
    c.images[0].src = 'failed-fallback';
    c.images[0].events.error();
    assert.equal(c.images[0].src, 'failed-fallback');
});
