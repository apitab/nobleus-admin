<?php

use backend\models\VendorGroups;
use backend\models\WaterSources;
use backend\models\Locations;
use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;
use backend\models\VendorSearchForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage Vendors';
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Manage Vendors'),
            'links' => [
                ['title' => Yii::t('app','Vendors'), 'active' => true]
            ],
            'buttons' => [
                [
                    'link' => Yii::$app->urlManager->createUrl('vendors/create'),
                    'icon' => 'plus-circle',
                    'title' => Yii::t('app','Add Vendor'),
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
                            <?= Yii::t('app','Filter Options')?>
                        </h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <?= $form->field($model, 'phone_number')->textInput([
                                'maxlength' => true,
                                'placeholder' => '0700112233',
                                'class' => 'form-control form-control-sm'
                            ])->label(Yii::t('app', 'Phone Number')) ?>
                        </div>
                        <div class="form-group mb-3">
                            <?= $form->field($model, 'first_name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Vendor name',
                                'class' => 'form-control form-control-sm'
                            ])->label(Yii::t('app', 'Vendor Names')) ?>
                        </div>
                        <div class="form-group mb-3">
                            <?= $form->field($model, 'vendor_group')->dropDownList(
                                ArrayHelper::map(VendorGroups::findAll(['status' => StatusCodes::ACTIVE_STATUS]), 'id', 'name'),
                                [
                                    'prompt' => Yii::t('app','-- Select Group --'),
                                    'class' => 'form-select form-select-sm'
                                ]
                            ) ?>
                        </div>

                        <div class="form-group mb-3">
                            <?= $form->field($model, 'tank_volume')->textInput([
                                'placeholder' => '200',
                                'class' => 'form-control form-control-sm'
                            ]) ?>
                        </div>
<!-- 
                        <div class="form-group mb-3">
                            <?= $form->field($model, 'water_source')->dropDownList(
                                ArrayHelper::map(WaterSources::findAll(['status' => StatusCodes::ACTIVE_STATUS]), 'id', 'name'),
                                [
                                    'prompt' => Yii::t('app','-- Select Source --'),
                                    'class' => 'form-select form-select-sm'
                                ]
                            ) ?>
                        </div> -->

                        <div class="form-group mb-3">
                            <?= $form->field($model, 'status')->dropDownList(
                                [
                                    StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'),
                                    StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')
                                ],
                                [
                                    'prompt' => Yii::t('app','-- Select Status --'),
                                    'class' => 'form-select form-select-sm'
                                ]
                            )?>
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
                                    ['vendors/index'],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Right Column - Vendors List -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-white">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="users" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Vendors List')?>
                        </h6>
                        <span class="badge bg-primary"><?= $dataProvider->getTotalCount() ?> <?= Yii::t('app', 'Vendors') ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mg-b-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="35%"><?= Yii::t('app','Vendor Name') ?></th>
                                        <th width="15%"><?= Yii::t('app','Phone Number') ?></th>
                                        <th width="15%"><?= Yii::t('app','Vendor Type') ?></th>
                                        <th width="10%"><?= Yii::t('app','Volume') ?></th>
                                        <th width="10%"><?= Yii::t('app','Status') ?></th>
                                        <th width="10%"><?= Yii::t('app','Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= ListView::widget([
                                        'dataProvider' => $dataProvider,
                                        'itemView' => '_vendor',
                                        'layout' => "{items}\n<div class='card-footer'>{pager}</div>",
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>