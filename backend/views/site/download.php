<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

use common\helpers\ViewHelper;
$this->title = "Download Apps";
?>
<div class="hero-auth">
    <div class="container">
        <div class="text-center">
            <?= Html::img('/images/logos/logo.png', ['width' => '50px']) ?>
        </div>
        <div class="d-flex justify-content-center ht-100p ">
            <div class="align-items-center justify-content-center pd-t-20" style="min-width: 60%">
                <h4 class="tx-20 tx-sm-24 text-center">Downloads</h4>
                <h6 class="text-center mg-b-20">
                    Demo | Mobile Applications
                </h6>
                <?= ViewHelper::displayFlash(); ?>
                
                <div class="row mg-b-40">
                    <!-- User App - Demo -->
                    <div class="col-md-6 mg-b-20">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-users me-2"></i>Demo - User App
                                </h5>
                            </div>
                        <div class="card-body">
                                <div class="text-center mg-b-20">
                                    <i class="fa fa-mobile-alt fa-3x text-primary"></i>
                                </div>
                                <h6 class="card-subtitle mb-3 text-muted">For Water Consumers</h6>
                            <div class="card-text">
                                    <ul class="list-unstyled mg-b-20">
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Request water delivery
                                        </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Track delivery status
                                        </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Manage your account
                                        </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Local language support
                                    </li>
                                    </ul>
                                </div>
                                <div class="text-center">
                                    <?= Html::a(
                                        '<i class="fa fa-download me-2"></i>Download Demo App',
                                        '@web/app/user-app-release.apk',
                                        [
                                            'class' => 'btn btn-primary btn-lg w-100',
                                            'download' => 'dhaamiye-user-app.apk'
                                        ]
                                    ) ?>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <small class="text-muted">Version 1.0.1 | Android APK</small>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor App - Demo -->
                    <div class="col-md-6 mg-b-20">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-truck me-2"></i>Demo - Vendor App
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mg-b-20">
                                    <i class="fa fa-truck fa-3x text-success"></i>
                                </div>
                                <h6 class="card-subtitle mb-3 text-muted">For Water Vendors</h6>
                                <div class="card-text">
                                    <ul class="list-unstyled mg-b-20">
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Accept delivery requests
                                    </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Manage deliveries
                                    </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Track earnings
                                    </li>
                                        <li class="mg-b-10">
                                            <i class="fa fa-check text-success me-2"></i>
                                            Enhanced vendor features
                                    </li>
                                </ul>
                                </div>
                                <div class="text-center">
                                    <?= Html::a(
                                        '<i class="fa fa-download me-2"></i>Download Demo App',
                                        '@web/app/vendor-app-release.apk',
                                        [
                                            'class' => 'btn btn-success btn-lg w-100',
                                            'download' => 'darawal-vendor-app.apk'
                                        ]
                                    ) ?>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <small class="text-muted">Version 1.0.1 | Android APK</small>
                            </div>
                        </div>
                    </div> 
                </div>

                <!-- Additional Information -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-info-circle me-2"></i>Installation Instructions
                        </h5>
                        <div class="card-text">
                            <ol class="mg-b-0">
                                <li class="mg-b-10">Download the APK file for your app</li>
                                <li class="mg-b-10">Enable "Install from Unknown Sources" in your Android settings</li>
                                <li class="mg-b-10">Open the downloaded APK file and tap "Install"</li>
                                <li class="mg-b-10">Follow the on-screen instructions to complete installation</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>