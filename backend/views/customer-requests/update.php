<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use common\helpers\ViewHelper;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\Modal;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */

$this->title = 'Update Request';

$vendorsJson = Json::encode($vendorLocations);

Modal::begin([
    'id' => 'vendorModal',
    'title' => 'Select Vendor',
    'size' => Modal::SIZE_LARGE,
]);
?>
<div id="map" style="height: 500px; width: 100%;"></div>
<?php Modal::end(); ?>

<?php
Modal::begin([
    'id' => 'addressModal',
    'title' => 'Select Delivery Address',
    'size' => Modal::SIZE_LARGE,
]);
?>
<div id="addressMap" style="height: 500px; width: 100%;"></div>
<div class="form-group mt-3">
    <label for="locationDescription">Location Description</label>
    <input type="text" class="form-control" id="locationDescription" placeholder="Enter location description">
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" id="saveAddress">Save Location</button>
</div>
<?php Modal::end(); ?>

<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Update customer request'),
            'links' => [
                ['title' => Yii::t('app', 'Manage Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app', 'View Customer'), 'url' => Yii::$app->urlManager->createUrl(['customers/requests', 'id' => $model->customer_id])],
                ['title' => Yii::t('app', 'Update Request'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">

                    <?php $form = ActiveForm::begin(); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'vendor_id', ['enableClientValidation' => false])->textInput([
                            'value' => $model->vendor->other_names . ', ' . $model->vendor->first_name,
                            'id' => 'vendorName',
                            'readonly' => true,
                            'onclick' => '$("#vendorModal").modal("show")',
                        ]) ?>
                        <?= $form->field($model, 'volume_requested')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'customer_address', ['enableClientValidation' => false])->textInput([
                            'value' => $model->customerAddress ? $model->customerAddress->address : '',
                            'id' => 'addressName',
                            'readonly' => true,
                            'onclick' => '$("#addressModal").modal("show")',
                        ]) ?>
                        <?= Html::hiddenInput('addressId', $model->customer_address, ['id' => 'addressId']) ?>
                        <?= Html::hiddenInput('addressCoordinates', $model->customerAddress ? $model->customerAddress->location_coordinates : '', ['id' => 'addressCoordinates']) ?>
                        <?= $form->field($model, 'delivery_date')->input('date', [
                                'class' => 'form-control form-control-sm'
                        ]); ?>
                        <?= $form->field($model, 'delivery_notes')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Enter any special delivery instructions or notes'
                        ]) ?>
                        <?= $form->field($model, 'status')->dropDownList([
                            StatusCodes::NEW_CUSTOMER_REQUEST => 'New',
                            StatusCodes::NEW_CUSTOMER_REQUEST_OPEN => 'Open',
                            StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST => 'On the Way',
                            StatusCodes::DELIVERED_CUSTOMER_REQUEST => 'Delivered',
                            StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST => 'Payment Initiated',
                            StatusCodes::PAYMENT_AWAITING_CONFIRMATION => 'Payment Awaiting Confirmation',
                            StatusCodes::COMPLETED_CUSTOMER_REQUEST => 'Completed',
                            StatusCodes::CUSTOMER_CANCELLED_REQUEST => 'Cancelled'
                        ], ['prompt' => 'Select Status', 'class' => 'form-select']) ?>
                    </div>
                    <?= Html::hiddenInput('vendorId', $model->vendor_id, ['id' => 'vendorId']) ?>
                    <div class="card-footer">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?>
                        <?= Yii::t('app', 'or') ?> <a
                            href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel'); ?></a>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        // Initialize Feather icons
        if (typeof feather !== 'undefined') feather.replace();
        var map;
        var addressMap;
        var markers = <?= $vendorsJson; ?>;
        var addresses = <?= Json::encode($addresses); ?>;
        var selectedMarker = null;

        $('#vendorModal').on('shown.bs.modal', function () {
            if (!map) {
                map = L.map('map').setView([9.5642, 44.0504], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                markers.forEach(function (marker) {
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
    });
</script>