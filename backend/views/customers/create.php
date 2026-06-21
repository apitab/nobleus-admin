<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Customers $model */

$this->title = 'Add new customer';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Add New Customer'),
            'links' => [
                ['title' => Yii::t('app','Manage Customers'), 'url' => Yii::$app->urlManager->createUrl('customers')],
                ['title' => Yii::t('app','Add New Customer'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>