<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var backend\models\CustomerRequests $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="customer-requests-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'form-layout form-layout-1']]); ?>
    
    <div class="row">
        <div class="col-lg-6">

            <?= $form->field($model, 'customer_address')->textInput() ?>

            <?= $form->field($model, 'volume_requested')->textInput(['type' => 'number', 'min' => '0', 'step' => '0.01', 'placeholder' => 'Enter volume in litres']) ?>

            
        </div>
        <div class="col-lg-6">
            

            

        </div>
    </div>

    <div class="form-layout-footer text-right">
        <?= Html::submitButton(
            $model->isNewRecord ? '<i data-feather="save" class="wd-10 mg-r-5"></i> Create Request' : '<i data-feather="save" class="wd-10 mg-r-5"></i> Update Request',
            ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-success']
        ) ?>
        <?= Html::a('<i data-feather="x" class="wd-10 mg-r-5"></i> Cancel', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
    $(function() {
        // Initialize Feather icons
        if (typeof feather !== 'undefined') feather.replace();

        
    });
</script>
