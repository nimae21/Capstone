# Phase 3A asset and address-data notes

The Philippine address records remain unchanged. The checked-in byte index was generated from `public/data/barangay.json` with `node scripts/generate-address-index.mjs`. Generation verifies that every city group is contiguous and that each indexed slice decodes back to the expected city and record count.

The audit found 17 regions, 88 province rows, 1,647 city rows, and 42,029 barangays. All province-to-region, city-to-province, and barangay-to-city references resolve. One pre-existing province-code inconsistency remains intentionally unchanged: code `1339` occurs as both `Ncr, City Of Manila, First District` and `City Of Manila`. No city codes or barangay codes are duplicated, and no barangay name is duplicated within a city.

Initial address selection now transfers and parses only region, province, and city JSON: 197,771 bytes instead of 4,983,425 bytes, a 4,785,654-byte (96.0%) reduction before city selection. The versioned barangay endpoint returns only names. Across 1,647 cities its encoded response averages 594 bytes; the Quezon City response is 3,301 bytes for 142 names, and the largest current response is 6,152 bytes for 259 names. These are source-file and encoded-payload measurements, not production transfer timings.

Inter, Figtree, Space Grotesk, Font Awesome Free, Leaflet, and Chart.js are pinned npm packages served through Vite. This removes browser requests to Google Fonts, Bunny Fonts, cdnjs, unpkg, and jsDelivr. Browser requests intentionally remain for `images.unsplash.com` images and for map use at `*.tile.openstreetmap.org` and `nominatim.openstreetmap.org`.

A future strict CSP must therefore account for `img-src 'self' data: https://images.unsplash.com https://*.tile.openstreetmap.org` and `connect-src 'self' https://nominatim.openstreetmap.org`. Existing Blade layouts and pages still contain inline style blocks and inline executable scripts. Complete their extraction or add per-response nonces/hashes before removing the current incremental CSP; do not add permanent `unsafe-inline` or `unsafe-eval` allowances.

Manual Chrome and Edge checks remain required for responsive address create/edit/checkout forms, Leaflet marker assets and map interaction, chart rendering, locally served fonts/icons, auth pages, dropdowns, modals, POS controls, gallery controls, and deferred recommendations.
