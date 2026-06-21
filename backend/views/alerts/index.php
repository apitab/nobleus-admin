<?php

use backend\models\Alerts;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;
YiiAsset::register($this);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Alerts')
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', '<i data-feather="bell" class="feather-medium"></i>  Manage Alerts'),
            'links' => [
                ['title' => Yii::t('app', 'Manage Alerts'), 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('alerts/create'), 'icon' => 'plus-circle', 'title' => Yii::t('app', 'Add Alert')]
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="card">
            <div class="card-header pd-y-15 pd-x-20 d-flex bg-info">
                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'List of Alerts') ?></h6>
            </div>
            <div class="card-body">
                <table class="table table-responsive table-striped">
                    <thead>
                        <th>#</th>
                        <th><?= Yii::t('app', 'Title') ?></th>
                        <th><?= Yii::t('app', 'Description') ?></th>
                        <th><?= Yii::t('app', 'Level') ?></th>
                        <th><?= Yii::t('app', 'Type') ?></th>
                        <th><?= Yii::t('app', 'Expiry Date') ?></th>
                        <th><?= Yii::t('app', 'Attachment') ?></th>
                        <th><?= Yii::t('app', 'Status') ?></th>
                        <th><?= Yii::t('app', 'Resend Alerts') ?></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <?=
                        ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemView' => '_alert',
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
