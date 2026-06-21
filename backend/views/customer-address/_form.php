<?php

use yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\CustomerAddress $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'location_id')->dropDownList(ArrayHelper::map($locations,'id','name'),['class' => 'form-select']) ?>
    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'location_coordinates')->textInput() ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Save Address') : Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app','or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app','Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>