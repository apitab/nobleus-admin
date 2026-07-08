<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterAlarm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $meterTypes */
/** @var array $filters */

$this->title = Yii::t('app', 'Meters Status');
?>
<div class="content pd-t-20">
    <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
        'title' => '<i data-feather="cpu" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Meters Status'),
        'links' => [
            ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
            ['title' => Yii::t('app', 'Alarms'), 'url' => \yii\helpers\Url::to(['/billing/alarms/index'])],
            ['title' => Yii::t('app', 'Meters'), 'active' => true],
        ],
    ]); ?>
    <?= ViewHelper::displayFlash(); ?>

    <!-- Quick Links -->
    <div class="card mg-b-15">
        <div class="card-body pd-y-10">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= Html::a('<i data-feather="alert-triangle" class="wd-16 ht-16 mg-r-5"></i>' . Yii::t('app', 'All Alarms'), ['index'], ['class' => 'btn btn-sm btn-outline-primary mg-r-5']) ?>
                    <?= Html::a('<i data-feather="cpu" class="wd-16 ht-16 mg-r-5"></i>' . Yii::t('app', 'Meters Status'), ['meters'], ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card mg-b-15">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['meters']]); ?>
            <div class="row row-xs">
                <div class="col-md-2">
                    <?= Html::textInput('serial_number', $filters['serial_number'], [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app', 'Serial Number'),
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::textInput('supply_no', $filters['supply_no'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app', 'Supply No'),
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::dropDownList('meter_type', $filters['meter_type'], 
                        array_combine($meterTypes, $meterTypes),
                        ['class' => 'form-control', 'prompt' => Yii::t('app', 'All Meter Types')]
                    ) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::dropDownList('has_alarms', $filters['has_alarms'], [
                        '' => Yii::t('app', 'All Meters'),
                        '1' => Yii::t('app', 'With Active Alarms Only'),
                    ], ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::dropDownList('assignment_status', $filters['assignment_status'] ?? '', [
                        '' => Yii::t('app', 'All Assignment'),
                        'assigned' => Yii::t('app', 'Assigned Only'),
                        'unassigned' => Yii::t('app', 'Unassigned Only'),
                    ], ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary btn-block']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Meters Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mg-b-0"><?= Yii::t('app', 'All Meters') ?></h6>
            <span class="badge bg-secondary"><?= $dataProvider->totalCount ?> <?= Yii::t('app', 'meters') ?></span>
        </div>
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover mg-b-0'],
                'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
                'columns' => [
                    [
                        'attribute' => 'serial_number',
                        'label' => Yii::t('app', 'Serial Number'),
                        'format' => 'raw',
                        'value' => fn($m) => Html::a($m->serial_number, ['history', 'id' => $m->id], ['class' => 'tx-semibold']),
                    ],
                    [
                        'label' => Yii::t('app', 'Supply No'),
                        'format' => 'raw',
                        'value' => function($m) {
                            if ($m->assignment && $m->assignment->supply_no) {
                                return '<span class="tx-semibold">' . Html::encode($m->assignment->supply_no) . '</span>';
                            }
                            return '<span class="badge bg-secondary">Unassigned</span>';
                        },
                    ],
                    [
                        'attribute' => 'meter_type',
                        'label' => Yii::t('app', 'Type'),
                        'format' => 'raw',
                        'value' => fn($m) => '<span class="badge bg-info">' . Html::encode($m->meter_type) . '</span>',
                    ],
                    [
                        'label' => Yii::t('app', 'Customer Phone'),
                        'value' => fn($m) => $m->assignment->customer_phone ?? '-',
                    ],
                    [
                        'label' => Yii::t('app', 'Last Reading'),
                        'format' => 'raw',
                        'value' => function($m) {
                            $lastReading = $m->getReadings()->orderBy(['reading_time' => SORT_DESC])->one();
                            if ($lastReading) {
                                return number_format((float) $lastReading->reading_value / 1000, 3) . ' m³<br><small class="tx-color-03">' . Yii::$app->formatter->asRelativeTime($lastReading->reading_time) . '</small>';
                            }
                            return '<span class="tx-color-03">No readings</span>';
                        },
                    ],
                    [
                        'label' => Yii::t('app', 'Last Alarm Status'),
                        'format' => 'raw',
                        'value' => function($model) {
                            // Get the LAST alarm (most recent) for this meter
                            $lastAlarm = MeterAlarm::find()
                                ->where(['meter_id' => $model->id])
                                ->orderBy(['originated_at' => SORT_DESC])
                                ->one();
                            
                            if (!$lastAlarm) {
                                return '<span class="tx-success"><i data-feather="check-circle" class="wd-14 ht-14"></i> No Alarms</span>';
                            }
                            
                            $badgeClass = $lastAlarm->getStatusBadgeClass();
                            $severityClass = $lastAlarm->getSeverityBadgeClass();
                            
                            return '<span class="badge bg-' . $severityClass . ' mg-r-5">' . $lastAlarm->severity . '</span>' .
                                   '<span class="badge bg-' . $badgeClass . '">' . $lastAlarm->status . '</span>' .
                                   '<br><small class="tx-color-03">' . $lastAlarm->getAlarmTypeLabel() . '</small>';
                        },
                    ],
                    [
                        'label' => Yii::t('app', 'Total Alarms'),
                        'format' => 'raw',
                        'value' => function($model) {
                            $total = MeterAlarm::find()->where(['meter_id' => $model->id])->count();
                            $active = MeterAlarm::find()->where(['meter_id' => $model->id, 'status' => MeterAlarm::STATUS_ACTIVE])->count();
                            
                            if ($total == 0) {
                                return '<span class="tx-color-03">0</span>';
                            }
                            
                            $html = '<span class="tx-semibold">' . $total . '</span>';
                            if ($active > 0) {
                                $html .= ' <span class="badge bg-danger">' . $active . ' active</span>';
                            }
                            return $html;
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{history}',
                        'buttons' => [
                            'history' => fn($url, $m) => Html::a(
                                '<i data-feather="clock" class="wd-14 ht-14 mg-r-5"></i>' . Yii::t('app', 'History'),
                                ['history', 'id' => $m->id],
                                ['class' => 'btn btn-xs btn-outline-primary']
                            ),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
