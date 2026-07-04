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
                <div class="col-md-3">
                    <?= Html::textInput('serial_number', $filters['serial_number'], [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app', 'Serial Number'),
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::dropDownList('meter_type', $filters['meter_type'], 
                        array_combine($meterTypes, $meterTypes),
                        ['class' => 'form-control', 'prompt' => Yii::t('app', 'All Meter Types')]
                    ) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::dropDownList('has_alarms', $filters['has_alarms'], [
                        '' => Yii::t('app', 'All Meters'),
                        '1' => Yii::t('app', 'With Active Alarms Only'),
                    ], ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-3">
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
                    'id',
                    [
                        'attribute' => 'serial_number',
                        'format' => 'raw',
                        'value' => function($model) {
                            $badge = '';
                            $alarmCount = $model->getActiveAlarmCount();
                            if ($alarmCount > 0) {
                                $badgeClass = $model->hasCriticalAlarm() ? 'danger' : 'warning';
                                $badge = ' <span class="badge bg-' . $badgeClass . '">' . $alarmCount . '</span>';
                            }
                            return Html::a($model->serial_number, ['history', 'id' => $model->id], ['class' => 'tx-semibold']) . $badge;
                        },
                    ],
                    'dev_eui',
                    'meter_type',
                    [
                        'label' => Yii::t('app', 'Supply No'),
                        'value' => fn($m) => $m->assignment->supply_no ?? '<span class="tx-color-03">Unassigned</span>',
                        'format' => 'raw',
                    ],
                    [
                        'label' => Yii::t('app', 'Customer'),
                        'value' => fn($m) => $m->assignment->customer_phone ?? '-',
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => fn($m) => '<span class="badge bg-' . ($m->status == 1 ? 'success' : 'secondary') . '">' . ($m->status == 1 ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive')) . '</span>',
                    ],
                    [
                        'label' => Yii::t('app', 'Active Alarms'),
                        'format' => 'raw',
                        'value' => function($model) {
                            $count = $model->getActiveAlarmCount();
                            if ($count === 0) {
                                return '<span class="tx-success"><i data-feather="check-circle" class="wd-14 ht-14"></i> OK</span>';
                            }
                            $class = $model->hasCriticalAlarm() ? 'tx-danger' : 'tx-warning';
                            return '<span class="' . $class . '"><i data-feather="alert-triangle" class="wd-14 ht-14"></i> ' . $count . ' alarm(s)</span>';
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{history}',
                        'buttons' => [
                            'history' => fn($url, $m) => Html::a(
                                Yii::t('app', 'View History'),
                                ['history', 'id' => $m->id],
                                ['class' => 'btn btn-xs btn-white']
                            ),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
