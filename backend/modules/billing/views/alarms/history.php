<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterAlarm;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var common\models\billing\Meter $meter */
/** @var backend\modules\billing\models\MeterAlarmSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */

$this->title = Yii::t('app', 'Alarm History') . ' - ' . $meter->serial_number;
?>
<div class="content pd-t-20">
    <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
        'title' => '<i data-feather="clock" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Alarm History'),
        'links' => [
            ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
            ['title' => Yii::t('app', 'Alarms'), 'url' => \yii\helpers\Url::to(['/billing/alarms/index'])],
            ['title' => Yii::t('app', 'Meters'), 'url' => \yii\helpers\Url::to(['/billing/alarms/meters'])],
            ['title' => $meter->serial_number, 'active' => true],
        ],
    ]); ?>
    <?= ViewHelper::displayFlash(); ?>

    <!-- Meter Info Card -->
    <div class="card mg-b-15">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mg-b-15"><?= Yii::t('app', 'Meter Information') ?></h5>
                    <?php
                    // Get last two readings for consumption calculation
                    $readings = $meter->getReadings()->orderBy(['reading_time' => SORT_DESC])->limit(2)->all();
                    $lastReading = $readings[0] ?? null;
                    $prevReading = $readings[1] ?? null;
                    $consumption = ($lastReading && $prevReading) ? $lastReading->reading_value - $prevReading->reading_value : null;
                    ?>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="tx-medium" style="width: 150px;"><?= Yii::t('app', 'Serial Number') ?></td>
                            <td><span class="tx-semibold"><?= Html::encode($meter->serial_number) ?></span></td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Supply No') ?></td>
                            <td>
                                <?php if ($meter->assignment && $meter->assignment->supply_no): ?>
                                    <span class="badge bg-primary"><?= Html::encode($meter->assignment->supply_no) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unassigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Customer Phone') ?></td>
                            <td><?= $meter->assignment->customer_phone ?? '-' ?></td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Meter Type') ?></td>
                            <td><span class="badge bg-info"><?= Html::encode($meter->meter_type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'DevEUI') ?></td>
                            <td><code class="tx-10"><?= Html::encode($meter->dev_eui) ?></code></td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Last Reading') ?></td>
                            <td>
                                <?php if ($lastReading): ?>
                                    <span class="tx-semibold"><?= number_format($lastReading->reading_value, 2) ?> L</span>
                                    <small class="tx-color-03">(<?= Yii::$app->formatter->asRelativeTime($lastReading->reading_time) ?>)</small>
                                <?php else: ?>
                                    <span class="tx-color-03">No readings</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Previous Reading') ?></td>
                            <td>
                                <?php if ($prevReading): ?>
                                    <span><?= number_format($prevReading->reading_value, 2) ?> L</span>
                                    <small class="tx-color-03">(<?= Yii::$app->formatter->asRelativeTime($prevReading->reading_time) ?>)</small>
                                <?php else: ?>
                                    <span class="tx-color-03">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="tx-medium"><?= Yii::t('app', 'Consumption') ?></td>
                            <td>
                                <?php if ($consumption !== null): ?>
                                    <span class="tx-semibold <?= $consumption >= 0 ? 'tx-success' : 'tx-danger' ?>">
                                        <?= $consumption >= 0 ? '+' : '' ?><?= number_format($consumption, 2) ?> L
                                    </span>
                                <?php else: ?>
                                    <span class="tx-color-03">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="mg-b-15"><?= Yii::t('app', 'Alarm Statistics') ?></h5>
                    <div class="row">
                        <div class="col-4">
                            <div class="card card-body bg-gray-100 pd-15">
                                <h6 class="tx-uppercase tx-10 tx-spacing-1 tx-color-02 tx-semibold mg-b-5"><?= Yii::t('app', 'Total') ?></h6>
                                <h4 class="tx-normal tx-rubik mg-b-0"><?= $stats['total'] ?></h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-body bg-danger pd-15">
                                <h6 class="tx-uppercase tx-10 tx-spacing-1 tx-white-8 tx-semibold mg-b-5"><?= Yii::t('app', 'Active') ?></h6>
                                <h4 class="tx-normal tx-rubik mg-b-0 tx-white"><?= $stats['active'] ?></h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-body bg-success pd-15">
                                <h6 class="tx-uppercase tx-10 tx-spacing-1 tx-white-8 tx-semibold mg-b-5"><?= Yii::t('app', 'Cleared') ?></h6>
                                <h4 class="tx-normal tx-rubik mg-b-0 tx-white"><?= $stats['cleared'] ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alarm History Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mg-b-0"><?= Yii::t('app', 'Alarm History') ?></h6>
            <?= Html::a('<i data-feather="arrow-left" class="wd-14 ht-14 mg-r-5"></i>' . Yii::t('app', 'Back to Meters'), ['meters'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover mg-b-0'],
                'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
                'emptyText' => '<div class="pd-40 tx-center"><i data-feather="check-circle" class="wd-40 ht-40 tx-success mg-b-10"></i><p class="mg-b-0">' . Yii::t('app', 'No alarms recorded for this meter.') . '</p></div>',
                'columns' => [
                    [
                        'attribute' => 'alarm_type',
                        'label' => Yii::t('app', 'Alarm Type'),
                        'format' => 'raw',
                        'value' => fn($m) => '<i data-feather="alert-triangle" class="wd-14 ht-14 mg-r-5"></i>' . $m->getAlarmTypeLabel(),
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
                        'value' => fn($m) => $m->message ?? '-',
                    ],
                    [
                        'attribute' => 'originated_at',
                        'label' => Yii::t('app', 'Started'),
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'cleared_at',
                        'label' => Yii::t('app', 'Cleared'),
                        'format' => 'raw',
                        'value' => fn($m) => $m->cleared_at ? Yii::$app->formatter->asDatetime($m->cleared_at) : '<span class="tx-color-03">-</span>',
                    ],
                    [
                        'label' => Yii::t('app', 'Duration'),
                        'value' => function($m) {
                            $start = strtotime($m->originated_at);
                            $end = $m->cleared_at ? strtotime($m->cleared_at) : time();
                            $diff = $end - $start;
                            
                            if ($diff < 60) return $diff . 's';
                            if ($diff < 3600) return round($diff / 60) . 'm';
                            if ($diff < 86400) return round($diff / 3600, 1) . 'h';
                            return round($diff / 86400, 1) . 'd';
                        },
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
