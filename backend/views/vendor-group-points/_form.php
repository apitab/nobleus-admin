<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\VendorGroupPoints $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'group_id')->dropDownList(
        ArrayHelper::map($groups, 'id', 'name'),
        [
            'class' => 'form-select form-select-sm',
            'prompt' => Yii::t('app', '-- Select Vendor Group --')
        ]
    ) ?>

    <?= $form->field($model, 'min_distance')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'max_distance')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'points_per_km')->textInput(['maxlength' => true]) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?>
    <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel'); ?></a>
</div>
<?php ActiveForm::end(); ?>