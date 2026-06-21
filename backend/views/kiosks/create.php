<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Kiosks $model */

$this->title = Yii::t('app', 'Create Kiosks');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Add Kiosk'),
            'links' => [
                ['title' => Yii::t('app','Kiosks'), 'url' => Yii::$app->urlManager->createUrl('kiosks')],
                ['title' => Yii::t('app','Add Kiosk'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
var map;
var addressMap;
var markers = $vendorsJson;
var addresses = $addressesJson;
var selectedMarker = null;

$('#vendorModal').on('shown.bs.modal', function () {
    if (!map) {
        map = L.map('map').setView([9.5642, 44.0504], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        markers.forEach(function(marker) {
            var coords = marker.location_coordinates.split(',');
            var latitude = parseFloat(coords[0]);
            var longitude = parseFloat(coords[1]);

            var leafletMarker = L.marker([latitude, longitude])
                .addTo(map)
                .bindPopup(marker.name);

            leafletMarker.on('click', function () {
                $('#vendorName').val(marker.other_names + ', ' + marker.first_name);
                $('#vendorId').val(marker.id);
                $('#vendorModal').modal('hide');
            });
        });
    }
});

$('#addressModal').on('shown.bs.modal', function () {
    if (!addressMap) {
        addressMap = L.map('addressMap').setView([9.5642, 44.0504], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(addressMap);

        // Add existing addresses as markers
        addresses.forEach(function (address) {
            var coords = address.location_coordinates.split(',');
            var latitude = parseFloat(coords[0]);
            var longitude = parseFloat(coords[1]);

            var leafletMarker = L.marker([latitude, longitude])
                .addTo(addressMap)
                .bindPopup(address.address);

            leafletMarker.on('click', function () {
                selectedMarker = leafletMarker;
                $('#addressName').val(address.address);
                $('#addressId').val(address.id);
                $('#addressCoordinates').val(address.location_coordinates);
                $('#locationDescription').val(address.address);
            });
        });

        // Add click handler for new locations
        addressMap.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            
            // Remove existing markers
            addressMap.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    addressMap.removeLayer(layer);
                }
            });

            // Add new marker
            selectedMarker = L.marker([lat, lng]).addTo(addressMap);
            
            // Update hidden fields
            $('#addressCoordinates').val(lat + ',' + lng);
            
            // If location description is provided, use it
            var description = $('#locationDescription').val();
            if (description) {
                $('#addressName').val(description);
                selectedMarker.bindPopup(description).openPopup();
            }
        });
    }
});

// Handle save button click
$('#saveAddress').on('click', function() {
    var description = $('#locationDescription').val();
    if (!description) {
        alert('Please enter a location description');
        return;
    }
    if (!selectedMarker) {
        alert('Please select a location on the map');
        return;
    }
    
    $('#addressName').val(description);
    if (selectedMarker.getPopup()) {
        selectedMarker.setPopupContent(description);
    } else {
        selectedMarker.bindPopup(description);
    }
    $('#addressModal').modal('hide');
});

// Reset form when modal is hidden
$('#addressModal').on('hidden.bs.modal', function() {
    $('#locationDescription').val('');
    selectedMarker = null;
});
JS
);
?>

