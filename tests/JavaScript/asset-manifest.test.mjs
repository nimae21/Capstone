import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

test('production manifest contains the page-specific and shared asset entries', () => {
    const manifest = JSON.parse(fs.readFileSync('public/build/manifest.json', 'utf8'));
    for (const entry of [
        'resources/css/app.css',
        'resources/css/checkout.css',
        'resources/css/vendor-assets.css',
        'resources/js/app.js',
        'resources/js/address-selector.js',
        'resources/js/chart-vendor.js',
        'resources/js/checkout-address.js',
    ]) {
        assert.ok(manifest[entry]?.file, `${entry} is missing from the production manifest`);
        assert.ok(fs.existsSync(`public/build/${manifest[entry].file}`));
    }
});
