<?php

use backend\helpers\Helpers;
use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\DetailView;

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Vendors $model */

$this->title = "View Vendor - " . $model->other_names . ', ' . $model->first_name;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => 'Vendor Profile - ' . $model->other_names . ', ' . $model->first_name . ' ' .
                ($model->status === StatusCodes::ACTIVE_STATUS ?
                    '<i data-feather="check-circle" class="tx-success feather-medium"></i>' :
                    '<i data-feather="alert-circle" class="tx-danger feather-medium"></i>'),
            'links' => [
                ['title' => 'Vendors', 'url' => Yii::$app->urlManager->createUrl('vendors')],
                ['title' => 'View Vendor', 'active' => true],
                ['title' => $model->other_names . ', ' . $model->first_name, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>



        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-sm-12 col-md-3">
                <!-- Vendor Info Card -->
                <?= $this->render('_vendor_info', ['model' => $model, 'averageRating' => $averageRating, 'totalRatings' => $totalRatings, 'ratingBreakdown' => $ratingBreakdown]); ?>
            </div>

            <!-- Main Content -->
            <div class="col-sm-12 col-md-9">
                <!-- Quick Actions Bar -->
                <div class="d-flex align-items-center justify-content-between mg-b-20">
                    <nav class="nav nav-pills">
                        <a class="nav-link active" href="javascript:void(0);">
                            <i data-feather="user" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Overview') ?>
                        </a>
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/deliveries', 'id' => $model->id]) ?>">
                            <i data-feather="truck" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Orders') ?>
                        </a>
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view-certifications', 'id' => $model->id]) ?>">
                            <i data-feather="file" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Certifications') ?>
                        </a>
                        <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['vendors/view-ratings', 'id' => $model->id]) ?>">
                            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Ratings') ?>
                        </a>
                        <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['payments', 'PaymentSearchForm[vendorPhoneNumber]' => ltrim($model->mobile_number, '+')]) ?>">
                            <i data-feather="clipboard" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Payments') ?>
                        </a>
                        <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['vendors/withdrawals', 'id' => $model->id]) ?>">
                            <i data-feather="clipboard" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Withdrawals') ?>
                        </a>
                    </nav>
                    <div class="d-none d-md-flex">
                        <?php if ($model->status === StatusCodes::ACTIVE_STATUS): ?>
                            <?= Html::a(
                                '<i data-feather="lock" class="wd-15 ht-15 stroke-2"></i>',
                                ['reset-password', 'id' => $model->id],
                                ['class' => 'btn btn-white btn-sm mg-r-5', 'data-toggle' => 'tooltip', 'title' => Yii::t('app', 'Reset Password')]
                            ) ?>
                        <?php endif; ?>
                        <?= Html::a(
                            '<i data-feather="edit-2" class="wd-15 ht-15 stroke-2"></i>',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-white btn-sm mg-r-5', 'data-toggle' => 'tooltip', 'title' => Yii::t('app', 'Edit Profile')]
                        ) ?>
                    </div>
                </div>
                <!-- Quick Stats Section -->
                <div class="row g-3 mb-4">
                    <!-- Wallet Balance -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= Helpers::localCurrencyFormatter($model->wallet_balance) ?>
                                    </h6>
                                    
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Wallet Balance</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Available balance in vendor's wallet
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Earnings -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= Helpers::localCurrencyFormatter($model->getTotalEarnings()) ?>
                                    </h6>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Total Earnings</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Total earnings from all deliveries
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Deliveries -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0"><?= $model->getTotalDeliveries() ?></h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="truck" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Total Deliveries</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Number of successful deliveries
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Rating -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= number_format($model->getAverageRating(), 1) ?>
                                        <small class="fs-sm text-secondary">/5</small>
                                    </h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="star" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Average Rating</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Based on <?= $model->getTotalRatings() ?> customer ratings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Tabs -->
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row row-xs">
                            <!-- Map Card -->
                            <div class="col-lg-6">
                                <div class="card mg-b-20">
                                    <div
                                        class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                                            <i data-feather="map-pin" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                            <?= Yii::t('app', 'Service Area') ?>
                                        </h6>
                                        <span
                                            class="badge badge-primary"><?= Html::encode($model->residential_location) ?></span>
                                    </div>
                                    <div class="card-body pd-0">
                                        <div id="vendor-location" style="height: 300px"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Orders -->
                            <div class="col-lg-6">
                                <?= $this->render('_recent_orders', [
                                    'recent_deliveries' => $recent_deliveries
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ratings Tab -->
                    <div class="tab-pane fade" id="ratings">
                        <?= $this->render('_ratings_list', [
                            'ratings' => $ratings
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerCss(<<<CSS
    .avatar-xl {
        width: 120px;
        height: 120px;
        font-size: 48px;
    }
    
    .avatar-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: 2px solid #fff;
    }
    
    .nav-pills .nav-link {
        padding: 8px 15px;
    }
    
    .fill-warning {
        fill: #ffc107;
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

.card-one .card-value small {
    font-weight: 400;
    margin-left: 5px;
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

/* Add these utility classes if not already present */
.fs-sm { font-size: 14px; }
.fs-xs { font-size: 12px; }
.stroke-1 { stroke-width: 1; }
CSS
); ?>

<script type="text/javascript">
    $(function() {
        const locationCoordinates = [<?= $model->location_coordinates ?>];
        const vendorName = "<?= $model->other_names . ', ' . $model->first_name ?>";
        // Initialize map
        function displayVendorOnMap() {
            var map = L.map('vendor-location').setView([locationCoordinates[0], locationCoordinates[1]], 13);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            L.marker([locationCoordinates[0], locationCoordinates[1]])
                .addTo(map)
                .bindPopup(vendorName)
                .openPopup();
        }

        displayVendorOnMap();
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Handle tab switching
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (typeof feather !== 'undefined') feather.replace();
        });
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>