<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var backend\models\VendorCertifications $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'certification_details')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'expiry_date')->textInput() ?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Save') : Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app','or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app','Cancel'); ?></a>
</div>
<?php ActiveForm::end(); ?>
<script type="text/javascript">
    $('#vendorcertifications-expiry_date').datepicker({format: 'yyyy-mm-dd', todayHightlight: true, autoclose: true});
</script>