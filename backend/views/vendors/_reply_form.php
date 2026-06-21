<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'id' => 'reply-form',
    'action' => ['vendors/save-reply', 'id' => $rating->id],
]); ?>

<?= $form->field($rating, 'vendor_reply')->textarea([
    'rows' => 4,
    'class' => 'form-control',
    'placeholder' => Yii::t('app', 'Write your reply...')
])->label(false) ?>

<div class="text-right mg-t-15">
    <?= Html::button(Yii::t('app', 'Cancel'), [
        'class' => 'btn btn-white btn-sm mg-r-5',
        'data-dismiss' => 'modal'
    ]) ?>
    <?= Html::submitButton(Yii::t('app', 'Submit Reply'), [
        'class' => 'btn btn-primary btn-sm'
    ]) ?>
</div>

<?php ActiveForm::end(); ?> 