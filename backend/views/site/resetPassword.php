<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \backend\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use common\helpers\ViewHelper;

$this->title = "Reset Password";
?>
<div class="hero-auth">
    <div class="container">
        <?= ViewHelper::displayFlash(); ?>
        <div class="text-center">
            <?= Html::img('/images/logos/logo.png', ['width' => '50px']) ?>
        </div>
        <div class="d-flex justify-content-center ht-100p ">
            <div class="align-items-center justify-content-center pd-t-20" style="min-width: 40%">
                <h4 class="tx-20 tx-sm-24 tx-center">Reset your password</h4>
                <h6 class="text-center mg-b-20">
                    Hargeisa Water Agency | Dhaamiye App
                </h6>
                <p class="tx-color-03 mg-b-30 tx-center">Enter your new password below.</p>

                <div class="wd-100p d-flex flex-column mg-b-40">
                    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                    <div class="form-group">
                        <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control', "placeholder" => "Your new password"]) ?>
                    </div>
                    <?= Html::submitButton('Save Password', ['class' => 'default-btn default-btn-auth default-btn-alt w-100', 'name' => 'reset-password-button']) ?>
                    <?php ActiveForm::end(); ?>
                    <div class="text-center mg-t-20">
                        <a href="<?= Yii::$app->urlManager->createUrl('site/login') ?>" class=""><i data-feather="user-check"></i> Go back to Sign In</a>
                    </div>
                </div>


            </div>
        </div>

    </div>
</div>