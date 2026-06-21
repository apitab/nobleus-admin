<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use common\helpers\ViewHelper;
use yii\widgets\ActiveForm;
use backend\models\Currencies;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\AppSettings $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Manage Customer Mobile App Settings';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Mobile App Settings'),
            'links' => [
                ['title' => Yii::t('app', 'Mobile App Settings'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <?php $form = ActiveForm::begin(); ?>
                <div class="card">
                    <div class="card-header bg-secondary">
                        <h6 class="tx-white"><?= Yii::t('app', 'Mobile App Configuration Settings') ?></h6>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'app_name')->textInput() ?>
                        <?= $form->field($model, 'default_currency_id')->dropDownList(ArrayHelper::map(Currencies::findAll(['status' => StatusCodes::ACTIVE_STATUS]),'id','name'),['class'=>'form-select']) ?>
                        <?= $form->field($model, 'default_mobile_language')->dropDownList(['en' => 'English','som' => 'Somal'],['class' => 'form-select']) ?>
                        <?= $form->field($model, 'enable_otp')->dropDownList([1 => Yii::t('app','Enabled'), 0 => Yii::t('app','Disabled')],['class' => 'form-select'])?>
                        <?= $form->field($model, 'is_online')->dropDownList([1 => Yii::t('app','Enabled'), 0 => Yii::t('app','Disabled')],['class' => 'form-select'])?>
                        <?= $form->field($model, 'usd_sos_rate')->textInput() ?>
                        <?= $form->field($model, 'price_per_barrel')->textInput() ?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save Address') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel') ?></a>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>