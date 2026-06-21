<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\InformationGuides $model */

$this->title = Yii::t('app', 'Create Information Guide');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="plus-circle" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Create Information Guide'),
            'links' => [
                ['title' => Yii::t('app', 'Information Guides'), 'url' => Yii::$app->urlManager->createUrl('information-guides')],
                ['title' => Yii::t('app', 'Create'), 'active' => true],
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
                            <i data-feather="help-circle" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Guide Tips') ?>
                        </h6>
                    </div>
                    <div class="card-body pd-20">
                        <ul class="list-unstyled tx-13 mg-b-0">
                            <li class="mg-b-10">
                                <i data-feather="check-circle" class="wd-15 ht-15 stroke-2 mg-r-5 tx-success"></i>
                                <?= Yii::t('app', 'Keep titles clear and concise') ?>
                            </li>
                            <li class="mg-b-10">
                                <i data-feather="check-circle" class="wd-15 ht-15 stroke-2 mg-r-5 tx-success"></i>
                                <?= Yii::t('app', 'Use simple language in descriptions') ?>
                            </li>
                            <li class="mg-b-10">
                                <i data-feather="check-circle" class="wd-15 ht-15 stroke-2 mg-r-5 tx-success"></i>
                                <?= Yii::t('app', 'Choose the appropriate guide type') ?>
                            </li>
                            <li>
                                <i data-feather="check-circle" class="wd-15 ht-15 stroke-2 mg-r-5 tx-success"></i>
                                <?= Yii::t('app', 'Ensure content is helpful and relevant') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
