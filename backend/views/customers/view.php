<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\ViewHelper;
use backend\helpers\Helpers;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */

    $this->title = "View Customer - " . $model->alias;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Customer Profile') . ' - ' . $model->alias,
            'links' => [
                ['title' => Yii::t('app','Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app','View Customer'), 'active' => true],
                ['title' => $model->alias, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        
        <div class="row">
            <!-- Left Column - Customer Info -->
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
                                <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#sendMessageModal">
                                    <i data-feather="message-square" class="wd-10 mg-r-5"></i> <?= Yii::t('app', 'Send Message') ?>
                                </button>
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

            <!-- Right Column - Orders and Details -->
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
                            <a class="nav-link rounded-0" 
                               href="<?= Yii::$app->urlManager->createUrl(['customers/addresses', 'id' => $model->id, 'tab' => 'addresses']); ?>">
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

                <?php if (!Yii::$app->request->get('tab')): ?>
                    <!-- Quick Stats Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-one">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="card-value mb-0"><?= count($recent_requests) ?></h6>
                                        <div class="chart-wrapper">
                                            <i data-feather="shopping-cart" class="wd-40 ht-40 stroke-1"></i>
                                        </div>
                                    </div>
                                    <h6 class="card-label fw-semibold fs-sm mb-1"><?= Yii::t('app', 'Total Orders') ?></h6>
                                    <p class="card-desc fs-xs text-secondary mb-0">
                                        <?= Yii::t('app', 'All time orders') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-one">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="card-value mb-0">
                                            <?= count(array_filter($recent_requests, fn($req) => $req->status === StatusCodes::COMPLETED_CUSTOMER_REQUEST)) ?>
                                        </h6>
                                        <div class="chart-wrapper">
                                            <i data-feather="check-circle" class="wd-40 ht-40 stroke-1"></i>
                                        </div>
                                    </div>
                                    <h6 class="card-label fw-semibold fs-sm mb-1"><?= Yii::t('app', 'Completed') ?></h6>
                                    <p class="card-desc fs-xs text-secondary mb-0">
                                        <?= Yii::t('app', 'Successfully delivered') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-one">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="card-value mb-0">
                                            <?= count(array_filter($recent_requests, fn($req) => ($req->status === StatusCodes::NEW_CUSTOMER_REQUEST || $req->status === StatusCodes::NEW_CUSTOMER_REQUEST_OPEN))) ?>
                                        </h6>
                                        <div class="chart-wrapper">
                                            <i data-feather="clock" class="wd-40 ht-40 stroke-1"></i>
                                        </div>
                                    </div>
                                    <h6 class="card-label fw-semibold fs-sm mb-1"><?= Yii::t('app', 'Pending') ?></h6>
                                    <p class="card-desc fs-xs text-secondary mb-0">
                                        <?= Yii::t('app', 'Awaiting delivery') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="card">
                        <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                            <h6 class="tx-uppercase tx-semibold mg-b-0">
                                <i data-feather="shopping-bag" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Recent Orders') ?>
                            </h6>
                            <?= Html::a(
                                '<i data-feather="plus-circle" class="wd-10 mg-r-5"></i> ' . Yii::t('app', 'New Order'),
                                ['customers/create-request', 'cid' => $model->id],
                                ['class' => 'btn btn-primary btn-sm']
                            ) ?>
                        </div>
                        <div class="card-body p-0">
                            <?php if(count($recent_requests) < 1): ?>
                                <div class="text-center py-5">
                                    <i data-feather="shopping-bag" class="wd-48 ht-48 text-muted"></i>
                                    <h5 class="mt-3"><?= Yii::t('app', 'No Orders Found') ?></h5>
                                    <p class="text-muted"><?= Yii::t('app', 'This customer has not placed any orders yet.') ?></p>
                                    <?= Html::a(
                                        '<i data-feather="plus-circle" class="wd-10 mg-r-5"></i> ' . Yii::t('app', 'Create First Order'),
                                        ['customers/create-request', 'cid' => $model->id],
                                        ['class' => 'btn btn-primary btn-sm mt-3']
                                    ) ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mg-b-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th><?= Yii::t('app','Vendor')?></th>
                                                <th><?= Yii::t('app','Delivery Date')?></th>
                                                <th class="text-end"><?= Yii::t('app','Amount')?></th>
                                                <th class="text-center"><?= Yii::t('app','Volume')?></th>
                                                <th><?= Yii::t('app','Status')?></th>
                                                <th><?= Yii::t('app','Payment')?></th>
                                                <th class="text-center"><?= Yii::t('app','Actions')?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_requests as $index => $delivery): ?>
                                                <tr>
                                                    <td class="text-center"><?= $index + 1 ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php
                                                            if ($delivery->vendor): 
                                                                $vendorInitials = $delivery->vendor->other_names ? strtoupper(substr($delivery->vendor->other_names, 0, 2)) : 'N/A';
                                                                $bgColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                                                            ?>
                                                                <div class="avatar avatar-sm me-2" 
                                                                     style="background-color: <?= $bgColor ?>; color: white;">
                                                                    <?= $vendorInitials ?>
                                                                </div>
                                                                <div>
                                                                    <a href="<?= Yii::$app->urlManager->createUrl(['vendors/view','id' => $delivery->vendor_id])?>" 
                                                                       class="text-primary d-block">
                                                                        <?= Html::encode($delivery->vendor->other_names ?? 'N/A') ?>
                                                                    </a>
                                                                    <small class="text-muted">
                                                                        <?= Html::encode($delivery->vendor->mobile_number ?? 'N/A') ?>
                                                                    </small>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="avatar avatar-sm me-2" 
                                                                     style="background-color: #ccc; color: white;">
                                                                    N/A
                                                                </div>
                                                                <div>
                                                                    <span class="text-muted d-block"><?= Yii::t('app', 'Vendor Not Available') ?></span>
                                                                    <small class="text-muted">-</small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span><?= Yii::$app->formatter->asDate($delivery->delivery_date, 'php:M d, Y') ?></span>
                                                            <small class="text-muted">
                                                                <?= Yii::$app->formatter->asTime($delivery->delivery_date, 'php:h:i A') ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong><?= Helpers::formatCurrency(Helpers::convertAmount($delivery->total_amount)) ?></strong>
                                                    </td>
                                                    <td class="text-center"><?= $delivery->volume_requested ?> L</td>
                                                    <td>
                                                        <?php
                                                        $statusClass = 'bg-warning'; // default
                                                        if ($delivery->status === StatusCodes::COMPLETED_CUSTOMER_REQUEST) {
                                                            $statusClass = 'bg-success';
                                                        } elseif ($delivery->status === StatusCodes::CUSTOMER_CANCELLED_REQUEST) {
                                                            $statusClass = 'bg-danger';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>">
                                                            <?= StatusCodes::getRequestStatusText($delivery->status) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if($delivery->payment_id != '' && $delivery->payment): ?>
                                                            <div class="d-flex flex-column">
                                                                <small class="text-muted"><?= Html::encode($delivery->payment->paymentMethod->name ?? 'N/A') ?></small>
                                                                <span class="badge <?= $delivery->payment->status == StatusCodes::PAYMENT_SUCCESS ? 'bg-success' : 'bg-warning' ?>">
                                                                    <?= StatusCodes::getPaymentStatusText($delivery->payment->status) ?>
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Not Available</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <?= Html::a(
                                                                '<i data-feather="eye" class="wd-15 ht-15"></i>',
                                                                ['customer-requests/view', 'id' => $delivery->id],
                                                                ['class' => 'btn btn-white', 'data-toggle' => 'tooltip', 'title' => Yii::t('app', 'View')]
                                                            ) ?>
                                                            <?= Html::a(
                                                                '<i data-feather="edit-2" class="wd-15 ht-15"></i>',
                                                                ['customer-requests/update', 'id' => $delivery->id, 'cid' => $model->id],
                                                                ['class' => 'btn btn-white', 'data-toggle' => 'tooltip', 'title' => Yii::t('app', 'Edit')]
                                                            ) ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif (Yii::$app->request->get('tab') == 'complaints'): ?>
                    <!-- Complaints Table -->
                    <div class="card">
                        <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-info">
                            <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Customer Complaints') ?></h6>
                        </div>
                        <div class="card-body">
                            <?php if(empty($complaints)): ?>
                                <div class="text-center py-5">
                                    <i data-feather="message-circle" class="wd-48 ht-48 text-muted"></i>
                                    <h5 class="mt-3"><?= Yii::t('app', 'No Complaints Found') ?></h5>
                                    <p class="text-muted"><?= Yii::t('app', 'This customer has not filed any complaints.') ?></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mg-b-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><?= Yii::t('app', 'Title') ?></th>
                                                <th><?= Yii::t('app', 'Description') ?></th>
                                                <th><?= Yii::t('app', 'Status') ?></th>
                                                <th><?= Yii::t('app', 'Date') ?></th>
                                                <th><?= Yii::t('app', 'Action') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($complaints as $complaint): ?>
                                                <tr>
                                                    <td><?= Html::encode($complaint->title) ?></td>
                                                    <td><?= Html::encode(substr($complaint->description, 0, 100)) . (strlen($complaint->description) > 100 ? '...' : '') ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = '';
                                                        switch($complaint->status) {
                                                            case 1:
                                                                $statusClass = 'bg-primary';
                                                                $statusText = 'New';
                                                                break;
                                                            case 2:
                                                                $statusClass = 'bg-warning';
                                                                $statusText = 'Being Handled';
                                                                break;
                                                            case 3:
                                                                $statusClass = 'bg-success';
                                                                $statusText = 'Resolved';
                                                                break;
                                                            default:
                                                                $statusClass = 'bg-secondary';
                                                                $statusText = 'Unknown';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                    </td>
                                                    <td><?= Yii::$app->formatter->asDatetime($complaint->date_created, 'php:M d, Y h:i A') ?></td>
                                                    <td>
                                                        <?= Html::a('<i data-feather="eye"></i>', 
                                                            ['/complaints/view', 'id' => $complaint->id], 
                                                            ['class' => 'btn btn-sm btn-info', 'title' => Yii::t('app', 'View Complaint')]
                                                        ) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendMessageModalLabel">
                    <?= Yii::t('app', 'Send Message to {name}', ['name' => Html::encode($model->alias)]) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('app','Close') ?>"></button>
            </div>
            <div class="modal-body">
                <?= Html::beginForm(['customers/send-message', 'id' => $model->id], 'post', ['id' => 'send-message-form']) ?>
                    <div class="mb-3">
                        <label class="form-label"><?= Yii::t('app', 'Subject') ?></label>
                        <?= Html::textInput('title', Yii::t('app', 'Message from Demo'), [
                            'class' => 'form-control',
                            'maxlength' => 100,
                        ]) ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= Yii::t('app', 'Message') ?></label>
                        <?= Html::textarea('message', '', [
                            'class' => 'form-control',
                            'rows' => 4,
                            'required' => true,
                        ]) ?>
                        <small class="text-muted">
                            <?= Yii::t('app', 'This will be sent as an in-app notification and SMS (if phone number is available).') ?>
                        </small>
                    </div>
                <?= Html::endForm() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= Yii::t('app', 'Cancel') ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('send-message-form').submit();">
                    <i data-feather="send" class="wd-10 mg-r-5"></i> <?= Yii::t('app', 'Send') ?>
                </button>
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