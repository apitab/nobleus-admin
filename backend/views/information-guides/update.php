<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\InformationGuides $model */

$this->title = Yii::t('app', 'Update Information Guide: {name}', [
    'name' => $model->title,
]);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="edit" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Update Guide'),
            'links' => [
                ['title' => Yii::t('app', 'Information Guides'), 'url' => Yii::$app->urlManager->createUrl('information-guides')],
                ['title' => Yii::t('app', 'Update'), 'active' => true],
                ['title' => Html::encode($model->title), 'active' => true],
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>

        <div class="row">
            <div class="col-lg-8">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="info" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Guide Details') ?>
                        </h6>
                    </div>
                    <div class="card-body pd-20">
                        <div class="d-flex align-items-center mg-b-15">
                            <i data-feather="calendar" class="wd-15 ht-15 stroke-2 mg-r-10"></i>
                            <div>
                                <label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-0">
                                    <?= Yii::t('app', 'Created') ?>
                                </label>
                                <p class="tx-13 tx-color-03 mg-b-0">
                                    <?= Yii::$app->formatter->asDatetime($model->date_created) ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i data-feather="clock" class="wd-15 ht-15 stroke-2 mg-r-10"></i>
                            <div>
                                <label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-0">
                                    <?= Yii::t('app', 'Last Modified') ?>
                                </label>
                                <p class="tx-13 tx-color-03 mg-b-0">
                                    <?= Yii::$app->formatter->asDatetime($model->date_modified) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
