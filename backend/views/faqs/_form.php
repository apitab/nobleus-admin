<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Faqs $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'target')->dropDownList(
        \backend\models\Faqs::optsTarget(),
        ['class' => 'form-select', 'prompt' => Yii::t('app', '-- Select Target --')]
    ) ?>
    <?= $form->field($model, 'type')->dropDownList([
        'faq' => Yii::t('app', 'FAQ'),
        'guide' => Yii::t('app', 'Guide'),
    ],['class' => 'form-select', 'prompt' => Yii::t('app', '-- Select Type --')]) ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save FAQ') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>