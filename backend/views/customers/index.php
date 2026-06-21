<?php

use backend\models\Customers;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app','Manage Customers');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Manage Customers'),
            'links' => [
                ['title' => Yii::t('app','Manage Customers'), 'active' => true]
            ],
            'buttons' => [
                [
                    'link' => Yii::$app->urlManager->createUrl('customers/create'),
                    'icon' => 'user-plus',
                    'title' => Yii::t('app','Add Customer'),
                    'class' => 'btn btn-primary'
                ]
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        
        <div class="row">
            <!-- Left Column - Search Filters -->
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-light">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="filter" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Filter Options') ?>
                        </h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="d-block tx-12 tx-uppercase tx-medium tx-spacing-1 mg-b-10">
                                <i data-feather="phone" class="wd-10 ht-10 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Phone Number') ?>
                            </label>
                            <?= $form->field($model, 'phone_number')->textInput([
                                'class' => 'form-control form-control-sm',
                                'placeholder' => '630112233'
                            ])->label(false) ?>
                        </div>

                        <div class="form-group">
                            <label class="d-block tx-12 tx-uppercase tx-medium tx-spacing-1 mg-b-10">
                                <i data-feather="toggle-right" class="wd-10 ht-10 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'Status') ?>
                            </label>
                            <?= $form->field($model, 'status')->dropDownList([
                                StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'),
                                StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')
                            ], [
                                'class' => 'form-select form-select-sm',
                                'prompt' => Yii::t('app','-- Select Status --')
                            ])->label(false) ?>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <?= Html::submitButton(
                                '<i data-feather="search" class="wd-15 ht-15 stroke-2"></i> ' . Yii::t('app', 'Apply Filters'),
                                ['class' => 'btn btn-primary btn-sm']
                            ) ?>
                            <?php if ($model->hasFilters()): ?>
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
                            <?= Yii::t('app','Download Report') ?>
                        </h6>
                    </div>  
                    <div class="card-body">
                        <?= Html::a(
                            '<i data-feather="download" class="wd-15 ht-15 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Download Report'),
                            ['report'] + ($model->hasFilters() ? ['CustomerSearchForm' => Yii::$app->request->get('CustomerSearchForm')] : []),
                            ['class' => 'btn btn-primary btn-sm']
                        ) ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Customers List -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-white">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="users" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Customers List') ?>
                        </h6>
                        <span class="badge bg-primary"><?= $dataProvider->getTotalCount() ?> <?= Yii::t('app', 'Customers') ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mg-b-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="30%"><?= Yii::t('app','Customer') ?></th>
                                        <th width="20%"><?= Yii::t('app','Contact') ?></th>
                                        <th width="15%"><?= Yii::t('app','Orders') ?></th>
                                        <th width="15%"><?= Yii::t('app','Status') ?></th>
                                        <th width="15%"><?= Yii::t('app','Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= ListView::widget([
                                        'dataProvider' => $dataProvider,
                                        'itemView' => '_customer',
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

