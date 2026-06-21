<?php

use backend\models\VendorGroupPoints;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\widgets\ListView;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Vendor Group Points';
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="truck" class="feather-medium"></i> ' . Yii::t('app', 'Manage Vendor Group Rewards points'),
            'links' => [
                ['title' => Yii::t('app', 'Vendor Groups Reward Points'), 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('vendor-group-points/create'), 'icon' => 'plus-circle', 'title' => Yii::t('app', 'Add Vendor Group rewards')]
            ]
            //'rangeFilter' => true,
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="card">
            <div class="card-header pd-y-15 pd-x-20 bg-info">
                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'List of Vendor Groups') ?></h6>
            </div>
            <div class="card-body">
                <table class="table table-responsive table-striped">
                    <thead>
                        <th>#</th>
                        <th><?= Yii::t('app', 'Vendor Group') ?></th>
                        <th><?= Yii::t('app', 'Min Distance') ?></th>
                        <th><?= Yii::t('app', 'Max Distance') ?></th>
                        <th><?= Yii::t('app', 'point per/km') ?></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <?=
                            ListView::widget([
                                'dataProvider' => $dataProvider,
                                'itemView' => '_points',
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