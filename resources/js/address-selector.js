import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { createBarangayLoader, normalizeAddressName } from './address-data.js';

function initAddressSelector() {
    const region = document.getElementById('region');
    const province = document.getElementById('province');
    const city = document.getElementById('city');
    const barangay = document.getElementById('barangay');
    if (!region || !province || !city || !barangay) return;

    const street = document.getElementById('street');
    const postalCode = document.getElementById('postal_code');
    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    const locateButton = document.getElementById('locateMe');
    const findButton = document.getElementById('findLocation');
    const locationStatus = document.getElementById('locationStatus');
    const mapElement = document.getElementById('map');
    let regions = [];
    let provinces = [];
    let cities = [];
    let map;
    let marker;

    const endpointTemplate = barangay.dataset.endpoint;
    const barangayLoader = createBarangayLoader({
        urlFor: (cityCode) => endpointTemplate.replace('__CITY__', encodeURIComponent(cityCode)),
    });

    function setLocationStatus(message, isError = false) {
        if (!locationStatus) return;
        locationStatus.textContent = message;
        locationStatus.className = `mt-2 text-sm ${isError ? 'text-red-600' : 'text-gray-500'}`;
    }

    async function fetchJson(url, timeout = 10000) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeout);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            if (!response.ok) throw new Error(`Unable to load ${url}`);
            return await response.json();
        } finally {
            clearTimeout(timer);
        }
    }

    function resetDropdown(dropdown, placeholder) {
        dropdown.replaceChildren(new Option(placeholder, ''));
    }

    function populateDropdown(dropdown, list, textKey, codeKey) {
        list.forEach((item) => {
            const option = new Option(item[textKey], item[textKey]);
            option.dataset.code = item[codeKey];
            dropdown.add(option);
        });
    }

    function selectOption(dropdown, value) {
        const normalizedValue = normalizeAddressName(value);
        const option = [...dropdown.options].find((item) => normalizeAddressName(item.value) === normalizedValue);
        if (!option) return false;
        dropdown.value = option.value;
        return true;
    }

    function selectedCode(dropdown) {
        return dropdown.selectedOptions[0]?.dataset.code || '';
    }

    function findMatchingItem(list, values, nameKey, filter = () => true) {
        const normalizedValues = values.map(normalizeAddressName).filter(Boolean);
        return list.find((item) => filter(item) && normalizedValues.includes(normalizeAddressName(item[nameKey])))
            || list.find((item) => {
                if (!filter(item)) return false;
                const name = normalizeAddressName(item[nameKey]);
                return normalizedValues.some((value) => name.includes(value) || value.includes(name));
            });
    }

    function loadProvinces(regionCode) {
        resetDropdown(province, 'Select Province');
        resetDropdown(city, 'Select City/Municipality');
        resetDropdown(barangay, 'Select Barangay');
        province.disabled = !regionCode;
        city.disabled = true;
        barangay.disabled = true;
        if (regionCode) populateDropdown(province, provinces.filter((item) => String(item.region_code) === String(regionCode)), 'province_name', 'province_code');
    }

    function loadCities(provinceCode) {
        resetDropdown(city, 'Select City/Municipality');
        resetDropdown(barangay, 'Select Barangay');
        city.disabled = !provinceCode;
        barangay.disabled = true;
        if (provinceCode) populateDropdown(city, cities.filter((item) => String(item.province_code) === String(provinceCode)), 'city_name', 'city_code');
    }

    async function loadBarangays(cityCode) {
        resetDropdown(barangay, cityCode ? 'Loading barangaysâ€¦' : 'Select Barangay');
        barangay.disabled = true;
        if (!cityCode) return false;

        try {
            const result = await barangayLoader.load(cityCode);
            if (result.stale || selectedCode(city) !== String(cityCode)) return false;
            resetDropdown(barangay, 'Select Barangay');
            result.barangays.forEach((name) => barangay.add(new Option(name, name)));
            barangay.disabled = false;
            return true;
        } catch (error) {
            resetDropdown(barangay, 'Barangays unavailable â€” select the city again to retry');
            setLocationStatus(error.message || 'Unable to load barangays. Select the city again to retry.', true);
            return false;
        }
    }

    async function loadAddressData() {
        [regions, provinces, cities] = await Promise.all([
            fetchJson('/data/region.json'),
            fetchJson('/data/province.json'),
            fetchJson('/data/city.json'),
        ]);
        resetDropdown(region, 'Select Region');
        populateDropdown(region, regions, 'region_name', 'region_code');
        province.disabled = true;
        city.disabled = true;
        barangay.disabled = true;

        const defaults = {
            region: region.dataset.default || '',
            province: province.dataset.default || '',
            city: city.dataset.default || '',
            barangay: barangay.dataset.default || '',
        };
        if (defaults.region && selectOption(region, defaults.region)) loadProvinces(selectedCode(region));
        if (defaults.province && selectOption(province, defaults.province)) loadCities(selectedCode(province));
        if (defaults.city && selectOption(city, defaults.city) && await loadBarangays(selectedCode(city))) selectOption(barangay, defaults.barangay);

        const lat = Number.parseFloat(latitude?.value || '');
        const lon = Number.parseFloat(longitude?.value || '');
        if (Number.isFinite(lat) && Number.isFinite(lon)) updateMap(lat, lon);
    }

    function updateMap(lat, lon) {
        marker?.setLatLng([lat, lon]);
        map?.setView([lat, lon], 17);
        if (latitude) latitude.value = lat;
        if (longitude) longitude.value = lon;
    }

    async function fetchMapJson(url, timeout = 15000) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeout);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            if (!response.ok) throw new Error('The map service is unavailable. Please enter the address manually.');
            return await response.json();
        } finally {
            clearTimeout(timer);
        }
    }

    async function fillAddressFromLocation(lat, lon) {
        const query = new URLSearchParams({ lat, lon, format: 'json', addressdetails: '1', countrycodes: 'ph', zoom: '18', 'accept-language': 'en' });
        const result = await fetchMapJson(`https://nominatim.openstreetmap.org/reverse?${query}`);
        if (!result || (result.address?.country_code && result.address.country_code.toLowerCase() !== 'ph')) throw new Error('Location is outside the Philippines. Please use a Philippine address.');
        const address = result.address || {};
        const provinceNames = [address.province, address.state_district, address.county, address.state, address.region];
        const cityNames = [address.city, address.municipality, address.town, address.city_district, address.county];
        const barangayNames = [address.village, address.neighbourhood, address.quarter, address.suburb, address.hamlet, address.residential];

        if (street) street.value = address.house_number && address.road ? `${address.house_number} ${address.road}` : (address.road || street.value);
        if (postalCode && /^\d{4}$/.test(address.postcode || '')) postalCode.value = address.postcode;

        let matchedProvince = findMatchingItem(provinces, provinceNames, 'province_name');
        let matchedCity = matchedProvince
            ? findMatchingItem(cities, cityNames, 'city_name', (item) => String(item.province_code) === String(matchedProvince.province_code))
            : findMatchingItem(cities, cityNames, 'city_name');
        if (!matchedProvince && matchedCity) matchedProvince = provinces.find((item) => String(item.province_code) === String(matchedCity.province_code));
        if (!matchedProvince) {
            setLocationStatus('Location found, but the province could not be matched. Please verify the address manually.');
            return;
        }

        const matchedRegion = regions.find((item) => String(item.region_code) === String(matchedProvince.region_code));
        matchedCity = matchedCity && String(matchedCity.province_code) === String(matchedProvince.province_code)
            ? matchedCity
            : findMatchingItem(cities, cityNames, 'city_name', (item) => String(item.province_code) === String(matchedProvince.province_code));
        if (matchedRegion && selectOption(region, matchedRegion.region_name)) loadProvinces(selectedCode(region));
        if (!selectOption(province, matchedProvince.province_name)) throw new Error('Location found, but its province could not be selected.');
        loadCities(selectedCode(province));
        if (!matchedCity || !selectOption(city, matchedCity.city_name)) throw new Error('Location found, but its city could not be matched.');
        if (!await loadBarangays(selectedCode(city))) return;

        const match = findMatchingItem([...barangay.options].map((option) => ({ value: option.value })), barangayNames, 'value');
        if (match) {
            barangay.value = match.value;
            setLocationStatus('Location detected. Please verify the address and postal code before saving.');
        } else setLocationStatus('Location detected, but the barangay and postal code need manual confirmation.');
    }

    async function findAddressOnMap() {
        const query = new URLSearchParams({ q: [barangay.value, city.value, province.value, 'Philippines'].filter(Boolean).join(', '), format: 'json', limit: '1', countrycodes: 'ph' });
        const results = await fetchMapJson(`https://nominatim.openstreetmap.org/search?${query}`, 10000);
        if (!results.length) throw new Error('Address not found.');
        updateMap(results[0].lat, results[0].lon);
        setLocationStatus('Location pinned. Please verify the address and postal code before saving.');
    }

    region.addEventListener('change', () => loadProvinces(selectedCode(region)));
    province.addEventListener('change', () => loadCities(selectedCode(province)));
    city.addEventListener('change', () => loadBarangays(selectedCode(city)));

    if (mapElement) {
        map = L.map(mapElement).setView([12.8797, 121.7740], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        marker = L.marker([12.8797, 121.7740], { draggable: true }).addTo(map);
        marker.on('dragend', async () => {
            const position = marker.getLatLng();
            updateMap(position.lat, position.lng);
            try { await fillAddressFromLocation(position.lat, position.lng); } catch (error) { setLocationStatus(error.message, true); }
        });
        map.on('click', async ({ latlng }) => {
            updateMap(latlng.lat, latlng.lng);
            try { await fillAddressFromLocation(latlng.lat, latlng.lng); } catch (error) { setLocationStatus(error.message, true); }
        });
    }

    findButton?.addEventListener('click', async () => {
        findButton.disabled = true;
        findButton.textContent = 'Finding addressâ€¦';
        try { await findAddressOnMap(); } catch (error) { setLocationStatus(error.message, true); }
        finally { findButton.disabled = false; findButton.textContent = 'Find Address on Map'; }
    });

    locateButton?.addEventListener('click', () => {
        if (!navigator.geolocation) return setLocationStatus('Geolocation is unavailable. Please choose the address manually.', true);
        locateButton.disabled = true;
        locateButton.textContent = 'Finding your locationâ€¦';
        navigator.geolocation.getCurrentPosition(async ({ coords }) => {
            try {
                updateMap(coords.latitude, coords.longitude);
                map?.invalidateSize();
                await fillAddressFromLocation(coords.latitude, coords.longitude);
            } catch (error) { setLocationStatus(error.message, true); }
            finally { locateButton.disabled = false; locateButton.textContent = 'Use My Current Location'; }
        }, () => {
            locateButton.disabled = false;
            locateButton.textContent = 'Use My Current Location';
            setLocationStatus('Unable to access your location. Please choose the address manually.', true);
        }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
    });

    document.addEventListener('address-modal-opened', () => requestAnimationFrame(() => map?.invalidateSize()));
    loadAddressData().catch((error) => {
        console.error(error);
        setLocationStatus('Unable to load address selections. Please refresh and try again.', true);
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAddressSelector, { once: true });
else initAddressSelector();

export { initAddressSelector };
