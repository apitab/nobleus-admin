<?php

use backend\models\Kiosks;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use yii\web\YiiAsset;
use yii\helpers\ArrayHelper;
use backend\models\Locations;

use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Kiosks');
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Manage Kiosks'),
            'links' => [
                ['title' => Yii::t('app','Kiosks'), 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('kiosks/create'), 'icon' => 'plus-circle', 'title' => Yii::t('app','Add Kiosk')]
            ]
            //'rangeFilter' => true,
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-secondary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 text-white"><?= Yii::t('app','Search & Filter')?></h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Kiosk name']) ?>
                        <?= $form->field($model, 'location')->dropDownList(ArrayHelper::map(Locations::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->all(), 'id', 'name'), ['placeholder' => '-- Select Location', 'class' => 'form-select form-select-sm'])?>
                        <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'),StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')],['class' => 'form-select','prompt' => Yii::t('app','-- Select Status --')])?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton('<i data-feather="search"></i> Filter', ['class' => 'btn btn-primary btn-fill']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-info">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 text-white"><?= Yii::t('app','List of Kiosks')?></h6>
                    </div>
                    <div class="card-body">
                        <?php if ($search) : ?>
                            <div class="alert alert-info justify-content-between">
                                <div>
                                <?= Yii::t('app','You searched for')?>: 
                                </div>
                                <div>
                                    <a href="<?= Yii::$app->urlManager->createUrl('kiosks');?>"><i data-feather="refresh-cw" class="feather-small"></i> <?= Yii::t('app','Reset')?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <table class="table table-striped">
                            <thead class="">
                                <th>#</th>
                                <th><?= Yii::t('app','Kiosk Name') ?></th>
                                <th><?= Yii::t('app','Location') ?></th>
                                <th><?= Yii::t('app','Location Description') ?></th>
                                <th><?= Yii::t('app','Coordinates') ?></th>
                                <th><?= Yii::t('app','Operating Hours') ?></th>
                                <th><?= Yii::t('app','Open/Closed') ?></th>
                                <th><?= Yii::t('app','System Status') ?></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <?=
                                ListView::widget([
                                    'dataProvider' => $dataProvider,
                                    'itemView' => '_kiosk',
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