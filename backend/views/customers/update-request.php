<?php

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

<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Create New Request'),
            'links' => [
                ['title' => Yii::t('app', 'Manage Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app', 'View Customer'), 'url' => Yii::$app->urlManager->createUrl(['customers/view', 'id' => $model->id])],
                ['title' => Yii::t('app', 'Update Request'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="card-body">
                        <?= $form->field($requestModel, 'vendor_id',['enableClientValidation' => false])->textInput([
                            'id' => 'vendorName',
                            'readonly' => true, // Make it readonly so users cannot type directly
                            'onclick' => '$("#vendorModal").modal("show")', // Open modal on click
                        ]) ?>
                        <?= $form->field($requestModel, 'volume_requested')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($requestModel, 'customer_address')->dropDownList(ArrayHelper::map($addresses, 'id', 'address'), ['class' => 'form-select', 'prompt' => Yii::t('app', 'Select primary address')]) ?>
                    </div>
                    <?= Html::hiddenInput('vendorId', '', ['id' => 'vendorId']) ?>
                    <div class="card-footer">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel'); ?></a>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
var map;
var markers = $vendorsJson;
console.log('HI');
console.log(markers);

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
JS
);
?>