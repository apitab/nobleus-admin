<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
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
                ['title' => Yii::t('app','Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => 'View Customer', 'active' => true],
                ['title' => $model->alias, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
        <div class="col-sm-12 col-md-3">
                <div class="card mg-b-20 bg-secondary tx-white">
                    <div class="card-body">
                        <div class="mg-b-20">
                            <i data-feather="user"></i>
                        </div>
                        <h5 class="mg-b-2 tx-spacing--1 tx-white"><?= $model->alias; ?></h5>
                        <p class="tx-white"><i class="feather-small" data-feather="phone"></i> <?= $model->phone_number; ?></p>
                        <?php if(!is_null($model->primary_address)):?>
                            <p class="tx-white"><i class="feather-small" data-feather="map-pin"></i> <?= $model->primaryAddress->address; ?></p>
                        <?php endif;?>
                    </div>
                    <div class="card-footer mg-b-15">
                        <div class="d-flex">
                            <a href="<?= Yii::$app->urlManager->createUrl(['customers/update', 'id' => $model->id]) ?>" class="btn btn-xs btn-light"><i data-feather="edit"></i> <?= Yii::t('app','Update')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="mg-b-20">
                    <a class="btn btn-sm pd-x-15 btn-white btn-uppercase" href="<?= Yii::$app->urlManager->createUrl(['customers/view','id' => $model->id]); ?>"><i data-feather="user" class="wd-10 mg-r-5"></i> <?= Yii::t('app','Overview') ?></a>
                    <a class="btn btn-sm pd-x-15 btn-white btn-uppercase mg-l-5" href="<?= Yii::$app->urlManager->createUrl(['customers/requests','id' => $model->id]); ?>"><i data-feather="git-pull-request" class="wd-10 mg-r-5"></i> <?= Yii::t('app','Requests') ?></a>
                    <a class="btn btn-sm pd-x-15 btn-secondary btn-uppercase mg-l-5" href="<?= Yii::$app->urlManager->createUrl(['customers/payments','id' => $model->id]); ?>"><i data-feather="globe" class="wd-10 mg-r-5"></i> <?= Yii::t('app','Payments'); ?></a>
                    <a class="btn btn-sm pd-x-15 btn-white btn-uppercase mg-l-5" href="<?= Yii::$app->urlManager->createUrl(['customers/addresses','id' => $model->id]); ?>"><i data-feather="map-pin" class="wd-10 mg-r-5"></i> <?= Yii::t('app','Addresses'); ?></a>
                </div>
                <div class="row row-xs mg-b-20">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card mg-b-20 mg-lg-b-25">
                            <div class="card-header pd-y-15 pd-x-20 d-flex bg-secondary">
                                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app','Customer Payments') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-dashboard mg-b-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th class="text-end"><?= Yii::t('app','Vendor') ?></th>
                                                <th class="text-end"><?= Yii::t('app','Volume Delivered') ?><s/th>
                                                <th class="text-end"><?= Yii::t('app','Payment Status') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="tx-color-03 tx-normal">03/09/2024</td>
                                                <td class="tx-medium text-end">Axmed Yaasiin Khaliif</td>
                                                <td class="text-end tx-teal">500 <?= Yii::t('app','Litres') ?></td>
                                                <td class="text-end tx-pink">Paid</td>
                                            </tr>
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