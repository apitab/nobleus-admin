<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Withdrawals $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="withdrawals-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'from_type')->dropDownList([ 'customer' => 'Customer', 'vendor' => 'Vendor', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'from_id')->textInput() ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
