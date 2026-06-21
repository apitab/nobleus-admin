<?php

use yii\helpers\Html;

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Vendors $model */

$this->title = 'Create Vendor';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Add Vendor'),
            'links' => [
                ['title' => Yii::t('app','Vendors'), 'url' => Yii::$app->urlManager->createUrl('vendors')],
                ['title' => Yii::t('app','Add Vendor'), 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'locations'=>$locations,
                        'waterSources'=>$waterSources,
                        'vendorGroups' => $vendorGroups
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>