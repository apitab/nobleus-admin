<?php

use yii;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */

$this->title =
    $this->title = "View Customer - " . $model->alias;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Customer Profile') . ' - ' . $model->alias,
            'links' => [
                ['title' => Yii::t('app', 'Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app', 'View Customer'), 'active' => true],
                ['title' => Yii::t('app', 'Addresses'), 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-primary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Customer Information') ?></h6>
                    </div>
                    <div class="card-body">
                        <!-- Profile Section -->
                        <div class="text-center mb-4">
                            <?php
                            $initials = strtoupper(substr($model->alias, 0, 2));
                            $bgColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                            ?>
                            <div class="avatar avatar-xl mx-auto mb-3" 
                                 style="background-color: <?= $bgColor ?>; color: white; display: flex; align-items: center; justify-content: center;">
                                <?= $initials ?>
                            </div>
                            <h4 class="mb-1"><?= Html::encode($model->alias) ?></h4>
                            <p class="text-muted"><?= Html::encode($model->phone_number) ?></p>
                            
                            <!-- Action Buttons -->
                            <div class="btn-group mt-2">
                                <?= Html::a(
                                    '<i data-feather="edit-2" class="wd-10 mg-r-5"></i> ' . Yii::t('app', 'Update Profile'),
                                    ['update', 'id' => $model->id],
                                    ['class' => 'btn btn-primary btn-sm']
                                ) ?>
                                <?= Html::a(
                                    '<i data-feather="shopping-cart" class="wd-10 mg-r-5"></i> ' . Yii::t('app', 'Create Order'),
                                    ['customers/create-request', 'cid' => $model->id],
                                    ['class' => 'btn btn-info btn-sm']
                                ) ?>
                            </div>
                        </div>

                        <!-- Wallet Balance Card -->
                        <div class="card card-one mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= Yii::$app->formatter->asCurrency($model->wallet_balance, 'Sl Sh') ?>
                                    </h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="dollar-sign" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1"><?= Yii::t('app', 'Wallet Balance') ?></h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    <?= Yii::t('app', 'Available balance for orders') ?>
                                </p>
                            </div>
                        </div>

                        <!-- Customer Stats -->
                        <div class="profile-info">
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><?= Yii::t('app', 'Status') ?></span>
                                <span class="badge <?= $model->status == StatusCodes::ACTIVE_STATUS ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $model->status == StatusCodes::ACTIVE_STATUS ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive') ?>
                                </span>
                            </div>
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><?= Yii::t('app', 'Join Date') ?></span>
                                <span><?= Yii::$app->formatter->asDate($model->date_created, 'php:M d, Y') ?></span>
                            </div>
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><?= Yii::t('app', 'Total Orders') ?></span>
                                <span class="badge bg-info"><?= count($recent_requests) ?></span>
                            </div>
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted"><?= Yii::t('app', 'Total Spent') ?></span>
                                <span class="badge bg-primary">
                                    <?= Yii::$app->formatter->asCurrency($model->getTotalSpent(), 'Sl Sh') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <!-- Navigation Tabs -->
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <nav class="nav nav-pills nav-fill border-bottom">
                            <a class="nav-link rounded-0 <?= !Yii::$app->request->get('tab') ? 'active' : '' ?>" 
                               href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $model->id]); ?>">
                                <i data-feather="shopping-bag" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app','Orders') ?>
                                <span class="badge bg-white text-primary ms-2"><?= count($recent_requests) ?></span>
                            </a>
                            <a class="nav-link rounded-0 <?= Yii::$app->request->get('tab') == 'addresses' ? 'active' : '' ?>" 
                               href="<?= Yii::$app->urlManager->createUrl(['customers/addresses', 'id' => $model->id]); ?>">
                                <i data-feather="map-pin" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app','Addresses') ?>
                                <span class="badge bg-white text-primary ms-2"><?= $model->getAddressCount() ?></span>
                            </a>
                            <a class="nav-link rounded-0 <?= Yii::$app->request->get('tab') == 'complaints' ? 'active' : '' ?>" 
                               href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $model->id, 'tab' => 'complaints']); ?>">
                                <i data-feather="alert-circle" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app','Complaints') ?>
                                <span class="badge bg-white text-primary ms-2"><?= count($complaints ?? []) ?></span>
                            </a>
                        </nav>
                    </div>
                </div>
                <div class="row row-xs mg-b-20">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card mg-b-20 mg-lg-b-25">
                            <div class="card-header pd-y-15 pd-x-20 bg-secondary">
                                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Addresses'); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-dashboard mg-b-0">
                                        <thead>
                                            <tr>
                                                <th class=""><?= Yii::t('app', 'Location') ?></th>
                                                <th class="text-end"><?= Yii::t('app', 'Address') ?></th>
                                                <th class="text-end"><?= Yii::t('app', 'Address Coordinates') ?></th>
                                                <th class="text-end"><?= Yii::t('app', 'Status') ?></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customerAddresses as $address) : ?>
                                                <tr>
                                                    <td class="tx-color-03 tx-normal"><?= $address->location->name; ?></td>
                                                    <td class="tx-medium text-end"><?= $address->address; ?></td>
                                                    <td class="text-end tx-teal"><?= $address->location_coordinates; ?></td>
                                                    <td class="text-end tx-pink"><?= StatusCodes::getStatusText($address->status); ?></td>
                                                    <td>
                                                        <a href="<?= Yii::$app->urlManager->createUrl(['customer-address/update', 'id' => $address->id, 'customer_id' => $address->customer_id]); ?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app', 'Update') ?></a> |
                                                        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app', 'Delete'), ['customer-address/delete', 'id' => $address->id, 'customer_id' => $address->customer_id], [
                                                            'data' => [
                                                                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                                                'method' => 'post',
                                                            ],
                                                        ]) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerCss(<<<CSS
    /* Add these styles if not already present */
    .avatar-xl {
        width: 80px;
        height: 80px;
        font-size: 32px;
        border-radius: 50%;
    }

    .card-one {
        border: 0;
        background-color: #fff;
        position: relative;
        transition: all 0.2s ease-in-out;
    }

    .card-one:hover {
        box-shadow: 0 0 10px rgba(28,39,60,0.1);
        transform: translateY(-1px);
    }

    .card-one .card-value {
        font-family: "Inter", sans-serif;
        font-weight: 600;
        font-size: 24px;
        color: #001737;
        margin-bottom: 5px;
        display: flex;
        align-items: baseline;
    }

    .card-one .card-label {
        font-size: 14px;
        font-weight: 600;
        color: #001737;
        margin-bottom: 5px;
    }

    .card-one .card-desc {
        font-size: 12px;
        color: #8392a5;
        margin-bottom: 0;
    }

    .card-one .chart-wrapper {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background-color: rgba(1, 104, 250, 0.1);
    }

    .card-one .chart-wrapper i {
        color: #0168fa;
    }

    .fs-sm { font-size: 14px; }
    .fs-xs { font-size: 12px; }
    .stroke-1 { stroke-width: 1; }
CSS
); ?>