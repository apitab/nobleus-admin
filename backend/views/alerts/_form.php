<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Alerts $model */
/** @var yii\widgets\ActiveForm $form */
?>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<div class="card-body">
    <?= $form->field($model, 'level')->dropDownList(
        \backend\models\Alerts::optsLevel(),
        ['class' => 'form-select', 'prompt' => Yii::t('app', 'Select Level')]
    ) ?>
    <?= $form->field($model, 'type')->dropDownList(
        \backend\models\Alerts::optsType(),
        ['class' => 'form-select', 'prompt' => Yii::t('app', 'Select Type')]
    ) ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'expiry_date')->input('date') ?>
    <?= $form->field($model, 'attachmentFile')->fileInput(['accept' => 'application/pdf'])->hint(Yii::t('app', 'Optional: Upload a PDF file (max 10MB)')) ?>
    <?php if (!$model->isNewRecord && $model->attachment): ?>
        <div class="form-group">
            <label class="form-label"><?= Yii::t('app', 'Current Attachment') ?></label>
            <div>
                <a href="<?= Yii::getAlias('@web') . '/' . $model->attachment ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i data-feather="file" class="wd-10 ht-10"></i> <?= Yii::t('app', 'View PDF') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select']) ?>
    </div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save Alert') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>
