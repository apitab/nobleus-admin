<?php

use yii\helpers\Html;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Users $model */

$this->title = 'Update User: ' . $model->user_id;
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app','Update User') . ': ' . $model->user_id,
            'links' => [
                ['title' => Yii::t('app','Users'), 'url' => Yii::$app->urlManager->createUrl('users')],
                ['title' => Yii::t('app','Update User') . ': ' . $model->user_id, 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'groups' => $groups
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>