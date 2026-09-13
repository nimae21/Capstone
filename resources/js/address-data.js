export function createBarangayLoader({ fetchImpl = fetch, urlFor, timeout = 10000 } = {}) {
    const cache = new Map();
    let activeRequest = null;
    let requestNumber = 0;

    async function load(cityCode) {
        requestNumber += 1;
        const currentRequest = requestNumber;
        activeRequest?.abort();
        activeRequest = null;

        if (!cityCode) return { barangays: [], empty: true };
        if (cache.has(cityCode)) return { barangays: cache.get(cityCode), cached: true };

        const controller = new AbortController();
        activeRequest = controller;
        let timedOut = false;
        const timer = setTimeout(() => {
            timedOut = true;
            controller.abort();
        }, timeout);

        try {
            const response = await fetchImpl(urlFor(cityCode), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) throw new Error('Unable to load barangays for this city.');

            const payload = await response.json();
            if (String(payload.city_code) !== String(cityCode) || !Array.isArray(payload.barangays)) {
                throw new Error('The barangay response was invalid.');
            }

            const barangays = payload.barangays.map((item) => item?.name).filter((name) => typeof name === 'string' && name);
            if (barangays.length !== payload.barangays.length) throw new Error('The barangay response was invalid.');
            if (currentRequest !== requestNumber) return { stale: true, barangays: [] };

            cache.set(cityCode, barangays);
            return { barangays };
        } catch (error) {
            if (currentRequest !== requestNumber) return { stale: true, barangays: [] };
            if (error.name === 'AbortError' && timedOut) throw new Error('The barangay request timed out. Select the city again to retry.');
            throw error;
        } finally {
            clearTimeout(timer);
            if (currentRequest === requestNumber) activeRequest = null;
        }
    }

    return { load, cache };
}

export function normalizeAddressName(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/^(barangay|brgy\.?|city of|municipality of)\s+/i, '')
        .replace(/\s*\(capital\)\s*/gi, ' ')
        .replace(/\s*\(pob\.\)\s*/gi, ' ')
        .replace(/\s*\(.*?\)\s*/g, ' ')
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}
