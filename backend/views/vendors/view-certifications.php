<?php

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
            'title' => 'Vendor Profile - ' . $model->other_names . ', ' . $model->first_name . ' ' . '<i data-feather="check-circle" class="tx-success feather-medium"></i>',
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
                <?= $this->render('_vendor_info', ['model' => $model, 'averageRating' => $averageRating, 'totalRatings' => $totalRatings, 'ratingBreakdown' => $ratingBreakdown]); ?>
            </div>

            <!-- Main Content -->
            <div class="col-sm-12 col-md-9">
                <!-- Navigation Buttons -->
                <!-- Quick Actions Bar -->
                <div class="d-flex align-items-center justify-content-between mg-b-20">
                    <nav class="nav nav-pills">
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view', 'id' => $model->id]) ?>">
                            <i data-feather="user" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Overview') ?>
                        </a>
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/deliveries','id' => $model->id]) ?>">
                            <i data-feather="truck" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Orders') ?>
                        </a>
                        <a class="nav-link active"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view-certifications', 'id' => $model->id]) ?>">
                            <i data-feather="file" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Certifications') ?>
                        </a>
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view-ratings', 'id' => $model->id]) ?>">
                            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Ratings') ?>
                        </a>
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view-payments', 'id' => $model->id]) ?>">
                            <i data-feather="clipboard" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Payments') ?>
                        </a>
                        <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['vendor/withdrawals', 'id' => $model->id]) ?>">
                            <i data-feather="clipboard" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Withdrawals') ?>
                        </a>
                    </nav>
                    <div class="d-none d-md-flex">
                        <?php if ($model->status === StatusCodes::ACTIVE_STATUS): ?>
                            <button type="button" class="btn btn-white btn-sm mg-r-5" data-toggle="tooltip"
                                title="<?= Yii::t('app', 'Send Message') ?>">
                                <i data-feather="message-square" class="wd-15 ht-15 stroke-2"></i>
                            </button>
                        <?php endif; ?>
                        <?= Html::a(
                            '<i data-feather="edit-2" class="wd-15 ht-15 stroke-2"></i>',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-white btn-sm mg-r-5', 'data-toggle' => 'tooltip', 'title' => Yii::t('app', 'Edit Profile')]
                        ) ?>
                        
                    </div>
                </div>

                <!-- Certifications Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0"><?= count($certifications) ?></h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="file-text" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Total Certifications</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Active and expired certifications
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= count(array_filter($certifications, fn($cert) => $cert->status === StatusCodes::ACTIVE_STATUS)) ?>
                                    </h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="check-circle" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Active Certifications</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Currently valid certifications
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card card-one">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="card-value mb-0">
                                        <?= count(array_filter($certifications, fn($cert) => strtotime($cert->expiry_date) < time())) ?>
                                    </h6>
                                    <div class="chart-wrapper">
                                        <i data-feather="alert-circle" class="wd-40 ht-40 stroke-1"></i>
                                    </div>
                                </div>
                                <h6 class="card-label fw-semibold fs-sm mb-1">Expired Certifications</h6>
                                <p class="card-desc fs-xs text-secondary mb-0">
                                    Certifications requiring renewal
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Certifications List -->
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="file-text" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Certifications List') ?>
                        </h6>
                        <a href="<?= Yii::$app->urlManager->createUrl(['vendor-certifications/create', 'cid' => $model->id]) ?>"
                            class="btn btn-primary btn-sm">
                            <i data-feather="plus-circle" class="wd-10 mg-r-5"></i>
                            <?= Yii::t('app', 'Add Certification') ?>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mg-b-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="40%"><?= Yii::t('app', 'Certification Details') ?></th>
                                        <th width="20%"><?= Yii::t('app', 'Expiry Date') ?></th>
                                        <th width="15%"><?= Yii::t('app', 'Status') ?></th>
                                        <th width="20%"><?= Yii::t('app', 'Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certifications as $index => $cert): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <p class="mg-b-0 tx-medium">
                                                    <?= Html::encode($cert->certification_details) ?></p>
                                            </td>
                                            <td>
                                                <?php
                                                $expiryDate = strtotime($cert->expiry_date);
                                                $today = time();
                                                $daysUntilExpiry = round(($expiryDate - $today) / (60 * 60 * 24));

                                                if ($daysUntilExpiry < 0) {
                                                    echo '<span class="badge bg-danger">Expired</span>';
                                                } elseif ($daysUntilExpiry <= 30) {
                                                    echo '<span class="badge bg-warning">Expires in ' . $daysUntilExpiry . ' days</span>';
                                                } else {
                                                    echo Yii::$app->formatter->asDate($cert->expiry_date);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = $cert->status === StatusCodes::ACTIVE_STATUS ? 'bg-success' : 'bg-danger';
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= StatusCodes::getStatusText($cert->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= Yii::$app->urlManager->createUrl(['vendor-certifications/update', 'cid' => $model->id, 'id' => $cert->id]) ?>"
                                                        class="btn btn-white btn-sm" data-toggle="tooltip"
                                                        title="<?= Yii::t('app', 'Update') ?>">
                                                        <i data-feather="edit" class="wd-15 ht-15"></i>
                                                    </a>
                                                    <?= Html::a(
                                                        '<i data-feather="trash" class="wd-15 ht-15"></i>',
                                                        ['vendor-certifications/delete', 'cid' => $cert->vendor_id, 'id' => $cert->id],
                                                        [
                                                            'class' => 'btn btn-white btn-sm',
                                                            'data' => [
                                                                'toggle' => 'tooltip',
                                                                'title' => Yii::t('app', 'Delete'),
                                                                'confirm' => Yii::t('app', 'Are you sure you want to delete this certification?'),
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($certifications)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i data-feather="file-text"
                                                    class="wd-40 ht-40 stroke-1 text-secondary mb-2"></i>
                                                <p class="text-secondary mb-0">
                                                    <?= Yii::t('app', 'No certifications found') ?></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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

<?php
$js = <<<JS
    // Initialize tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        // Reinitialize Feather icons
        if (typeof feather !== 'undefined') feather.replace();
    });
JS;
$this->registerJs($js);
?>