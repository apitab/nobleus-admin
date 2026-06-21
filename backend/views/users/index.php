<?php

use backend\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage Users';
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="users" class="feather-medium"></i> ' . Yii::t('app','Manage Users'),
            'links' => [
                ['title' => Yii::t('app','Manage Users'), 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('users/create'), 'icon' => 'plus-circle', 'title' => Yii::t('app','Add User')]
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row row-xs">
            <div class="col-sm-12 col-lg-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-secondary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app','Search & Filter')?></h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <?= $form->field($model, 'phone_number')->textInput(['placeholder' => '630112233']) ?>
                        <?= $form->field($model, 'email_address')->textInput(['placeholder' => 'email@email.com']) ?>
                        <?= $form->field($model, 'group')->dropDownList(ArrayHelper::map($groups, 'id', 'name'), ['prompt' => '-- Select Group --', 'class' => 'form-select']) ?>
                        <?= $form->field($model, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app','Active'), StatusCodes::DELETE_STATUS => Yii::t('app','Inactive')], ['class' => 'form-select', 'prompt' => Yii::t('app','-- Select status --')]) ?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton('<i data-feather="search"></i> Filter', ['class' => 'btn btn-primary btn-fill']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-sm-12 col-lg-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 bg-info">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app','List of Users')?></h6>
                    </div>
                    <div class="card-body">
                        <?php if ($search) : ?>
                            <div class="alert alert-info justify-content-between">
                                <div>
                                    <?= Yii::t('app','You searched for')?>:
                                </div>
                                <div>
                                    <a href="<?= Yii::$app->urlManager->createUrl('users'); ?>"><i data-feather="refresh-cw" class="feather-small"></i> <?= Yii::t('app','Reset'); ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <table class="table table-responsive table-striped">
                            <thead>
                                <th>#</th>
                                <th><?= Yii::t('app','Phone Number')?></th>
                                <th><?= Yii::t('app','Email Address')?></th>
                                <th><?= Yii::t('app','Role')?></th>
                                <th><?= Yii::t('app','Status')?></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <?=
                                ListView::widget([
                                    'dataProvider' => $dataProvider,
                                    'itemView' => '_user',
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