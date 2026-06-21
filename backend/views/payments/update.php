<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\PaymentMethods;
use backend\helpers\StatusCodes;


/** @var yii\web\View $this */
/** @var backend\models\Payments $model */

$this->title = Yii::t('app', 'Update Payments: {name}', [
    'name' => $model->id,
]);

?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Update Payment Details'),
            'links' => [
                ['title' => Yii::t('app', 'Update Request'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'request_id')->hiddenInput()->label(false) ?>
                        
                        <div class="form-group">
                            <label class="form-label"><?= Yii::t('app', 'Customer') ?></label>
                            <?= Html::textInput('customer', $model->request->customer->alias, [
                                'class' => 'form-control',
                                'readonly' => true
                            ]) ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= Yii::t('app', 'Vendor') ?></label>
                            <?= Html::textInput('vendor', $model->request->vendor->other_names . ', ' . $model->request->vendor->first_name, [
                                'class' => 'form-control',
                                'readonly' => true
                            ]) ?>
                        </div>

                        <?= $form->field($model, 'payment_method_id')->dropDownList(ArrayHelper::map(PaymentMethods::findAll(['enabled' => StatusCodes::ACTIVE_STATUS]), 'id', 'name'),['class' => 'form-select','prompt' => Yii::t('app','-- Select Payment Method --')])?>
                        
                        <?= $form->field($model, 'status')->dropDownList([
                            StatusCodes::PAYMENT_NEW => Yii::t('app', 'Pending processing'),
                            StatusCodes::PAYMENT_CUSTOMER_CONFIRMED => Yii::t('app', 'Customer Confirmed'),
                            StatusCodes::PAYMENT_VENDOR_CONFIRMED => Yii::t('app', 'Vendor Confirmed'),
                            StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST => Yii::t('app', 'Request Initiated to customer'),
                            StatusCodes::PAYMENT_SUCCESS => Yii::t('app', 'Payment Successful'),
                            StatusCodes::PAYMENT_FAILED => Yii::t('app', 'Payment Failed'),
                            StatusCodes::PAYMENT_RETRY => Yii::t('app', 'Payment marked for retry')
                        ], ['class' => 'form-select']) ?>

                        <?= $form->field($model, 'amount')->textInput() ?>
                        <?= $form->field($model, 'description')->textInput() ?>
                        <?= $form->field($model, 'phone_number')->textInput() ?>
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
