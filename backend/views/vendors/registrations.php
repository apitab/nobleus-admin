<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;
use backend\models\Vendors;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var backend\models\Vendors $model */
/** @var bool $search */

$this->title = Yii::t('app', 'Pending Registrations');
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Pending Vendor Registrations'),
            'links' => [
                ['title' => Yii::t('app','Vendors'), 'url' => Yii::$app->urlManager->createUrl('vendors/index')],
                ['title' => Yii::t('app','Registrations'), 'active' => true]
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
                            <?= Yii::t('app','Search')?>
                        </h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <?= $form->field($model, 'mobile_number')->textInput([
                                'maxlength' => true,
                                'placeholder' => '0700112233',
                                'class' => 'form-control form-control-sm',
                                'name' => 'Vendors[phone_number]',
                                'value' => Yii::$app->request->get('Vendors')['phone_number'] ?? ''
                            ])->label(Yii::t('app', 'Phone Number')) ?>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <?= Html::submitButton(
                                '<i data-feather="search" class="wd-15 ht-15 stroke-2"></i> ' . Yii::t('app', 'Search'),
                                ['class' => 'btn btn-primary btn-sm']
                            ) ?>
                            <?php if ($search): ?>
                                <?= Html::a(
                                    '<i data-feather="refresh-cw" class="wd-15 ht-15 stroke-2"></i> ' . Yii::t('app', 'Reset'),
                                    ['vendors/registrations'],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Right Column - Registrations List -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-white">
                        <h6 class="tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="user-plus" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app','Pending Registrations')?>
                        </h6>
                        <span class="badge bg-warning"><?= $dataProvider->getTotalCount() ?> <?= Yii::t('app', 'Pending') ?></span>
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

<?php
$js = <<<JS
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
JS;
$this->registerJs($js);
?>

