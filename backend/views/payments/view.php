<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;
/** @var yii\web\View $this */
/** @var backend\models\Payments $model */

$this->title = 'View Payment #' . $model->id;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => 'View Payment #' . $model->id,
            'links' => [
                ['title' => 'Payments', 'url' => Yii::$app->urlManager->createUrl('payments')],
                ['title' => 'View Payment', 'active' => true],
                ['title' => 'Payment #' . $model->id, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0"><?= Yii::t('app', 'Payment Details') ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <p class="tx-13 tx-gray-600 mg-b-10"><?= Yii::t('app', 'Payment ID') ?> #<?= $model->id ?></p>
                                <?= DetailView::widget([
                                    'model' => $model,
                                    'attributes' => [
                                        'amount',
                                        'description',
                                        [
                                            'attribute' => 'payment_method_id',
                                            'value' => $model->paymentMethod->name,
                                            'label' => Yii::t('app', 'Payment Method')
                                        ],
                                        [
                                            'attribute' => 'payment_status_id',
                                            'value' => StatusCodes::getPaymentStatusText($model->status),
                                            'label' => Yii::t('app', 'Payment Status')
                                        ],
                                        'date_created',
                                        'date_modified',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-uppercase tx-semibold mg-b-0"><?= Yii::t('app', 'Request Details') ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <p class="tx-13 tx-gray-600 mg-b-10"><?= Yii::t('app', 'Request ID') ?> #<?= $model->request_id ?></p>
                                <?= DetailView::widget([
                                    'model' => $model->request,
                                    'attributes' => [
                                        [
                                            'attribute' => 'Customer',
                                            'value' => $model->customerRequest->customer->alias,
                                            'label' => Yii::t('app', 'Customer')
                                        ],
                                        [
                                            'attribute' => 'Vendor',
                                            'value' => $model->request->vendor->other_names . ', ' . $model->request->vendor->first_name,
                                            'label' => Yii::t('app', 'Vendor')
                                        ],
                                        [
                                            'attribute' => 'Delivery Date',
                                            'value' => $model->customerRequest->delivery_date,
                                            'label' => Yii::t('app', 'Delivery Date')
                                        ],
                                        [
                                            'attribute' => 'Delivery Notes',
                                            'value' => $model->customerRequest->delivery_notes,
                                            'label' => Yii::t('app', 'Delivery Notes')
                                        ],
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>