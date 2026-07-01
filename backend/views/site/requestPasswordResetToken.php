<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use common\helpers\ViewHelper;

$this->title = "Request Password Reset";
?>
<div class="hero-auth">
    <div class="container">
        <?= ViewHelper::displayFlash(); ?>
        <div class="text-center">
            <?= Html::img('/images/logos/logo.png', ['width' => '50px']) ?>
        </div>
        
        <div class="d-flex justify-content-center ht-100p ">
            <div class="align-items-center justify-content-center pd-t-20">
                <h4 class="tx-20 tx-sm-24 text-center">Reset your password</h4>
                <h6 class="text-center mg-b-20">
                    Demo
                </h6>
                <p class="tx-color-03 mg-b-30 text-center">Enter your email address and we will send you a link to reset your password.</p>
                <div class="wd-100p d-flex flex-column mg-b-40">
                    <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                    <div class="form-group">
                        <?= $form->field($model, 'email_address')->textInput(['class' => 'form-control', "placeholder" => "Enter email address"]) ?>
                    </div>
                    <div class="text-center">
                        <?= Html::submitButton('Reset password / Dib u deji furaha sirta ah', ['class' => 'default-btn default-btn-main default-btn-auth w-100']) ?>
                    </div>
                    <div class="text-center mg-t-20">
                        <a href="<?= Yii::$app->urlManager->createUrl('site/login') ?>" class=""><i data-feather="user-check" class="feather-small"></i> Go back to Sign In / Ku noqo Gal</a>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>


            </div>
        </div>
    </div>
</div>