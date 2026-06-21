<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\PaymentMethods $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'method_metadata')->textarea(['rows' => 6]) ?>
    
    <?= $form->field($model, 'code')->textInput() ?>

    <?= $form->field($model, 'image')->textInput() ?>

    <?= $form->field($model, 'enabled')->dropDownList(['0' => 'Disabled', '1' => 'Enabled',], ['prompt' => '-- Select Status --','class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save Payment Method') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>