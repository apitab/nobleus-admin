<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\Vendors $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<div class="card-body">
    <!-- Personal Information Section -->
    <div class="pd-20 pd-sm-30">
        <h6 class="tx-uppercase tx-semibold mg-b-20 tx-color-01">
            <i data-feather="user" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Personal Information') ?>
        </h6>
        <div class="row row-sm">
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'first_name')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'other_names')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'mobile_number')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm',
                    'placeholder' => '07XXXXXXXX'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Business Information Section -->
    <div class="pd-20 pd-sm-30 bd-t">
        <h6 class="tx-uppercase tx-semibold mg-b-20 tx-color-01">
            <i data-feather="briefcase" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Business Information') ?>
        </h6>
        <div class="row row-sm">
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'owner_rental')->dropDownList(
                    ['owner' => 'Owner', 'rental' => 'Rental'],
                    [
                        'prompt' => Yii::t('app','-- Select Type --'),
                        'class' => 'form-select form-select-sm'
                    ]
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'owners_name')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'owner_phone_number')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm',
                    'placeholder' => '07XXXXXXXX'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'govt_reg')->dropDownList(
                    ['NO' => 'No', 'YES' => 'YES'],
                    ['class' => 'form-select form-select-sm']
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'vendor_type')->dropDownList(
                    [
                        'vendor' => 'Mobile Vendor',
                        'kiosk' => 'Water Kiosk'
                    ],
                    [
                        'class' => 'form-select form-select-sm',
                        'prompt' => Yii::t('app','-- Select Vendor Type --')
                    ]
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'vendor_group')->dropDownList(
                    ArrayHelper::map($vendorGroups, 'id', 'name'),
                    [
                        'class' => 'form-select form-select-sm',
                        'prompt' => Yii::t('app','-- Select Vendor Group --')
                    ]
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'status')->dropDownList(
                    [
                        StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'),
                        StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')
                    ],
                    ['class' => 'form-select form-select-sm']
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'vehicle_registration')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Vehicle Registration Number'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Location & Water Source Section -->
    <div class="pd-20 pd-sm-30 bd-t">
        <h6 class="tx-uppercase tx-semibold mg-b-20 tx-color-01">
            <i data-feather="map-pin" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Location & Water Source') ?>
        </h6>
        <div class="row row-sm">
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'residential_location')->dropDownList(
                    ArrayHelper::map($locations, 'id', 'name'),
                    [
                        'class' => 'form-select form-select-sm',
                        'prompt' => Yii::t('app','-- Select Location --')
                    ]
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'location_coordinates')->textInput([
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'lat,long'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'water_source')->dropDownList(
                    ArrayHelper::map($waterSources, 'id', 'name'),
                    [
                        'class' => 'form-select form-select-sm',
                        'prompt' => Yii::t('app','-- Select Water Source --')
                    ]
                ) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'tank_volume')->textInput([
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Volume in liters'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'moq')->textInput([
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Number of barrels'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'borehole_id')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm'
                ]) ?>
            </div>
            <div class="col-lg-4 col-sm-12">
                <?= $form->field($model, 'kiosk_id')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-sm'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Profile Picture Section -->
    <div class="pd-20 pd-sm-30 bd-t">
        <h6 class="tx-uppercase tx-semibold mg-b-20 tx-color-01">
            <i data-feather="image" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Profile Picture') ?>
        </h6>
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="custom-file">
                    <?= $form->field($model, 'imageFile')->fileInput([
                        'class' => 'custom-file-input',
                        'accept' => 'image/*'
                    ])->label(false) ?>
                    <label class="custom-file-label"><?= Yii::t('app', 'Choose file...') ?></label>
                </div>
                <?php if ($model->display_pic): ?>
                    <div class="mt-2">
                        <img src="<?= $model->display_pic ?>" class="wd-80 rounded" alt="">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card-footer bg-light">
    <div class="d-flex justify-content-between">
        <div>
            <?= Html::submitButton(
                $model->isNewRecord ? 
                    '<i data-feather="save" class="wd-15 ht-15 stroke-2 mg-r-5"></i> ' . Yii::t('app','Save') : 
                    '<i data-feather="save" class="wd-15 ht-15 stroke-2 mg-r-5"></i> ' . Yii::t('app','Save Changes'),
                ['class' => 'btn btn-primary']
            ) ?>
        </div>
        <div>
            <?= Html::a(
                '<i data-feather="x" class="wd-15 ht-15 stroke-2 mg-r-5"></i> ' . Yii::t('app','Cancel'),
                Yii::$app->request->referrer,
                ['class' => 'btn btn-outline-secondary']
            ) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    // Initialize custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Reinitialize Feather icons
    if (typeof feather !== 'undefined') feather.replace();
JS;
$this->registerJs($js);
?>