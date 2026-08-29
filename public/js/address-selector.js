let regions = [];
let provinces = [];
let cities = [];
let barangays = [];

const region = document.getElementById('region');
const province = document.getElementById('province');
const city = document.getElementById('city');
const barangay = document.getElementById('barangay');
const street = document.getElementById('street');
const postalCode = document.getElementById('postal_code');
const latitude = document.getElementById('latitude');
const longitude = document.getElementById('longitude');
const locateButton = document.getElementById('locateMe');
const findButton = document.getElementById('findLocation');
const locationStatus = document.getElementById('locationStatus');

let map;
let marker;
let addressDataPromise;

function setLocationStatus(message, isError = false) {
    locationStatus.textContent = message;
    locationStatus.className = `mt-2 text-sm ${isError ? 'text-red-600' : 'text-gray-500'}`;
}

function fetchWithTimeout(url, options = {}, timeout = 10000) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeout);

    return fetch(url, { ...options, signal: controller.signal }).finally(() => clearTimeout(timer));
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
    const normalizedValue = normalizeName(value);
    const option = [...dropdown.options].find((item) => normalizeName(item.value) === normalizedValue);

    if (!option) {
        return false;
    }

    dropdown.value = option.value;
    dropdown.dispatchEvent(new Event('change'));

    return true;
}

function normalizeName(value) {
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

function findMatchingItem(list, values, nameKey, filter = () => true) {
    const normalizedValues = values.map(normalizeName).filter(Boolean);

    return list.find((item) => {
        if (!filter(item)) {
            return false;
        }

        const itemName = normalizeName(item[nameKey]);

        return normalizedValues.some((value) => itemName === value);
    }) || list.find((item) => {
        if (!filter(item)) {
            return false;
        }

        const itemName = normalizeName(item[nameKey]);

        return normalizedValues.some((value) => itemName.includes(value) || value.includes(itemName));
    });
}

function updateMap(lat, lon) {
    marker.setLatLng([lat, lon]);
    map.setView([lat, lon], 17);
    latitude.value = lat;
    longitude.value = lon;
}

function loadRegions() {
    resetDropdown(region, 'Select Region');
    populateDropdown(region, regions, 'region_name', 'region_code');
}

function loadProvinces(regionCode) {
    resetDropdown(province, 'Select Province');
    resetDropdown(city, 'Select City');
    resetDropdown(barangay, 'Select Barangay');
    province.disabled = false;
    city.disabled = true;
    barangay.disabled = true;
    populateDropdown(province, provinces.filter((item) => String(item.region_code) === String(regionCode)), 'province_name', 'province_code');
}

function loadCities(provinceCode) {
    resetDropdown(city, 'Select City');
    resetDropdown(barangay, 'Select Barangay');
    city.disabled = false;
    barangay.disabled = true;
    populateDropdown(city, cities.filter((item) => String(item.province_code) === String(provinceCode)), 'city_name', 'city_code');
}

function loadBarangays(cityCode) {
    resetDropdown(barangay, 'Select Barangay');
    barangay.disabled = false;
    barangays
        .filter((item) => String(item.city_code) === String(cityCode))
        .forEach((item) => barangay.add(new Option(item.brgy_name, item.brgy_name)));
}

async function loadAddressData() {
    const files = ['region.json', 'province.json', 'city.json', 'barangay.json'];
    const result = await Promise.all(files.map(async (file) => {
        const response = await fetchWithTimeout(`/data/${file}`);

        if (!response.ok) {
            throw new Error(`Unable to load ${file}`);
        }

        return response.json();
    }));

    [regions, provinces, cities, barangays] = result;
    loadRegions();
    province.disabled = true;
    city.disabled = true;
    barangay.disabled = true;

    const initialValues = {
        region: region.dataset.default || region.value || '',
        province: province.dataset.default || province.value || '',
        city: city.dataset.default || city.value || '',
        barangay: barangay.dataset.default || barangay.value || '',
    };

    if (initialValues.region) {
        selectOption(region, initialValues.region);
    }

    if (initialValues.province) {
        selectOption(province, initialValues.province);
    }

    if (initialValues.city) {
        selectOption(city, initialValues.city);
    }

    if (initialValues.barangay) {
        selectOption(barangay, initialValues.barangay);
    }

    const lat = parseFloat(latitude.value || '');
    const lon = parseFloat(longitude.value || '');

    if (!Number.isNaN(lat) && !Number.isNaN(lon) && lat && lon) {
        updateMap(lat, lon);
    }
}

async function reverseGeocode(lat, lon) {
    const query = new URLSearchParams({
        lat,
        lon,
        format: 'json',
        addressdetails: '1',
        countrycodes: 'ph',
        zoom: '18',
        'accept-language': 'en',
    });

    const response = await fetchWithTimeout(`https://nominatim.openstreetmap.org/reverse?${query}`, {
        headers: { Accept: 'application/json' },
    }, 15000);

    if (!response.ok) {
        throw new Error('Unable to find the address for this location.');
    }

    const result = await response.json();

    if (!result || result.address?.country_code && result.address.country_code.toLowerCase() !== 'ph') {
        throw new Error('Location is outside the Philippines. Please use a Philippine address.');
    }

    return result;
}

async function findAddressOnMap() {
    const address = [barangay.value, city.value, province.value, 'Philippines'].filter(Boolean).join(', ');
    const query = new URLSearchParams({ q: address, format: 'json', limit: '1', countrycodes: 'ph' });
    const response = await fetchWithTimeout(`https://nominatim.openstreetmap.org/search?${query}`, {}, 10000);

    if (!response.ok) {
        throw new Error('Unable to find this address.');
    }

    const results = await response.json();

    if (!results.length) {
        throw new Error('Address not found.');
    }

    updateMap(results[0].lat, results[0].lon);
    setLocationStatus('Location pinned. Please verify the address and postal code before saving.', false);
}

async function fillAddressFromLocation(lat, lon) {
    const result = await reverseGeocode(lat, lon);
    const address = result.address || {};
    const provinceNames = [address.province, address.state_district, address.county, address.state, address.region];
    const cityNames = [address.city, address.municipality, address.town, address.city_district, address.county];
    const barangayNames = [
        address.village,
        address.neighbourhood,
        address.quarter,
        address.suburb,
        address.hamlet,
        address.residential,
    ];

    street.value = address.house_number && address.road
        ? `${address.house_number} ${address.road}`
        : (address.road || street.value);
    if (address.postcode && /^\d{4}$/.test(address.postcode)) {
        postalCode.value = address.postcode;
    }

    let matchedProvince = findMatchingItem(provinces, provinceNames, 'province_name');
    let matchedCity = matchedProvince
        ? findMatchingItem(cities, cityNames, 'city_name', (item) =>
            String(item.province_code) === String(matchedProvince.province_code)
        )
        : findMatchingItem(cities, cityNames, 'city_name');

    if (!matchedProvince && matchedCity) {
        matchedProvince = provinces.find((item) =>
            String(item.province_code) === String(matchedCity.province_code)
        );
    }

    if (!matchedProvince) {
        setLocationStatus('Location found, but the province could not be matched to the Philippines dataset. Please verify the address fields manually.', false);
        return;
    }

    const matchedRegion = regions.find((item) => String(item.region_code) === String(matchedProvince.region_code));
    matchedCity = matchedCity && String(matchedCity.province_code) === String(matchedProvince.province_code)
        ? matchedCity
        : findMatchingItem(cities, cityNames, 'city_name', (item) =>
            String(item.province_code) === String(matchedProvince.province_code)
        );

    if (matchedRegion) {
        selectOption(region, matchedRegion.region_name);
    }

    if (!selectOption(province, matchedProvince.province_name)) {
        setLocationStatus('Location found, but the province could not be selected. Please verify the address manually.', true);
        return;
    }

    if (!matchedCity || !selectOption(city, matchedCity.city_name)) {
        setLocationStatus('Location found, but the city could not be matched. Please select it manually.', true);
        return;
    }

    const matchedBarangay = findMatchingItem(
        [...barangay.options].map((option) => ({ value: option.value })),
        barangayNames,
        'value',
    );

    if (matchedBarangay) {
        barangay.value = matchedBarangay.value;
        setLocationStatus('Location detected. Please verify the address and postal code before saving.', false);
    } else {
        setLocationStatus('Location detected, but the barangay and postal code need manual confirmation.', false);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    map = L.map('map').setView([12.8797, 121.7740], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);
    marker = L.marker([12.8797, 121.7740], { draggable: true }).addTo(map);

    marker.on('dragend', async () => {
        const position = marker.getLatLng();
        latitude.value = position.lat;
        longitude.value = position.lng;

        try {
            await addressDataPromise;
            await fillAddressFromLocation(position.lat, position.lng);
        } catch (error) {
            setLocationStatus(error.message, true);
        }
    });

    map.on('click', async (event) => {
        updateMap(event.latlng.lat, event.latlng.lng);

        try {
            await addressDataPromise;
            await fillAddressFromLocation(event.latlng.lat, event.latlng.lng);
        } catch (error) {
            setLocationStatus(error.message, true);
        }
    });

    try {
        addressDataPromise = loadAddressData();
        await addressDataPromise;
    } catch (error) {
        console.error(error);
        setLocationStatus('Unable to load address selections. Please refresh and try again.', true);
    }
});

region.addEventListener('change', () => loadProvinces(region.selectedOptions[0]?.dataset.code || ''));
province.addEventListener('change', () => loadCities(province.selectedOptions[0]?.dataset.code || ''));
city.addEventListener('change', () => loadBarangays(city.selectedOptions[0]?.dataset.code || ''));

findButton.addEventListener('click', async () => {
    findButton.disabled = true;
    findButton.textContent = 'Finding address...';

    try {
        await findAddressOnMap();
    } catch (error) {
        alert(error.message);
    } finally {
        findButton.disabled = false;
        findButton.textContent = '📍 Find Address on Map';
    }
});

locateButton.addEventListener('click', () => {
    if (!navigator.geolocation) {
        setLocationStatus('Geolocation is not supported by this browser. Please choose your address manually on the map.', true);
        return;
    }

    locateButton.disabled = true;
    locateButton.textContent = '📍 Finding your location...';

    let locationRequestFinished = false;
    const resetLocateButton = () => {
        locateButton.disabled = false;
        locateButton.textContent = '📍 Use My Current Location';
    };
    const finishLocationRequest = () => {
        locationRequestFinished = true;
        clearTimeout(watchdog);
        resetLocateButton();
    };
    const watchdog = setTimeout(() => {
        if (locationRequestFinished) {
            return;
        }

        finishLocationRequest();
        setLocationStatus('Location lookup timed out. Please choose your address manually on the map.', true);
    }, 12000);

    navigator.geolocation.getCurrentPosition(async ({ coords }) => {
        if (locationRequestFinished) {
            return;
        }

        const lat = coords.latitude;
        const lon = coords.longitude;

        try {
            updateMap(lat, lon);
            map.invalidateSize();
            setLocationStatus(`Location found. Accuracy: approximately ${Math.round(coords.accuracy)} meters.`);

            await addressDataPromise;
            await fillAddressFromLocation(lat, lon);
        } catch (error) {
            console.error('LOCATION PROCESS ERROR:', error);
            setLocationStatus(error.name === 'AbortError' ? 'The address lookup timed out. Please try again.' : error.message, true);
        } finally {
            finishLocationRequest();
        }
    }, async (error) => {
        if (locationRequestFinished) {
            return;
        }

        console.warn('LOCATION ERROR:', {
            code: error.code,
            message: error.message,
            permission: await navigator.permissions?.query({ name: 'geolocation' }).then((result) => result.state).catch(() => 'unknown'),
        });

        if (error.code === 1) {
            finishLocationRequest();
            setLocationStatus('Location permission was denied. Enable location access for this site, then try again.', true);
            return;
        }

        finishLocationRequest();

        const message = {
            2: 'Your device location is unavailable. Please pin your location or select the address manually.',
            3: 'Location lookup timed out. Please pin your location or select the address manually.',
        }[error.code] || 'Unable to access your location. Please choose your address manually on the map.';

        setLocationStatus(message, true);
       }, {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 300000,
    });
        
});
