<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

use common\helpers\ViewHelper;
$this->title = "Sign in to your Account";
?>
<div class="hero-auth">
    <div class="container">
        <div class="text-center">
            <?= Html::img('/images/logos/logo.png', ['width' => '50px']) ?>
        </div>
        <div class="d-flex justify-content-center ht-100p ">
            <div class="align-items-center justify-content-center pd-t-20" style="min-width: 40%">
                <h4 class="tx-20 tx-sm-24 text-center">Sign in to your Account</h4>
                <h6 class="text-center mg-b-20">
                    Demo
                </h6>
                <?= ViewHelper::displayFlash(); ?>
                <div class="wd-100p d-flex flex-column mg-b-40">
                    <?php $form = ActiveForm::begin(['id' => 'form-signin']); ?>
                    <div class="form-group">
                        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'class' => 'form-control', 'placeholder' => 'Your email address']) ?>
                    </div>
                    <div class="form-group">
                        <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control', 'placeholder' => 'Your password']) ?>
                    </div>
                    <div class="form-group">
                        <?= $form->field($model, 'language')->dropDownList(['en' => 'English','som' => 'Somali'],['class' => 'form-select']) ?>
                    </div>
                    <div class="mg-b-20"><a href="<?= Yii::$app->urlManager->createUrl('site/request-password-reset') ?>" class="tx-13">Forgot password? / Miyaad hilmaantay furaha sirta ah?</a></div>
                    <?= Html::submitButton('Sign In / Gal', ['class' => 'default-btn default-btn-auth default-btn-alt w-100', 'name' => 'signin-button']) ?>
                    <?php ActiveForm::end(); ?>
                </div>


            </div>
        </div>

    </div>
</div>