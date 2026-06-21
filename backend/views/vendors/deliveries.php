<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ListView;

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Vendors $model */

$this->title = "View Vendor - " . $model->other_names . ', ' . $model->first_name;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Vendor Profile') . ' - ' . $model->other_names . ', ' . $model->first_name . ' ' . '<i data-feather="check-circle" class="tx-success feather-medium"></i>',
            'links' => [
                ['title' => Yii::t('app', 'Vendors'), 'url' => Yii::$app->urlManager->createUrl('vendors')],
                ['title' => Yii::t('app', 'View Vendor'), 'active' => true],
                ['title' => $model->other_names . ', ' . $model->first_name, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <!-- Vendor Info Card -->
                <?= $this->render('_vendor_info', ['model' => $model, 'averageRating' => $averageRating, 'totalRatings' => $totalRatings, 'ratingBreakdown' => $ratingBreakdown]); ?>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="d-flex align-items-center justify-content-between mg-b-20">
                    <nav class="nav nav-pills">
                        <a class="nav-link"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/view', 'id' => $model->id]) ?>">
                            <i data-feather="user" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Overview') ?>
                        </a>
                        <a class="nav-link active"
                            href="<?= Yii::$app->urlManager->createUrl(['vendors/deliveries', 'id' => $model->id]) ?>">
                            <i data-feather="truck" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Orders') ?>
                        </a>
                        <a class="nav-link"
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
                <div class="table-responsive">
                    <table class="table table-hover mg-b-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="2%">#</th>
                                <th width="10%"><?= Yii::t('app', 'Customer') ?></th>
                                <th width="10%"><?= Yii::t('app', 'Vendor') ?></th>
                                <th width="10%"><?= Yii::t('app', 'Address') ?></th>
                                <th width="8%"><?= Yii::t('app', 'Volume') ?></th>
                                <th width="8%"><?= Yii::t('app', 'Amount') ?></th>
                                <th width="5%"><?= Yii::t('app', 'Type') ?></th>
                                <th width="10%"><?= Yii::t('app', 'Status') ?></th>
                                <th width="8%"><?= Yii::t('app', 'Payment') ?></th>
                                <th width="10%"><?= Yii::t('app', 'Date') ?></th>
                                <th width="5%"><?= Yii::t('app', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= ListView::widget([
                                'dataProvider' => $dataProvider,
                                'itemView' => '_request',
                                'layout' => "{items}\n<div class='card-footer'>{pager}</div>",
                                'itemOptions' => ['tag' => false],
                                'pager' => [
                                    'options' => [
                                        'class' => 'pagination pagination-sm justify-content-center mg-b-0',
                                    ],
                                    'firstPageLabel' => '<i data-feather="chevrons-left"></i>',
                                    'lastPageLabel' => '<i data-feather="chevrons-right"></i>',
                                    'prevPageLabel' => '<i data-feather="chevron-left"></i>',
                                    'nextPageLabel' => '<i data-feather="chevron-right"></i>',
                                    'activePageCssClass' => 'active',
                                    'linkOptions' => ['class' => 'page-link'],
                                    'disabledPageCssClass' => 'page-link disabled',
                                ]
                            ]); ?>
                        </tbody>
                    </table>
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