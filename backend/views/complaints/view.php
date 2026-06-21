<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\ViewHelper;
use yii\widgets\ActiveForm;
use backend\models\Complaints;

/** @var yii\web\View $this */
/** @var backend\models\Complaints $model */

\yii\web\YiiAsset::register($this);

$this->title =
    $this->title = "View Complaint - " . $model->title;
\yii\web\YiiAsset::register($this);
?>
<div class="content pd-t-20 content-profile">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Complaint Details') . ' - ' . $model->title,
            'links' => [
                ['title' => Yii::t('app','Complaints'), 'url' => Yii::$app->urlManager->createUrl('complaints')],
                ['title' => Yii::t('app','View Complaint'), 'active' => true],
                ['title' => $model->title, 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        
        <div class="row">
            <!-- Complaint Details Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-primary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Complaint Details') ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Type') ?>:</div>
                            <div class="col-md-8"><?= $model->displayType() ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">
                                <?= $model->type === Complaints::TYPE_CUSTOMERS ? Yii::t('app', 'Customer') : Yii::t('app', 'Vendor') ?>:
                            </div>
                            <div class="col-md-8">
                                <?php
                                if ($model->type === Complaints::TYPE_CUSTOMERS) {
                                    $customer = $model->customer;
                                    if ($customer) {
                                        echo Html::a($customer->alias, ['/customers/view', 'id' => $model->customer_id], ['class' => 'text-primary']);
                                    } else {
                                        echo '-';
                                    }
                                } elseif ($model->type === Complaints::TYPE_VENDORS) {
                                    $vendor = $model->vendor;
                                    if ($vendor) {
                                        $name = trim($vendor->first_name . ' ' . $vendor->other_names);
                                        echo Html::a($name, ['/vendors/view', 'id' => $model->customer_id], ['class' => 'text-primary']);
                                    } else {
                                        echo '-';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Phone Number') ?>:</div>
                            <div class="col-md-8">
                                <?php
                                if ($model->type === Complaints::TYPE_CUSTOMERS) {
                                    $customer = $model->customer;
                                    echo $customer ? Html::encode($customer->phone_number) : '-';
                                } elseif ($model->type === Complaints::TYPE_VENDORS) {
                                    $vendor = $model->vendor;
                                    echo $vendor ? Html::encode($vendor->mobile_number) : '-';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Title') ?>:</div>
                            <div class="col-md-8"><?= Html::encode($model->title) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Description') ?>:</div>
                            <div class="col-md-8"><?= Html::encode($model->description) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Date Submitted') ?>:</div>
                            <div class="col-md-8"><?= Yii::$app->formatter->asDatetime($model->date_created, 'php:M d, Y h:i A') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold"><?= Yii::t('app', 'Status') ?>:</div>
                            <div class="col-md-8">
                                <?php
                                switch($model->status) {
                                    case 1:
                                        echo '<span class="badge bg-primary">New</span>';
                                        break;
                                    case 2:
                                        echo '<span class="badge bg-warning">Being Handled</span>';
                                        break;
                                    case 3:
                                        echo '<span class="badge bg-success">Resolved</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolution Form Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-info">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Complaint Resolution') ?></h6>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin(); ?>
                        
                        <?= $form->field($model, 'resolution_notes')->textarea(['rows' => 6, 'placeholder' => Yii::t('app', 'Enter resolution notes here...')]) ?>

                        <?= $form->field($model, 'status')->dropDownList([
                            1 => 'New',
                            2 => 'Being Handled',
                            3 => 'Resolved'
                        ], ['class' => 'form-select']) ?>

                        <div class="form-group mt-4">
                            <?= Html::submitButton(Yii::t('app', 'Update Status'), ['class' => 'btn btn-primary']) ?>
                            
                            <?php if ($model->status !== 3): ?>
                                <?= Html::submitButton(Yii::t('app', 'Mark as Resolved'), [
                                    'class' => 'btn btn-success',
                                    'name' => 'resolve',
                                    'value' => '1'
                                ]) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

