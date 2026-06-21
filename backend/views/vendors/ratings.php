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
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view-certifications', 'id' => $model->id]) ?>">
                            <i data-feather="file" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Certifications') ?>
                        </a>
                        <a class="nav-link active" href="<?= Yii::$app->urlManager->createUrl(['vendors/view-ratings', 'id' => $model->id]) ?>">
                            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Ratings') ?>
                        </a>
                        <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['vendors/view-payments', 'id' => $model->id]) ?>">
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

                <!-- Certifications List -->
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Customer Ratings') ?>
                        </h6>
                        <span class="badge bg-primary"><?= count($ratings) ?> <?= Yii::t('app', 'Reviews') ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mg-b-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30%"><?= Yii::t('app', 'Customer') ?></th>
                                        <th width="15%"><?= Yii::t('app', 'Rating') ?></th>
                                        <th width="35%"><?= Yii::t('app', 'Review') ?></th>
                                        <th width="20%"><?= Yii::t('app', 'Date') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ratings as $rating): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                    $customerInitials = strtoupper(substr($rating->order->customer->alias, 0, 2));
                                                    $bgColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                                                    ?>
                                                    <div class="avatar avatar-sm me-3" 
                                                         style="background-color: <?= $bgColor ?>; color: white; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%;">
                                                        <?= $customerInitials ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="tx-13 tx-medium mg-b-0"><?= Html::encode($rating->order->customer->alias) ?></h6>
                                                        <span class="tx-12 tx-color-03"><?= Html::encode($rating->order->customer->phone_number) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="stars-container me-2">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $rating->rating): ?>
                                                                <i data-feather="star" class="wd-15 ht-15 stroke-2 fill-warning text-warning"></i>
                                                            <?php else: ?>
                                                                <i data-feather="star" class="wd-15 ht-15 stroke-2 text-secondary"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="tx-medium"><?= $rating->rating ?>.0</span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="tx-13 mg-b-0 text-truncate" style="max-width: 300px;" 
                                                   data-toggle="tooltip" 
                                                   title="<?= Html::encode($rating->notes) ?>">
                                                    <?= Html::encode($rating->notes) ?>
                                                </p>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="tx-13"><?= Yii::$app->formatter->asDate($rating->date_created, 'php:M d, Y') ?></span>
                                                    <span class="tx-11 tx-color-03"><?= Yii::$app->formatter->asTime($rating->date_created, 'php:h:i A') ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($ratings)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i data-feather="star" class="wd-40 ht-40 stroke-1 text-secondary mb-2"></i>
                                                <p class="text-secondary mb-0"><?= Yii::t('app', 'No ratings found') ?></p>
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

/* Add these styles to your existing CSS */
.stars-container {
    display: inline-flex;
    align-items: center;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: 600;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}
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