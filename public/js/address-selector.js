// =====================================================
// DATA
// =====================================================

let regions = [];
let provinces = [];
let cities = [];
let barangays = [];

// =====================================================
// ELEMENTS
// =====================================================

const $region = $('#region');
const $province = $('#province');
const $city = $('#city');
const $barangay = $('#barangay');

const $street = $('#street');
const $postalCode = $('#postal_code');
const $latitude = $('#latitude');
const $longitude = $('#longitude');

// =====================================================
// INITIALIZE
// =====================================================

$(document).ready(function () {

    Promise.all([
        $.getJSON('/data/region.json'),
        $.getJSON('/data/province.json'),
        $.getJSON('/data/city.json'),
        $.getJSON('/data/barangay.json')
    ]).then(function (result) {

        regions = result[0];
        provinces = result[1];
        cities = result[2];
        barangays = result[3];

        loadRegions();

        disableDropdowns();

    });

});

// =====================================================
// HELPERS
// =====================================================

function disableDropdowns() {

    $province.prop('disabled', true);
    $city.prop('disabled', true);
    $barangay.prop('disabled', true);

}

function resetDropdown($dropdown, placeholder) {

    $dropdown
        .empty()
        .append(`<option value="">${placeholder}</option>`);

}

function populateDropdown($dropdown, list, textKey, codeKey) {

    list.forEach(function (item) {

        $dropdown.append(`
            <option
                value="${item[textKey]}"
                data-code="${item[codeKey]}">
                ${item[textKey]}
            </option>
        `);

    });

}

function updateMap(lat, lon) {

    marker.setLatLng([lat, lon]);
    map.setView([lat, lon], 18);

    $latitude.val(lat);
    $longitude.val(lon);

}

// =====================================================
// LOAD REGIONS
// =====================================================

function loadRegions() {

    resetDropdown($region, "Select Region");

    populateDropdown(
        $region,
        regions,
        "region_name",
        "region_code"
    );

}

// =====================================================
// REGION CHANGE
// =====================================================

$region.change(function () {

    const regionCode = $(this).find(':selected').data('code');

    $province.prop('disabled', false);
    $city.prop('disabled', true);
    $barangay.prop('disabled', true);

    resetDropdown($province, "Select Province");
    resetDropdown($city, "Select City");
    resetDropdown($barangay, "Select Barangay");

    const filtered = provinces.filter(function (province) {

        return province.region_code == regionCode;

    });

    populateDropdown(
        $province,
        filtered,
        "province_name",
        "province_code"
    );

});

// =====================================================
// PROVINCE CHANGE
// =====================================================

$province.change(function () {

    const provinceCode = $(this).find(':selected').data('code');

    $city.prop('disabled', false);
    $barangay.prop('disabled', true);

    resetDropdown($city, "Select City");
    resetDropdown($barangay, "Select Barangay");

    const filtered = cities.filter(function (city) {

        return city.province_code == provinceCode;

    });

    populateDropdown(
        $city,
        filtered,
        "city_name",
        "city_code"
    );

});

// =====================================================
// CITY CHANGE
// =====================================================

$city.change(function () {

    const cityCode = $(this).find(':selected').data('code');

    $barangay.prop('disabled', false);

    resetDropdown($barangay, "Select Barangay");

    const filtered = barangays.filter(function (barangay) {

        return barangay.city_code == cityCode;

    });

    filtered.forEach(function (barangay) {

        $barangay.append(`
            <option value="${barangay.brgy_name}">
                ${barangay.brgy_name}
            </option>
        `);

    });

});

// =====================================================
// FIND LOCATION
// =====================================================

$('#findLocation').click(function () {

    const address = `${$barangay.val()}, ${$city.val()}, ${$province.val()}, Philippines`;

    $.get(
        "https://nominatim.openstreetmap.org/search",
        {
            q: address,
            format: "json",
            limit: 1
        },
        function (result) {

            if (!result.length) {

                alert("Address not found.");
                return;

            }

            updateMap(result[0].lat, result[0].lon);

        }
    );

});

// =====================================================
// LOCATE ME
// =====================================================

$('#locateMe').click(function () {

    if (!navigator.geolocation) {

        alert("Geolocation is not supported.");
        return;

    }

    navigator.geolocation.getCurrentPosition(function (position) {

        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        updateMap(lat, lon);

        $.get(
            "https://nominatim.openstreetmap.org/reverse",
            {
                lat: lat,
                lon: lon,
                format: "json"
            },
            function (result) {

                console.log(result);

                const address = result.address;

                $street.val(

                    address.house_number && address.road
                        ? `${address.house_number} ${address.road}`
                        : (address.road || "")

                );

                $postalCode.val(address.postcode || "");

                const provinceName = address.state || "";
                const cityName = address.city || address.town || address.municipality || "";
                const barangayName = address.village || address.suburb || address.hamlet || "";

                const province = provinces.find(function (p) {

                    return p.province_name.toLowerCase() === provinceName.toLowerCase();

                });

                if (!province) {

                    console.log("Province not found.");
                    return;

                }

                const region = regions.find(function (r) {

                    return r.region_code === province.region_code;

                });

                if (!region) {

                    console.log("Region not found.");
                    return;

                }

                // Select Region
                $region
                    .val(region.region_name)
                    .trigger('change');

                // Province
                setTimeout(function () {

                    $province
                        .val(provinceName)
                        .trigger('change');

                }, 300);

                // City
                setTimeout(function () {

                    $city
                        .val(cityName)
                        .trigger('change');

                }, 600);

                // Barangay
                setTimeout(function () {

                    $barangay.val(barangayName);

                }, 900);

            }
        );

    });

});

// =====================================================
// LEAFLET MAP
// =====================================================

const map = L.map('map').setView([12.8797, 121.7740], 6);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '&copy; OpenStreetMap contributors'
    }
).addTo(map);

const marker = L.marker(
    [12.8797, 121.7740],
    {
        draggable: true
    }
).addTo(map);

marker.on('dragend', function () {

    const position = marker.getLatLng();

    $latitude.val(position.lat);
    $longitude.val(position.lng);

});

map.on('click', function (e) {

    marker.setLatLng(e.latlng);

    $latitude.val(e.latlng.lat);
    $longitude.val(e.latlng.lng);

});