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
    return value
        .toLowerCase()
        .replace(/^(barangay|brgy\.?|city of|municipality of)\s+/i, '')
        .replace(/\s+/g, ' ')
        .trim();
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
}

async function reverseGeocode(lat, lon) {
    const query = new URLSearchParams({ lat, lon, format: 'json', addressdetails: '1' });
    const response = await fetchWithTimeout(`https://nominatim.openstreetmap.org/reverse?${query}`, {
        headers: { Accept: 'application/json' },
    }, 10000);

    if (!response.ok) {
        throw new Error('Unable to find the address for this location.');
    }

    return response.json();
}

async function findAddressOnMap() {
    const address = [barangay.value, city.value, province.value, 'Philippines'].filter(Boolean).join(', ');
    const query = new URLSearchParams({ q: address, format: 'json', limit: '1' });
    const response = await fetchWithTimeout(`https://nominatim.openstreetmap.org/search?${query}`, {}, 10000);
    const results = await response.json();

    if (!results.length) {
        throw new Error('Address not found.');
    }

    updateMap(results[0].lat, results[0].lon);
}

async function fillAddressFromLocation(lat, lon) {
    const result = await reverseGeocode(lat, lon);
    const address = result.address || {};
    const provinceName = address.state || address.province || '';
    const cityName = address.city || address.town || address.municipality || address.city_district || '';
    const barangayName = address.village || address.suburb || address.quarter || address.hamlet || address.neighbourhood || '';
    const normalizedProvince = normalizeName(provinceName);
    const normalizedCity = normalizeName(cityName);
    const matchedProvince = provinces.find((item) => normalizeName(item.province_name) === normalizedProvince)
        || provinces.find((item) => normalizeName(item.province_name).includes(normalizedCity) || normalizedCity.includes(normalizeName(item.province_name)));

    street.value = address.house_number && address.road
        ? `${address.house_number} ${address.road}`
        : (address.road || street.value);
    postalCode.value = address.postcode || postalCode.value;

    if (!matchedProvince) {
        return;
    }

    const matchedRegion = regions.find((item) => String(item.region_code) === String(matchedProvince.region_code));
    const matchedCity = cities.find((item) => String(item.province_code) === String(matchedProvince.province_code) && normalizeName(item.city_name) === normalizeName(cityName));

    if (matchedRegion) {
        selectOption(region, matchedRegion.region_name);
    }

    selectOption(province, matchedProvince.province_name);

    if (matchedCity) {
        selectOption(city, matchedCity.city_name);
        if (!selectOption(barangay, barangayName) && barangayName) {
            const partialMatch = [...barangay.options].find((item) => normalizeName(barangayName).includes(normalizeName(item.value)) || normalizeName(item.value).includes(normalizeName(barangayName)));

            if (partialMatch) {
                barangay.value = partialMatch.value;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    map = L.map('map').setView([12.8797, 121.7740], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);
    marker = L.marker([12.8797, 121.7740], { draggable: true }).addTo(map);

    marker.on('dragend', () => {
        const position = marker.getLatLng();
        latitude.value = position.lat;
        longitude.value = position.lng;
    });

    map.on('click', (event) => updateMap(event.latlng.lat, event.latlng.lng));

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
    }, 20000);

    navigator.geolocation.getCurrentPosition(async ({ coords }) => {
        if (locationRequestFinished) {
            return;
        }

        try {
            await addressDataPromise;
            updateMap(coords.latitude, coords.longitude);
            await fillAddressFromLocation(coords.latitude, coords.longitude);
        } catch (error) {
            setLocationStatus(error.name === 'AbortError' ? 'The address lookup timed out. Please try again.' : error.message, true);
        } finally {
            finishLocationRequest();
        }
    }, async (error) => {
        if (locationRequestFinished) {
            return;
        }

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
    }, { enableHighAccuracy: false, timeout: 6000, maximumAge: 300000 });
});
