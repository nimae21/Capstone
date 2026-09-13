import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const read = (name) => JSON.parse(fs.readFileSync(`public/data/${name}.json`, 'utf8'));

test('Philippine address references remain consistent and known source duplicates are reported', () => {
    const regions = read('region');
    const provinces = read('province');
    const cities = read('city');
    const barangays = read('barangay');
    const regionCodes = new Set(regions.map((row) => String(row.region_code)));
    const provinceCodes = new Set(provinces.map((row) => String(row.province_code)));
    const cityCodes = new Set(cities.map((row) => String(row.city_code)));

    assert.deepEqual([regions.length, provinces.length, cities.length, barangays.length], [17, 88, 1647, 42029]);
    assert.equal(provinces.filter((row) => !regionCodes.has(String(row.region_code))).length, 0);
    assert.equal(cities.filter((row) => !provinceCodes.has(String(row.province_code))).length, 0);
    assert.equal(barangays.filter((row) => !cityCodes.has(String(row.city_code))).length, 0);
    assert.equal(new Set(cities.map((row) => row.city_code)).size, cities.length);
    assert.equal(new Set(barangays.map((row) => row.brgy_code)).size, barangays.length);

    const duplicateProvince = provinces.filter((row) => String(row.province_code) === '1339');
    assert.deepEqual(duplicateProvince.map((row) => row.province_name), [
        'Ncr, City Of Manila, First District',
        'City Of Manila',
    ]);

    const namesWithinCity = new Set();
    for (const row of barangays) {
        const key = `${row.city_code}\0${row.brgy_name}`;
        assert.equal(namesWithinCity.has(key), false, `duplicate barangay name in city: ${key}`);
        namesWithinCity.add(key);
    }
});