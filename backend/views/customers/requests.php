<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */

$this->title =
    $this->title = "View Customer - " . $model->alias;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => 'Customer Profile - ' . $model->alias,
            'links' => [
                ['title' => 'Customers', 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => 'View Customer', 'active' => true],
                ['title' => $model->alias, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <?= $this->render('_info', ['model' => $model]); ?>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="mg-b-20">
                    <a class="btn btn-sm pd-x-15 btn-white btn-uppercase" href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $model->id]); ?>"><i data-feather="user" class="wd-10 mg-r-5"></i> <?= Yii::t('app', 'Overview') ?></a>
                    <a class="btn btn-sm pd-x-15 btn-secondary btn-uppercase mg-l-5" href="<?= Yii::$app->urlManager->createUrl(['customers/requests', 'id' => $model->id]); ?>"><i data-feather="git-pull-request" class="wd-10 mg-r-5"></i><?= Yii::t('app', 'Requests & Payments'); ?></a>
                    <a class="btn btn-sm pd-x-15 btn-white btn-uppercase mg-l-5" href="<?= Yii::$app->urlManager->createUrl(['customers/addresses', 'id' => $model->id]); ?>"><i data-feather="map-pin" class="wd-10 mg-r-5"></i> <?= Yii::t('app', 'Addresses'); ?></a>
                </div>
                <div class="row row-xs mg-b-20">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card mg-b-20 mg-lg-b-25">
                            <div class="card-header pd-y-15 pd-x-20 bg-secondary">
                                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white">Recent Requests</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive table-striped">
                                        <thead>
                                            <th>#</th>
                                            <th><?= Yii::t('app', 'Vendor') ?></th>
                                            <th><?= Yii::t('app', 'Delivery Address') ?></th>
                                            <th><?= Yii::t('app', 'Water Volume') ?></th>
                                            <th><?= Yii::t('app', 'Total Amount') ?></th>
                                            <th><?= Yii::t('app', 'Request Status') ?></th>
                                            <th><?= Yii::t('app', 'Payment Method') ?></th>
                                            <th><?= Yii::t('app', 'Payment Status') ?></th>
                                            <th><?= Yii::t('app', 'Date') ?></th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            <?=
                                            ListView::widget([
                                                'viewParams' => [
                                                    'updated' => $updated
                                                ],
                                                'dataProvider' => $dataProvider,
                                                'itemView' => '_request',
                                                'layout' => "{summary}\n<div class='row row-xs'>{items}</div>\n{pager}",
                                                'itemOptions' => [
                                                    'tag' => false
                                                ],
                                                'pager' => [
                                                    'options' => [
                                                        'tag' => 'ul',
                                                        'class' => 'pagination justify-content-center',
                                                        'id' => 'pager-container',
                                                    ],
                                                    //First option value
                                                    'firstPageLabel' => 'First',
                                                    //Last option value
                                                    'lastPageLabel' => 'Last',
                                                    //Previous option value
                                                    'prevPageLabel' => 'Previous',
                                                    //Next option value
                                                    'nextPageLabel' => 'Next',
                                                    //Current Active option value
                                                    'activePageCssClass' => 'page-active',
                                                    //Max count of allowed options
                                                    //'maxButtonCount' => 3,
                                                    // Css for each options. Links
                                                    'linkOptions' => ['class' => 'page-link'],
                                                    'disabledPageCssClass' => 'disabled page-link',
                                                ]
                                            ]);
                                            ?>
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