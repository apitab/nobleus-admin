<?php

use backend\models\Locations;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var backend\models\Kiosks $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'location_id')->dropDownList(ArrayHelper::map(Locations::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->all(), 'id', 'name'), ['placeholder' => '-- Select Location', 'class' => 'form-select form-select-sm'])?>
    <?= $form->field($model, 'location_desc')->textarea() ?>
    <?= $form->field($model, 'location_coordinates')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'operating_hours')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'is_open')->dropDownList([ '0' => 'Closed', '1'=>'Open', ], ['prompt' => ' -- Open Status -- ', 'class' => 'form-select']) ?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Save Kiosk') : Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app','or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app','Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>