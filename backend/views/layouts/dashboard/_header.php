<?php

use common\helpers\StatusCodes;
use backend\helpers\ViewHelper;



?>
<header class="navbar navbar-header">
    <a href="" id="mainMenuOpen" class="burger-menu"><i data-feather="menu"></i></a>
    <div class="navbar-brand">
        <a href="<?= Yii::$app->homeUrl; ?>" class="df-logo">Demo<span>App</span></a>
    </div><!-- navbar-brand -->
    <div id="navbarMenu" class="navbar-menu-wrapper">
        <div class="navbar-menu-header">
            <a href="<?= Yii::$app->homeUrl; ?>" class="df-logo">Demo<span>App</span></a>
            <a id="mainMenuClose" href=""><i data-feather="x"></i></a>
        </div><!-- navbar-menu-header -->
        <ul class="nav navbar-menu">
            <li class="nav-label pd-l-20 pd-lg-l-25 d-lg-none">Main Navigation</li>
            <li class="nav-item">
                <a href="<?= Yii::$app->homeUrl; ?>" class="nav-link"><i data-feather="home"></i> <?= Yii::t('app','Dashboard'); ?></a>
            </li>
            <li class="nav-item with-sub">
                <a href="#" class="nav-link"><i data-feather="git-pull-request"></i> <?= Yii::t('app','Demo Admin') ?></a>
                <div class="navbar-menu-sub">
                    <div class="d-lg-flex">
                        <ul>
                            <li class="nav-label">System</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('customer-requests') ?>" class="nav-sub-link"><i data-feather="git-pull-request"></i> <?= Yii::t('app','Requests') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('payments') ?>" class="nav-sub-link"><i data-feather="credit-card"></i> <?= Yii::t('app','Payments') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('withdrawals') ?>" class="nav-sub-link"><i data-feather="download"></i> <?= Yii::t('app','Withdrawals') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('vendors/registrations') ?>" class="nav-sub-link"><i data-feather="truck"></i> <?= Yii::t('app','Vendor Registrations') ?></a></li>
                            <li class="nav-label mg-t-20">Vendor & Kiosks</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('vendors') ?>" class="nav-sub-link"><i data-feather="truck"></i> <?= Yii::t('app','Vendors') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('kiosks') ?>" class="nav-sub-link"><i data-feather="map-pin"></i> <?= Yii::t('app','Kiosks') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('vendors/profile-updates') ?>" class="nav-sub-link"><i data-feather="map-pin"></i> <?= Yii::t('app','Vendor Profile Updates') ?></a></li>
                        </ul>
                        <ul>
                            <li class="nav-label">Customers</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('customers') ?>" class="nav-sub-link"><i data-feather="user"></i> <?= Yii::t('app','Manage Customers') ?></a></li>
                            <li class="nav-label mg-t-20">Complaints & Feedback</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('complaints') ?>" class="nav-sub-link"><i data-feather="list"></i> <?= Yii::t('app','Complaints & Feedback') ?></a></li>
                        </ul>
                    </div>
                </div>
            </li>
            <li class="nav-item with-sub">
                <a href="#" class="nav-link"><i data-feather="zap"></i> <?= Yii::t('app','Billing') ?></a>
                <div class="navbar-menu-sub">
                    <div class="d-lg-flex">
                        <ul>
                            <li class="nav-label"><?= Yii::t('app','Billing') ?></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/dashboard/index') ?>" class="nav-sub-link"><i data-feather="home"></i> <?= Yii::t('app','Billing Dashboard') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/readings/index') ?>" class="nav-sub-link"><i data-feather="inbox"></i> <?= Yii::t('app','Reading Panel') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/customers/index') ?>" class="nav-sub-link"><i data-feather="user"></i> <?= Yii::t('app','Customer Analysis') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/flowmeters/index') ?>" class="nav-sub-link"><i data-feather="cpu"></i> <?= Yii::t('app','Flowmeters') ?></a></li>
                            <li class="nav-label mg-t-20"><?= Yii::t('app','Alarms & Monitoring') ?></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/alarms/index') ?>" class="nav-sub-link"><i data-feather="alert-triangle"></i> <?= Yii::t('app','Alarms Panel') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/alarms/meters') ?>" class="nav-sub-link"><i data-feather="activity"></i> <?= Yii::t('app','Meter Status') ?></a></li>
                        </ul>
                        <ul>
                            <li class="nav-label"><?= Yii::t('app','Reports') ?></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/reports/general') ?>" class="nav-sub-link"><i data-feather="file-text"></i> <?= Yii::t('app','General Reading Report') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/reports/import-status') ?>" class="nav-sub-link"><i data-feather="check-square"></i> <?= Yii::t('app','Posting Status Report') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('billing/reports/comparative') ?>" class="nav-sub-link"><i data-feather="trending-up"></i> <?= Yii::t('app','Comparative Analysis') ?></a></li>
                        </ul>
                    </div>
                </div>
            </li>
            <li class="nav-item with-sub">
                <a href="#" class="nav-link"><i data-feather="file-text"></i> <?= Yii::t('app','Reports') ?></a>
                <div class="navbar-menu-sub">
                    <div class="d-lg-flex">
                        <ul>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('reports') ?>" class="nav-sub-link"><i data-feather="file-text"></i> <?= Yii::t('app','All Reports') ?></a></li>   
                        </ul>
                    </div>
                </div>
            </li>
            <li class="nav-item with-sub">
                <a href="<?= Yii::$app->urlManager->createUrl('settings') ?>" class="nav-link"><i data-feather="settings"></i>  <?= Yii::t('app','Settings') ?></a>
                <div class="navbar-menu-sub">
                    <div class="d-lg-flex">
                        <ul>
                            <li class="nav-label">General Settings</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('locations') ?>" class="nav-sub-link"><i data-feather="map-pin"></i> <?= Yii::t('app','Locations') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('faqs') ?>" class="nav-sub-link"><i data-feather="help-circle"></i> <?= Yii::t('app','FAQs') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('video-tutorials') ?>" class="nav-sub-link"><i data-feather="video"></i> <?= Yii::t('app','Video Tutorials') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('alerts') ?>" class="nav-sub-link"><i data-feather="bell"></i> <?= Yii::t('app','Alerts') ?></a></li>
                            <li class="nav-label mg-t-20">Vendor Settings</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('vendor-groups') ?>" class="nav-sub-link"><i data-feather="truck"></i> <?= Yii::t('app','Vendor Groups') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('vendor-group-points') ?>" class="nav-sub-link"><i data-feather="droplet"></i> <?= Yii::t('app','Vendor group points') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('water-sources') ?>" class="nav-sub-link"><i data-feather="droplet"></i> <?= Yii::t('app','Water Sources') ?></a></li>
                            <li class="nav-label mg-t-20">Payments Settings</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('payment-methods') ?>" class="nav-sub-link"><i data-feather="arrow-right-circle"></i> <?= Yii::t('app','Payment Methods') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('information-guides') ?>" class="nav-sub-link"><i data-feather="book"></i> <?= Yii::t('app','Information Guides') ?></a></li>
                        </ul>
                        <ul>
                            <li class="nav-label">System Access</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('users') ?>" class="nav-sub-link"><i data-feather="arrow-right-circle" class="feather-small"></i> <?= Yii::t('app','Users') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('groups') ?>" class="nav-sub-link"><i data-feather="arrow-right-circle" class="feather-small"></i> <?= Yii::t('app','User Groups') ?></a></li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('permissions') ?>" class="nav-sub-link"><i data-feather="arrow-right-circle" class="feather-small"></i> <?= Yii::t('app','Group Permissions') ?></a></li>
                            <li class="nav-label mg-t-20">Mobile App Settings</li>
                            <li class="nav-sub-item"><a href="<?= Yii::$app->urlManager->createUrl('app-settings') ?>" class="nav-sub-link"><i data-feather="phone"></i> <?= Yii::t('app','Mobile App Settings') ?></a></li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div><!-- navbar-menu-wrapper -->
    <div class="navbar-right">
        <div class="dropdown dropdown-profile">
            <a href="" role="button" class="dropdown-link" data-bs-toggle="dropdown" data-bs-display="static">
                <div class="avatar avatar-sm"><i data-feather="user" class="rounded-circle"></i></div>
            </a>
            <div class="dropdown-menu dropdown-menu-end tx-13">
                <div class="avatar avatar-lg mg-b-15"><i data-feather="user" class="rounded-circle"></i></div>
                <h6 class="tx-semibold mg-b-5"><?= Yii::$app->user->identity->names; ?></h6>
                <p class="mg-b-25 tx-12 tx-color-03"><?= Yii::$app->user->identity->email_address ?></p>
                <a href="<?= Yii::$app->urlManager->createUrl('dashboard/settings'); ?>" class="dropdown-item"><i data-feather="settings"></i> <?= Yii::t('app','My Account') ?></a>
                <a href="<?= Yii::$app->urlManager->createUrl('dashboard/change-password'); ?>" class="dropdown-item"><i data-feather="edit-3"></i> <?= Yii::t('app','Change Password') ?></a>
                <div class="dropdown-divider"></div>
                <a href="<?= Yii::$app->urlManager->createUrl('dashboard/help-center'); ?>" class="dropdown-item"><i data-feather="help-circle"></i> <?= Yii::t('app','Help Center') ?></a>
                <a href="<?= Yii::$app->urlManager->createUrl('site/logout') ?>" class="dropdown-item"><i data-feather="log-out"></i> <?= Yii::t('app','Sign Out') ?></a>
            </div>
        </div>
    </div>
    </div>
</header>