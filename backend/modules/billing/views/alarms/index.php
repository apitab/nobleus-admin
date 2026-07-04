<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterAlarm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var backend\modules\billing\models\MeterAlarmSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */
/** @var array $meterTypes */

$this->title = Yii::t('app', 'Alarms Panel');
?>
<div class="content pd-t-20">
    <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
        'title' => '<i data-feather="alert-triangle" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Alarms Panel'),
        'links' => [
            ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
            ['title' => Yii::t('app', 'Alarms'), 'active' => true],
        ],
    ]); ?>
    <?= ViewHelper::displayFlash(); ?>

    <!-- Stats Cards -->
    <div class="row row-xs mg-b-20">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8"><?= Yii::t('app', 'Active Alarms') ?></h6>
                <div class="d-flex d-lg-block d-xl-flex align-items-end">
                    <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1"><?= number_format($stats['total_active']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
            <div class="card card-body bg-danger">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-white-8 tx-semibold mg-b-8"><?= Yii::t('app', 'Critical') ?></h6>
                <div class="d-flex d-lg-block d-xl-flex align-items-end">
                    <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1 tx-white"><?= number_format($stats['critical']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
            <div class="card card-body bg-warning">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-white-8 tx-semibold mg-b-8"><?= Yii::t('app', 'Warning') ?></h6>
                <div class="d-flex d-lg-block d-xl-flex align-items-end">
                    <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1 tx-white"><?= number_format($stats['warning']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
            <div class="card card-body bg-success">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-white-8 tx-semibold mg-b-8"><?= Yii::t('app', 'Cleared Today') ?></h6>
                <div class="d-flex d-lg-block d-xl-flex align-items-end">
                    <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1 tx-white"><?= number_format($stats['cleared_today']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="card mg-b-15">
        <div class="card-body pd-y-10">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= Html::a('<i data-feather="list" class="wd-16 ht-16 mg-r-5"></i>' . Yii::t('app', 'All Alarms'), ['index'], ['class' => 'btn btn-sm btn-primary mg-r-5']) ?>
                    <?= Html::a('<i data-feather="cpu" class="wd-16 ht-16 mg-r-5"></i>' . Yii::t('app', 'Meters Status'), ['meters'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card mg-b-15">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row row-xs">
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'serial_number')->textInput(['placeholder' => Yii::t('app', 'Serial Number')])->label(false) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'meter_type')->dropDownList(
                        array_combine($meterTypes, $meterTypes),
                        ['prompt' => Yii::t('app', 'All Meter Types')]
                    )->label(false) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'alarm_type')->dropDownList(
                        MeterAlarm::getAlarmTypes(),
                        ['prompt' => Yii::t('app', 'All Alarm Types')]
                    )->label(false) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'severity')->dropDownList(
                        MeterAlarm::getSeverities(),
                        ['prompt' => Yii::t('app', 'All Severities')]
                    )->label(false) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'status')->dropDownList(
                        MeterAlarm::getStatuses(),
                        ['prompt' => Yii::t('app', 'All Statuses')]
                    )->label(false) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary btn-block']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Alarms Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mg-b-0"><?= Yii::t('app', 'Alarm History') ?></h6>
        </div>
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover mg-b-0'],
                'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
                'columns' => [
                    [
                        'attribute' => 'serial_number',
                        'label' => Yii::t('app', 'Meter'),
                        'format' => 'raw',
                        'value' => function($model) {
                            $serial = $model->serial_number ?? ($model->meter->serial_number ?? $model->dev_eui);
                            return Html::a($serial, ['history', 'id' => $model->meter_id], ['class' => 'tx-semibold']);
                        },
                    ],
                    [
                        'label' => Yii::t('app', 'Meter Type'),
                        'value' => fn($m) => $m->meter->meter_type ?? '-',
                    ],
                    [
                        'attribute' => 'alarm_type',
                        'label' => Yii::t('app', 'Alarm Type'),
                        'value' => fn($m) => $m->getAlarmTypeLabel(),
                    ],
                    [
                        'attribute' => 'severity',
                        'format' => 'raw',
                        'value' => fn($m) => '<span class="badge bg-' . $m->getSeverityBadgeClass() . '">' . $m->severity . '</span>',
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => fn($m) => '<span class="badge bg-' . $m->getStatusBadgeClass() . '">' . $m->status . '</span>',
                    ],
                    [
                        'attribute' => 'message',
                        'value' => fn($m) => \yii\helpers\StringHelper::truncate($m->message ?? '-', 50),
                    ],
                    [
                        'attribute' => 'originated_at',
                        'label' => Yii::t('app', 'Occurred'),
                        'format' => 'datetime',
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {acknowledge}',
                        'buttons' => [
                            'view' => fn($url, $m) => Html::a(
                                '<i data-feather="eye" class="wd-14 ht-14"></i>',
                                ['view', 'id' => $m->id],
                                ['class' => 'btn btn-xs btn-white', 'title' => Yii::t('app', 'View')]
                            ),
                            'acknowledge' => fn($url, $m) => $m->status === MeterAlarm::STATUS_ACTIVE
                                ? Html::a(
                                    '<i data-feather="check" class="wd-14 ht-14"></i>',
                                    ['acknowledge', 'id' => $m->id],
                                    [
                                        'class' => 'btn btn-xs btn-outline-success',
                                        'title' => Yii::t('app', 'Acknowledge'),
                                        'data-confirm' => Yii::t('app', 'Are you sure you want to acknowledge this alarm?'),
                                    ]
                                )
                                : '',
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
