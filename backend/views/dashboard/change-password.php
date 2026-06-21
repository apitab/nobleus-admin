<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\helpers\ViewHelper;

$this->title = "Change my Password";
?>
<div class="content">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="edit" class="feather-medium"></i> ' . Yii::t('app','Change Password'),
            'links' => [
                ['title' => Yii::t('app','Change Password'), 'active' => true]
            ]
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <div class="card-header d-sm-flex justify-content-between bd-b-0 pd-t-20 pd-b-0">
                        <div class="mg-b-10 mg-sm-b-0">
                            <h6 class="mg-b-5"><?= Yii::t('app','Enter password details below'); ?></h6>
                        </div>
                    </div>
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="card-body">             
                        <div class="form-field">
                            <?= $form->field($model, 'oldPassword')->passwordInput(['placeholder' => 'Old password']) ?>
                        </div>
                        <div class="form-field">
                            <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => 'New password']) ?>
                        </div>
                        <div class="form-field">
                            <?= $form->field($model, 'confirmNewPassword')->passwordInput(['placeholder' => 'Confirm password']) ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton(Yii::t('app','Save Changes'), ['class' => 'btn btn-brand-02', 'name' => 'change-password-button']) ?> <?= Yii::t('app','or'); ?> <a href="<?= Yii::$app->request->referrer?>"><?= Yii::t('app','Cancel'); ?></a>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>