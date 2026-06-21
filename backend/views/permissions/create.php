<?php

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var backend\models\Permissions $model */

$this->title = 'Add Permission';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => 'Add Permission',
            'links' => [
                ['title' => 'Permissions', 'url' => Yii::$app->urlManager->createUrl('permissions')],
                ['title' => 'Add Permission', 'active' => true]
            ],
        ]); ?>
        <div class="row row-xs">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <?= ViewHelper::displayFlash(); ?>
                <div class="card">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'groups' => $groups,
                        'modules' => $modules,
                        'module_actions' => $moduleActions
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
