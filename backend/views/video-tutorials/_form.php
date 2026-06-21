<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\VideoTutorials $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin([
    'id' => 'video-tutorial-form',
    'options' => ['enctype' => 'multipart/form-data'],
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
]); ?>
<div class="card-body">
    <?= $form->field($model, 'target')->dropDownList(
        \backend\models\VideoTutorials::optsTarget(),
        ['class' => 'form-select', 'prompt' => Yii::t('app', '-- Select Target --')]
    ) ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <label class="form-label"><?= Yii::t('app', 'Video File') ?></label>
        <div class="custom-file">
            <?= Html::activeFileInput($model, 'videoFile', [
                'class' => 'custom-file-input',
                'accept' => 'video/*',
                'id' => 'videotutorials-videofile'
            ]) ?>
            <label class="custom-file-label" for="videotutorials-videofile"><?= Yii::t('app', 'Choose video file...') ?></label>
        </div>
        <?php if ($model->hasErrors('videoFile')): ?>
            <div class="help-block" style="color: #a94442;">
                <?= Html::error($model, 'videoFile') ?>
            </div>
        <?php endif; ?>
        <?php if ($model->video_name): ?>
            <div class="mt-2">
                <small class="text-muted"><?= Yii::t('app', 'Current video:') ?> <?= basename($model->video_name) ?></small>
                <br>
                <a href="<?= Yii::getAlias('@web') . '/' . $model->video_name ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                    <i data-feather="play" class="wd-12 ht-12"></i> <?= Yii::t('app', 'View Current Video') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select']) ?>
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save Video Tutorial') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel') ?></a>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    // Initialize custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Reinitialize Feather icons
    if (typeof feather !== 'undefined') feather.replace();
JS;
$this->registerJs($js);
?>
