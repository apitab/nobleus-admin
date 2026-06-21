<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\InformationGuides;

/** @var yii\web\View $this */
/** @var backend\models\InformationGuides $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="card">
    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
            <i data-feather="file-text" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Guide Information') ?>
        </h6>
    </div>
    <div class="card-body pd-20">
        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'guide-form'],
        ]); ?>

        <?= $form->field($model, 'title')->textInput([
            'maxlength' => true,
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Enter guide title'),
        ]) ?>

        <?= $form->field($model, 'target')->dropDownList(
            InformationGuides::optsTarget(),
            [
                'class' => 'form-select',
                'prompt' => Yii::t('app', '-- Select Target --'),
            ]
        ) ?>

        <?= $form->field($model, 'type')->dropDownList(
            InformationGuides::optsType(),
            [
                'class' => 'form-select',
                'prompt' => Yii::t('app', '-- Select Guide Type --'),
            ]
        ) ?>

        <?= $form->field($model, 'description')->textarea([
            'rows' => 6,
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Enter guide description'),
        ]) ?>

        <?= $form->field($model, 'status')->dropDownList([
            1 => Yii::t('app', 'Active'),
            0 => Yii::t('app', 'Inactive'),
        ], [
            'class' => 'form-select',
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton(
                $model->isNewRecord ? 
                    '<i data-feather="save" class="wd-15 ht-15 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Create Guide') :
                    '<i data-feather="save" class="wd-15 ht-15 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Update Guide'),
                ['class' => 'btn btn-primary']
            ) ?>
            <?= Html::a(
                '<i data-feather="x" class="wd-15 ht-15 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Cancel'),
                ['index'],
                ['class' => 'btn btn-outline-secondary mg-l-5']
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php $this->registerCss(<<<CSS
    .guide-form .form-group {
        margin-bottom: 20px;
    }
    
    .guide-form .form-control,
    .guide-form .form-select {
        border-color: rgba(72, 94, 144, 0.16);
    }
    
    .guide-form .form-control:focus,
    .guide-form .form-select:focus {
        border-color: #0168fa;
    }
    
    .guide-form textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .guide-form .help-block {
        font-size: 12px;
        margin-top: 5px;
        color: #dc3545;
    }
CSS
); ?>

<?php $this->registerJs(<<<JS
    $(function() {
        // Initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();
    });
JS
); ?>
