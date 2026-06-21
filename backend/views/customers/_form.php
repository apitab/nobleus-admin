<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true]) ?>
    <?php if(!$model->isNewRecord && count($customerAddresses) > 0) : ?>
        <?= $form->field($model, 'primary_address')->dropDownList(ArrayHelper::map($customerAddresses,'id','address'),['class' => 'form-select','prompt' => Yii::t('app','Select primary address')]) ?>
    <?php endif;?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')], ['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Save') : Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app','or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app','Cancel'); ?></a>
</div>
<?php ActiveForm::end(); ?>