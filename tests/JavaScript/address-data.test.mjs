import test from 'node:test';
import assert from 'node:assert/strict';
import { createBarangayLoader, normalizeAddressName } from '../../resources/js/address-data.js';

function response(cityCode, names, ok = true) {
    return { ok, json: async () => ({ city_code: cityCode, barangays: names.map((name) => ({ name })) }) };
}

test('loads the selected city once and reuses its in-memory result', async () => {
    let calls = 0;
    const loader = createBarangayLoader({
        fetchImpl: async () => { calls += 1; return response('137404', ['Alicia', 'Amihan']); },
        urlFor: (cityCode) => `/address-data/barangays/${cityCode}`,
    });

    assert.deepEqual((await loader.load('137404')).barangays, ['Alicia', 'Amihan']);
    assert.deepEqual(await loader.load('137404'), { barangays: ['Alicia', 'Amihan'], cached: true });
    assert.equal(calls, 1);
});

test('ignores an older response after a rapid city change', async () => {
    const pending = new Map();
    const loader = createBarangayLoader({
        fetchImpl: (url) => new Promise((resolve) => pending.set(url, resolve)),
        urlFor: (cityCode) => cityCode,
    });

    const first = loader.load('012801');
    const second = loader.load('137404');
    pending.get('137404')(response('137404', ['Alicia']));
    pending.get('012801')(response('012801', ['Adams (Pob.)']));

    assert.deepEqual((await second).barangays, ['Alicia']);
    assert.equal((await first).stale, true);
    assert.equal(loader.cache.has('012801'), false);
});

test('reports invalid, unavailable, and timed-out responses', async () => {
    const invalid = createBarangayLoader({
        fetchImpl: async () => response('wrong-city', ['Alicia']),
        urlFor: String,
    });
    await assert.rejects(invalid.load('137404'), /invalid/);

    const unavailable = createBarangayLoader({ fetchImpl: async () => ({ ok: false }), urlFor: String });
    await assert.rejects(unavailable.load('137404'), /Unable to load/);

    const timeout = createBarangayLoader({
        timeout: 5,
        urlFor: String,
        fetchImpl: (_url, { signal }) => new Promise((_resolve, reject) => signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')))),
    });
    await assert.rejects(timeout.load('137404'), /timed out/);
});

test('normalizes equivalent city and barangay labels', () => {
    assert.equal(normalizeAddressName('City of Quezon'), 'quezon');
    assert.equal(normalizeAddressName('Brgy. San José (Pob.)'), 'san jose');
});
