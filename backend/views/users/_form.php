<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\Users $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'names')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone_number')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'group_id')->dropDownList(ArrayHelper::map($groups, 'id','name'),['prompt' => Yii::t('app','-- Select Group --'),'class' => 'form-select']) ?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')], ['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Save') : Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app','or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app','Cancel'); ?></a>
</div>
<?php ActiveForm::end(); ?>