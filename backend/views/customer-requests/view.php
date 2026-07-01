<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\ViewHelper;
use backend\helpers\Helpers;
/** @var yii\web\View $this */
/** @var backend\models\CustomerRequests $model */

$this->title = "Request Details: DEMO-" . $model->id;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="file-text" class="feather-medium"></i> Request Details',
            'links' => [
                ['title' => 'Customer Requests', 'url' => ['index'], 'active' => false],
                ['title' => 'Request DEMO-' . $model->id, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>

        <!-- Header Section -->
        <div class="row mg-b-25">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg mg-r-20 bg-primary">
                        <span class="tx-medium tx-white tx-18"><?= substr($model->customer->alias, 0, 1) ?></span>
                    </div>
                    <div>
                        <h3 class="mg-b-5">DEMO-<?= $model->id ?></h3>
                        <p class="tx-color-03 mg-b-0"><?= Html::encode($model->customer->alias) ?> • <?= Html::encode($model->customer->phone_number) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex justify-content-end">
                    <div class="btn-group">
                        <a href="<?= Yii::$app->urlManager->createUrl(['customer-requests/update', 'id' => $model->id, 'cid' => $model->customer_id]) ?>" 
                           class="btn btn-primary">
                            <i data-feather="edit-2" class="wd-12 mg-r-5"></i> Update Request
                        </a>
                        <a href="<?= Yii::$app->urlManager->createUrl(['customer-requests/index']) ?>" 
                           class="btn btn-outline-secondary">
                            <i data-feather="list" class="wd-12 mg-r-5"></i> All Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="row mg-b-25">
            <div class="col-12">
                <?php
                $statusClass = '';
                $statusIcon = '';
                switch($model->status) {
                    case StatusCodes::COMPLETED_CUSTOMER_REQUEST:
                        $statusClass = 'bg-success';
                        $statusIcon = 'check-circle';
                        break;
                    case StatusCodes::NEW_CUSTOMER_REQUEST:
                    case StatusCodes::NEW_CUSTOMER_REQUEST_OPEN:
                        $statusClass = 'bg-warning';
                        $statusIcon = 'clock';
                        break;
                    case StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST:
                        $statusClass = 'bg-info';
                        $statusIcon = 'truck';
                        break;
                    case StatusCodes::DELIVERED_CUSTOMER_REQUEST:
                        $statusClass = 'bg-primary';
                        $statusIcon = 'package';
                        break;
                    case StatusCodes::CUSTOMER_CANCELLED_REQUEST:
                        $statusClass = 'bg-danger';
                        $statusIcon = 'x-circle';
                        break;
                    default:
                        $statusClass = 'bg-secondary';
                        $statusIcon = 'help-circle';
                }
                ?>
                <div class="alert <?= $statusClass ?> alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i data-feather="<?= $statusIcon ?>" class="wd-16 mg-r-10"></i>
                        <strong>Status:</strong> <?= StatusCodes::getRequestStatusText($model->status) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-xs">
            <!-- Main Information Card -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="info" class="wd-12 mg-r-5"></i> Request Information
                        </h6>
                        <span class="badge badge-primary"><?= date("M j, Y", strtotime($model->date_created)) ?></span>
                    </div>
                    <div class="card-body pd-25">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mg-b-20">
                                    <label class="tx-11 tx-uppercase tx-medium tx-color-03">Vendor</label>
                                    <p class="tx-medium mg-b-0"><?= Html::encode($model->vendor->other_names . ', ' . $model->vendor->first_name) ?></p>
                                    <small class="tx-color-03"><?= Html::encode($model->vendor->mobile_number) ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mg-b-20">
                                    <label class="tx-11 tx-uppercase tx-medium tx-color-03">Volume Requested</label>
                                    <p class="tx-medium mg-b-0"><?= number_format($model->volume_requested) ?> <?= Yii::t('app', 'Barrels') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mg-b-20">
                                    <label class="tx-11 tx-uppercase tx-medium tx-color-03">Total Amount</label>
                                    <p class="tx-medium mg-b-0 tx-success"><?= Helpers::localCurrencyFormatter($model->total_amount) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mg-b-20">
                                    <label class="tx-11 tx-uppercase tx-medium tx-color-03">Delivery Date</label>
                                    <p class="tx-medium mg-b-0"><?= date("M j, Y", strtotime($model->delivery_date)) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($model->delivery_notes): ?>
                            <div class="mg-t-25 pd-20 bg-light rounded">
                                <div class="d-flex align-items-start">
                                    <i data-feather="message-square" class="wd-14 mg-r-10 tx-primary"></i>
                                    <div>
                                        <h6 class="tx-medium mg-b-5">Delivery Notes</h6>
                                        <p class="mg-b-0 tx-color-03"><?= nl2br(Html::encode($model->delivery_notes)) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mg-t-20">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="credit-card" class="wd-12 mg-r-5"></i> Payment Information
                        </h6>
                        <?php if ($paymentDetails != null): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['payments/update', 'id' => $paymentDetails->id, 'rid' => $model->id]) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i data-feather="edit-3" class="wd-10 mg-r-5"></i> Update Payment
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body pd-25">
                        <?php if ($paymentDetails != null): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mg-b-20">
                                        <label class="tx-11 tx-uppercase tx-medium tx-color-03">Payment Method</label>
                                        <p class="tx-medium mg-b-0"><?= Html::encode($paymentDetails->paymentMethod->name) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mg-b-20">
                                        <label class="tx-11 tx-uppercase tx-medium tx-color-03">Amount</label>
                                        <p class="tx-medium mg-b-0 tx-success"><?= Yii::$app->formatter->asCurrency($paymentDetails->amount) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mg-b-20">
                                        <label class="tx-11 tx-uppercase tx-medium tx-color-03">Payment Status</label>
                                        <?php
                                        $paymentStatusClass = '';
                                        switch($paymentDetails->status) {
                                            case StatusCodes::PAYMENT_SUCCESS:
                                                $paymentStatusClass = 'badge-success';
                                                break;
                                            case StatusCodes::PAYMENT_FAILED:
                                                $paymentStatusClass = 'badge-danger';
                                                break;
                                            case StatusCodes::PAYMENT_RETRY:
                                                $paymentStatusClass = 'badge-warning';
                                                break;
                                            default:
                                                $paymentStatusClass = 'badge-info';
                                        }
                                        ?>
                                        <p class="mg-b-0"><span class="badge <?= $paymentStatusClass ?>"><?= StatusCodes::getPaymentStatusText($paymentDetails->status) ?></span></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mg-b-20">
                                        <label class="tx-11 tx-uppercase tx-medium tx-color-03">Payment Date</label>
                                        <p class="tx-medium mg-b-0"><?= date("M j, Y, h:i A", strtotime($paymentDetails->date_created)) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php if ($paymentDetails->description): ?>
                                <div class="mg-t-20 pd-20 bg-light rounded">
                                    <div class="d-flex align-items-start">
                                        <i data-feather="file-text" class="wd-14 mg-r-10 tx-primary"></i>
                                        <div>
                                            <h6 class="tx-medium mg-b-5">Payment Description</h6>
                                            <p class="mg-b-0 tx-color-03"><?= Html::encode($paymentDetails->description) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center pd-40">
                                <i data-feather="alert-circle" class="wd-48 mg-b-20 tx-warning"></i>
                                <h6 class="tx-medium mg-b-10">No Payment Information</h6>
                                <p class="tx-color-03 mg-b-25">This request doesn't have any payment details yet.</p>
                                <a href="<?= Yii::$app->urlManager->createUrl(['payments/create', 'rid' => $model->id]) ?>" 
                                   class="btn btn-primary">
                                    <i data-feather="plus" class="wd-12 mg-r-5"></i> Add Payment Details
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Delivery Location -->
                <div class="card mg-b-20">
                    <div class="card-header">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="map-pin" class="wd-12 mg-r-5"></i> Delivery Location
                        </h6>
                    </div>
                    <div class="card-body pd-0">
                        <div class="pd-20">
                            <div class="d-flex align-items-start mg-b-15">
                                <i data-feather="map-pin" class="wd-14 mg-r-10 tx-primary"></i>
                                <div>
                                    <h6 class="tx-medium mg-b-5">Delivery Address</h6>
                                    <p class="tx-color-03 mg-b-0"><?= Html::encode($model->customerAddress->address) ?></p>
                                </div>
                            </div>
                        </div>
                        <div id="delivery-map" style="height: 250px; border-radius: 0 0 0.375rem 0.375rem;"></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="zap" class="wd-12 mg-r-5"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body pd-20">
                        <div class="d-grid gap-2">
                            <a href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $model->customer_id]) ?>" 
                               class="btn btn-outline-primary btn-block">
                                <i data-feather="user" class="wd-12 mg-r-5"></i> View Customer Profile
                            </a>
                            <a href="<?= Yii::$app->urlManager->createUrl(['vendors/view', 'id' => $model->vendor_id]) ?>" 
                               class="btn btn-outline-info btn-block">
                                <i data-feather="truck" class="wd-12 mg-r-5"></i> View Vendor Profile
                            </a>
                            <a href="<?= Yii::$app->urlManager->createUrl(['customer-requests/index']) ?>" 
                               class="btn btn-outline-secondary btn-block">
                                <i data-feather="list" class="wd-12 mg-r-5"></i> All Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #8392a5;
    margin-bottom: 5px;
    display: block;
}

.info-item p {
    font-size: 14px;
    margin-bottom: 0;
}

.avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.card {
    border: none;
    box-shadow: 0 0 0 1px rgba(0,0,0,.05), 0 2px 4px rgba(0,0,0,.08);
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem 0.5rem 0 0;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-left: 0.5rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}
</style>

<script type="text/javascript">
    $(function () {
        // Initialize Feather icons
        if (typeof feather !== 'undefined') feather.replace();

        function displayDeliveryMap() {
            var map = L.map('delivery-map').setView([<?= $model->customerAddress->location_coordinates ?>], 15);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            L.marker([<?= $model->customerAddress->location_coordinates ?>]).addTo(map)
                .bindPopup("Delivery Address: <?= Html::encode($model->customerAddress->address) ?>")
                .openPopup();
        }

        displayDeliveryMap();
    });
</script>