<?php

use backend\models\Payments;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\ListView;
use backend\models\PaymentMethods;
use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Manage Withdrawals');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="credit-card" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app','Manage Withdrawals'),
            'links' => [
                ['title' => Yii::t('app','Withdrawals'), 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        
        <div class="row">
            <!-- Filter Section -->
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="filter" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Search & Filter')?>
                        </h6>
                        <?php if ($model->hasFilters()): ?>
                            <a href="<?= Url::to(['withdrawals/index']) ?>" class="tx-12 tx-color-03" data-toggle="tooltip" title="<?= Yii::t('app', 'Clear Filters') ?>">
                                <i data-feather="x" class="wd-15 ht-15 stroke-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'phoneNumber')->textInput([
                            'placeholder' => '639123456789',
                            'class' => 'form-control form-control-sm',
                        ])->label(Yii::t('app', 'Vendor Phone')) ?>

                        <?= $form->field($model, 'firstName')->textInput([
                            'placeholder' => 'First Name',
                            'class' => 'form-control form-control-sm',
                        ])->label(Yii::t('app', 'First Name')) ?>

                        <?= $form->field($model, 'lastName')->textInput([
                            'placeholder' => 'Last Name',
                            'class' => 'form-control form-control-sm',
                        ])->label(Yii::t('app', 'Last Name')) ?>

                        <?= $form->field($model, 'status')->dropDownList(
                            [
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'complete' => 'Completed',
                                'failed' => 'Failed'
                            ],
                            [
                                'class' => 'form-select form-select-sm',
                                'prompt' => Yii::t('app','-- Select Status --')
                            ]
                        ) ?>
                    </div>
                    <div class="card-footer pd-15">
                        <?= Html::submitButton(
                            '<i data-feather="search" class="wd-15 ht-15 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Apply Filters'), 
                            ['class' => 'btn btn-primary btn-sm btn-block']
                        ) ?>
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

            <!-- Payments List Section -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="list" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Withdrawals List')?>
                            <span class="badge badge-primary mg-l-5"><?= $dataProvider->getTotalCount() ?></span>
                        </h6>
                    </div>
                    <div class="card-body pd-0">
                        <?php if ($search) : ?>
                            <div class="alert alert-info mg-20 d-flex align-items-center justify-content-between">
                                <span>
                                    <i data-feather="info" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                    <?= Yii::t('app','Search Results for:')?> <?= $searchText; ?>
                                </span>
                                <a href="<?= Url::to(['withdrawals/index']) ?>" class="btn btn-sm btn-white">
                                    <i data-feather="refresh-cw" class="wd-15 ht-15 stroke-2"></i>
                                    <?= Yii::t('app','Reset')?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-hover mg-b-0">
                                <thead class="thead-primary">
                                    <tr>
                                        <th class="tx-12"><?= Yii::t('app', 'Id') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Type') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Name') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Amount') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Date') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Status') ?></th>
                                        <th class="tx-12 text-right"><?= Yii::t('app', 'Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= ListView::widget([
                                        'dataProvider' => $dataProvider,
                                        'itemView' => '_item',
                                        'layout' => "{items}",
                                        'itemOptions' => [
                                            'tag' => false
                                        ],
                                    ]); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer pd-y-15 pd-x-20">
                        <?= ListView::widget([
                            'dataProvider' => $dataProvider,
                            //'itemView' => '_item',
                            'layout' => "{pager}",
                            'itemOptions' => [
                                'tag' => false
                            ],
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>