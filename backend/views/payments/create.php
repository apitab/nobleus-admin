<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\PaymentMethods;
use backend\helpers\StatusCodes;


/** @var yii\web\View $this */
/** @var backend\models\Payments $model */

$this->title = Yii::t('app', 'Add Payment Details');

?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Add Payment Details'),
            'links' => [
                ['title' => Yii::t('app', 'Add Payment Details'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'request_id')->hiddenInput()->label(false) ?>
                        <?= $form->field($model, 'payment_method_id')->dropDownList(ArrayHelper::map(PaymentMethods::findAll(['enabled' => StatusCodes::ACTIVE_STATUS]), 'id', 'name'),['class' => 'form-select','prompt' => Yii::t('app','-- Select Payment Method --')])?>
                        <?= $form->field($model, 'amount')->textInput() ?>
                        <?= $form->field($model, 'description')->textInput() ?>
                        <?= $form->field($model, 'phone_number')->textInput() ?>
                        <?= $form->field($model, 'date_created')->input('datetime-local', ['class' => 'form-control']) ?>
                        
                        <?= $form->field($model, 'status')->dropDownList(StatusCodes::getPaymentStatusConstants(), ['class' => 'form-select','prompt' => Yii::t('app','-- Select Status --')])?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save Changes'), ['class' => 'btn btn-brand-02']) ?> <?= Yii::t('app', 'or') ?> <a href="<?= Yii::$app->request->referrer ?>"><?= Yii::t('app', 'Cancel'); ?></a>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>        
    </div>
</div>
