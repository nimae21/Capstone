let regions = [];
let provinces = [];
let cities = [];
let barangays = [];

$(document).ready(function () {

    Promise.all([
    $.getJSON('/data/region.json'),
    $.getJSON('/data/province.json'),
    $.getJSON('/data/city.json'),
    $.getJSON('/data/barangay.json')
]).then(function(result){

    regions = result[0];
    provinces = result[1];
    cities = result[2];
    barangays = result[3];

    loadRegions();

    $('#province').prop('disabled', true);
    $('#city').prop('disabled', true);
    $('#barangay').prop('disabled', true);

});

});

function loadRegions() {

    $('#region').empty();

    $('#region').append('<option value="">Select Region</option>');

    regions.forEach(function(region){

        $('#region').append(`
            <option
                value="${region.region_name}"
                data-code="${region.region_code}">
                ${region.region_name}
            </option>
        `);

    });

}


// ==========================
// REGION -> PROVINCE
// ==========================

$('#region').change(function () {

    let regionCode = $(this).find(':selected').data('code');

    $('#province').prop('disabled', false);
    $('#city').prop('disabled', true);
    $('#barangay').prop('disabled', true);

    $('#province').empty().append('<option value="">Select Province</option>');
    $('#city').empty().append('<option value="">Select City</option>');
    $('#barangay').empty().append('<option value="">Select Barangay</option>');

    let filteredProvinces = provinces.filter(function(province){
        return province.region_code == regionCode;
    });

    filteredProvinces.forEach(function(province){

        $('#province').append(`
            <option
                value="${province.province_name}"
                data-code="${province.province_code}">
                ${province.province_name}
            </option>
        `);

    });

});


// ==========================
// PROVINCE -> CITY
// ==========================

$('#province').change(function () {

    let provinceCode = $(this).find(':selected').data('code');

    $('#city').prop('disabled', false);
    $('#barangay').prop('disabled', true);

    $('#city').empty().append('<option value="">Select City</option>');
    $('#barangay').empty().append('<option value="">Select Barangay</option>');

    let filteredCities = cities.filter(function(city){
        return city.province_code == provinceCode;
    });

    filteredCities.forEach(function(city){

        $('#city').append(`
            <option
                value="${city.city_name}"
                data-code="${city.city_code}">
                ${city.city_name}
            </option>
        `);

    });

});


// ==========================
// CITY -> BARANGAY
// ==========================

$('#city').change(function () {

    let cityCode = $(this).find(':selected').data('code');

    $('#barangay').prop('disabled', false);

    $('#barangay').empty().append('<option value="">Select Barangay</option>');

    let filteredBarangays = barangays.filter(function(barangay){
        return barangay.city_code == cityCode;
    });

    filteredBarangays.forEach(function(barangay){

        $('#barangay').append(`
            <option value="${barangay.brgy_name}">
                ${barangay.brgy_name}
            </option>
        `);

    });

});

// ==========================
// OPENSTREETMAP
// ==========================

let map = L.map('map').setView([12.8797, 121.7740], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let marker = L.marker([12.8797, 121.7740], {
    draggable: true
}).addTo(map);

marker.on('dragend', function () {

    let position = marker.getLatLng();

    $('#latitude').val(position.lat);
    $('#longitude').val(position.lng);

    console.log(position.lat, position.lng);

});

map.on('click', function (e) {

    marker.setLatLng(e.latlng);

    $('#latitude').val(e.latlng.lat);
    $('#longitude').val(e.latlng.lng);

});

