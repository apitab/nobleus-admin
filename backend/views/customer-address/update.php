<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\CustomerAddress $model */

$this->title = 'Update Customer Address: ' . $model->id;
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Update Address') . ' - ' . $customer->alias,
            'links' => [
                ['title' => Yii::t('app','Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app','View Customer'), 'url' => Yii::$app->urlManager->createUrl(['customers/view','id'=>$customer->id])],
                ['title' => Yii::t('app','Update Address'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'locations'=>$locations
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>