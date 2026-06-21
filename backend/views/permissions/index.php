<?php

use backend\models\Permissions;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
use backend\models\Groups;
use backend\models\Modules;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\helpers\ArrayHelper;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage Permissions';
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="arrow-right-circle" class="feather-medium"></i> Manage Permissions',
            'links' => [
                ['title' => 'Settings', 'active' => true],
                ['title' => 'Manage Permissions', 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('permissions/create'), 'icon' => 'plus-circle', 'title' => 'Add Permission']
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex bg-secondary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white">Search & Filter</h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'group')->dropDownList(ArrayHelper::map(Groups::findAll(['status' => StatusCodes::ACTIVE_STATUS]), 'id', 'name'), ['prompt' => '-- Select Group --', 'class' => 'form-select']) ?>
                        <?= $form->field($model, 'module')->dropDownList(ArrayHelper::map(Modules::findAll(['status' => StatusCodes::ACTIVE_STATUS]), 'id', 'description'), ['prompt' => '-- Select Module --', 'class' => 'form-select']) ?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton('<i data-feather="search"></i> Filter', ['class' => 'btn btn-primary btn-fill']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex bg-info">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white">List of Permissions</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($search) : ?>
                            <div class="alert alert-info justify-content-between">
                                <div>
                                You searched for: 
                                </div>
                                <div>
                                    <a href="<?= Yii::$app->urlManager->createUrl('permissions');?>"><i data-feather="refresh-cw" class="feather-small"></i> Reset</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <table class="table table-striped table-responsive">
                            <thead>
                                <th>#</th>
                                <th>Group</th>
                                <th>Module</th>
                                <th>Module Action</th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0</td>
                                    <td>Administrator</td>
                                    <td>All Modules</td>
                                    <td>All Actions</td>
                                    <td></td>
                                </tr>
                                <?=
                                ListView::widget([
                                    'dataProvider' => $dataProvider,
                                    'itemView' => '_permission',
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