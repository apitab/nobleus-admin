<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;

use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;
use backend\models\Locations;
use backend\models\PaymentStatuses;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage & View Customer Requests';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Manage Customer Requests'),
            'links' => [
                ['title' => Yii::t('app', 'Manage Customer Requests'), 'active' => true]
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>

        <div class="row">
            <!-- Left Column - Filters -->
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-light">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="filter" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Filter Options') ?>
                        </h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <!-- Customer Section -->
                        <div class="form-group mb-3">
                            <label class="d-block tx-12 tx-uppercase tx-medium tx-spacing-1 mg-b-10">
                                <i data-feather="users" class="wd-10 ht-10 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Search by User') ?>
                            </label>
                            <?= $form->field($model, 'customer_phone_number')->textInput([
                                'class' => 'form-control form-control-sm',
                                'placeholder' => Yii::t('app', 'Customer Phone: 0700112233')
                            ])->label(false) ?>

                            <?= $form->field($model, 'vendor_phone_number')->textInput([
                                'class' => 'form-control form-control-sm',
                                'placeholder' => Yii::t('app', 'Vendor Phone: 0700112233')
                            ])->label(false) ?>
                        </div>

                        <!-- Date Range Section -->
                        <div class="form-group mb-3">
                            <label class="d-block tx-12 tx-uppercase tx-medium tx-spacing-1 mg-b-10">
                                <i data-feather="calendar" class="wd-10 ht-10 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Date Range') ?>
                            </label>
                            <?= $form->field($model, 'date_from')->input('date', [
                                'class' => 'form-control form-control-sm'
                            ])->label(false) ?>

                            <?= $form->field($model, 'date_to')->input('date', [
                                'class' => 'form-control form-control-sm'
                            ])->label(false) ?>
                        </div>

                        <!-- Status Section -->
                        <div class="form-group">
                            <label class="d-block tx-12 tx-uppercase tx-medium tx-spacing-1 mg-b-10">
                                <i data-feather="tag" class="wd-10 ht-10 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Request Status') ?>
                            </label>
                            <?= $form->field($model, 'status')->dropDownList([
                                StatusCodes::NEW_CUSTOMER_REQUEST => Yii::t('app', 'New Request'),
                                StatusCodes::NEW_CUSTOMER_REQUEST_OPEN => Yii::t('app', 'New Request Open'),
                                StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST => Yii::t('app', 'On the Way'),
                                StatusCodes::DELIVERED_CUSTOMER_REQUEST => Yii::t('app', 'Delivered'),
                                StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST => Yii::t('app', 'Payment Initiated'),
                                StatusCodes::COMPLETED_CUSTOMER_REQUEST => Yii::t('app', 'Paid'),
                                StatusCodes::CUSTOMER_CANCELLED_REQUEST => Yii::t('app', 'Cancelled by Customer')
                            ], [
                                'class' => 'form-select form-select-sm',
                                'prompt' => Yii::t('app', '-- Select Status --')
                            ])->label(false) ?>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <?= Html::submitButton(
                                '<i data-feather="search" class="wd-15 ht-15 stroke-2"></i> ' . Yii::t('app', 'Apply Filters'),
                                ['class' => 'btn btn-primary btn-sm']
                            ) ?>
                            <?php if ($search): ?>
                                <?= Html::a(
                                    '<i data-feather="refresh-cw" class="wd-15 ht-15 stroke-2"></i> ' . Yii::t('app', 'Reset'),
                                    ['index'],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="card mg-t-20">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-light">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="download" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Download Report') ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?= Html::a(
                            '<i data-feather="download" class="me-1"></i> Download Report',
                            ['report'] + Yii::$app->request->queryParams,
                            [
                                'class' => 'btn btn-secondary btn-sm',
                                'data-toggle' => 'tooltip',
                                'title' => 'Download Excel Report'
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Requests List -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-white">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="list" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Customer Requests') ?>
                        </h6>
                        <span class="badge bg-primary"><?= $dataProvider->getTotalCount() ?>
                            <?= Yii::t('app', 'Requests') ?></span>
                    </div>
                    <div class="card-body p-0">
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
    </div>
</div>

<?php
$js = <<<JS
    // Initialize Feather icons
    if (typeof feather !== 'undefined') feather.replace();
JS;
$this->registerJs($js);
?>