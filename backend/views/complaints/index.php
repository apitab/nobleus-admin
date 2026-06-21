<?php

use backend\models\Complaints;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

use common\helpers\ViewHelper;
use backend\helpers\StatusCodes;;
/** @var yii\web\View $this */
/** @var backend\models\ComplaintsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Customer Complaints');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Vendor & Customer Complaints'),
            'links' => [
                ['title' => Yii::t('app', 'Vendor & Customer Complaints'), 'active' => true]
            ]
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-secondary">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Search & Filter') ?></h6>
                    </div>
                    <?php $form = ActiveForm::begin(['method' => 'GET']); ?>
                    <div class="card-body">
                        <?= $form->field($searchModel, 'type')->dropDownList(
                            Complaints::optsType(),
                            ['class' => 'form-select', 'prompt' => '-- ' . Yii::t('app', 'Select Type') . ' --']
                        ) ?>
                        <?= $form->field($searchModel, 'phone_number')->textInput([
                            'placeholder' => Yii::t('app', 'Enter phone number'),
                            'class' => 'form-control'
                        ]) ?>
                        <?= $form->field($searchModel, 'status')->dropDownList([StatusCodes::ACTIVE_STATUS => Yii::t('app', 'Active'), StatusCodes::DELETE_STATUS => Yii::t('app', 'Inactive')], ['class' => 'form-select', 'prompt' => '-- ' . Yii::t('app', 'Select Status') . ' --']) ?>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton('<i data-feather="search"></i> Filter', ['class' => 'btn btn-primary btn-fill']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between bg-info">
                        <h6 class="tx-uppercase tx-semibold mg-b-0 tx-white"><?= Yii::t('app', 'Vendor & Customer Complaints'); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-responsive table-striped">
                            <thead>
                                <th>#</th>
                                <th><?= Yii::t('app', 'Type'); ?></th>
                                <th><?= Yii::t('app', 'Name'); ?></th>
                                <th><?= Yii::t('app', 'Phone Number'); ?></th>
                                <th><?= Yii::t('app', 'Title'); ?></th>
                                <th><?= Yii::t('app', 'Description'); ?></th>
                                <th><?= Yii::t('app', 'Status'); ?></th>
                                <th><?= Yii::t('app', 'Actions'); ?></th>
                            </thead>
                            <tbody>
                                <?= GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    //'filterModel' => $searchModel,
                                    'columns' => [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        [
                                            'attribute' => 'type',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                return $model->displayType();
                                            }
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Name'),
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                if ($model->type === Complaints::TYPE_CUSTOMERS) {
                                                    $customer = $model->customer;
                                                    if ($customer) {
                                                        return Html::a($customer->alias, 
                                                            ['/customers/view', 'id' => $model->customer_id],
                                                            ['class' => 'text-primary']
                                                        );
                                                    }
                                                } elseif ($model->type === Complaints::TYPE_VENDORS) {
                                                    $vendor = $model->vendor;
                                                    if ($vendor) {
                                                        $name = trim($vendor->first_name . ' ' . $vendor->other_names);
                                                        return Html::a($name, 
                                                            ['/vendors/view', 'id' => $model->customer_id],
                                                            ['class' => 'text-primary']
                                                        );
                                                    }
                                                }
                                                return '-';
                                            }
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Phone Number'),
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                if ($model->type === Complaints::TYPE_CUSTOMERS) {
                                                    $customer = $model->customer;
                                                    return $customer ? $customer->phone_number : '-';
                                                } elseif ($model->type === Complaints::TYPE_VENDORS) {
                                                    $vendor = $model->vendor;
                                                    return $vendor ? $vendor->mobile_number : '-';
                                                }
                                                return '-';
                                            }
                                        ],
                                        'title',
                                        'description:ntext',
                                        [
                                            'attribute' => 'status',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                switch($model->status) {
                                                    case 1:
                                                        return '<span class="badge bg-primary">New</span>';
                                                    case 2:
                                                        return '<span class="badge bg-warning">Being Handled</span>';
                                                    case 3:
                                                        return '<span class="badge bg-success">Resolved</span>';
                                                    default:
                                                        return '<span class="badge bg-secondary">Unknown</span>';
                                                }
                                            }
                                        ],
                                        [
                                            'attribute' => 'date_created',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                return Yii::$app->formatter->asDatetime($model->date_created, 'php:M d, Y h:i A');
                                            }
                                        ],
                                        [
                                            'class' => ActionColumn::class,
                                            'template' => '{view}',
                                            'urlCreator' => function ($action, Complaints $model, $key, $index, $column) {
                                                return Url::toRoute([$action, 'id' => $model->id]);
                                            }
                                        ],
                                    ],
                                ]); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>