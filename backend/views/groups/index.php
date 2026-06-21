<?php

use backend\models\Groups;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;

use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="arrow-right-circle" class="feather-medium"></i> Manage Groups',
            'links' => [
                ['title' => Yii::t('app','Settings'), 'active' => true],
                ['title' => Yii::t('app','Manage Groups'), 'active' => true]
            ],
            'buttons' => [
                ['link' => Yii::$app->urlManager->createUrl('groups/create'), 'icon' => 'plus-circle', 'title' => Yii::t('app','Add Group')]
            ]
            //'rangeFilter' => true,
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="card">
            <div class="card-header pd-y-15 pd-x-20 d-flex bg-info">
                <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app','List of Groups')?></h6>
            </div>
            <div class="card-body">
                <table class="table-responsive table table-striped">
                    <thead>
                        <th>#</th>
                        <th><?= Yii::t('app','Name')?></th>
                        <th><?= Yii::t('app','Description')?></th>
                        <th></th>
                        <th><?= Yii::t('app','Status')?></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <?=
                        ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemView' => '_group',
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