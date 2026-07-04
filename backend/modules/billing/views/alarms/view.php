<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterAlarm;
use yii\helpers\Html;

/** @var common\models\billing\MeterAlarm $alarm */

$this->title = Yii::t('app', 'Alarm Details') . ' #' . $alarm->id;
?>
<div class="content pd-t-20">
    <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
        'title' => '<i data-feather="alert-triangle" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Alarm Details'),
        'links' => [
            ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
            ['title' => Yii::t('app', 'Alarms'), 'url' => \yii\helpers\Url::to(['/billing/alarms/index'])],
            ['title' => '#' . $alarm->id, 'active' => true],
        ],
    ]); ?>
    <?= ViewHelper::displayFlash(); ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Alarm Details Card -->
            <div class="card mg-b-15">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mg-b-0"><?= Yii::t('app', 'Alarm Information') ?></h6>
                    <div>
                        <span class="badge bg-<?= $alarm->getSeverityBadgeClass() ?> mg-r-5"><?= $alarm->severity ?></span>
                        <span class="badge bg-<?= $alarm->getStatusBadgeClass() ?>"><?= $alarm->status ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="tx-medium" style="width: 140px;"><?= Yii::t('app', 'Alarm ID') ?></td>
                                    <td>#<?= $alarm->id ?></td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'Alarm Type') ?></td>
                                    <td><span class="tx-semibold"><?= $alarm->getAlarmTypeLabel() ?></span></td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'Severity') ?></td>
                                    <td><span class="badge bg-<?= $alarm->getSeverityBadgeClass() ?>"><?= $alarm->severity ?></span></td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'Status') ?></td>
                                    <td><span class="badge bg-<?= $alarm->getStatusBadgeClass() ?>"><?= $alarm->status ?></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="tx-medium" style="width: 140px;"><?= Yii::t('app', 'Originated At') ?></td>
                                    <td><?= Yii::$app->formatter->asDatetime($alarm->originated_at) ?></td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'Cleared At') ?></td>
                                    <td><?= $alarm->cleared_at ? Yii::$app->formatter->asDatetime($alarm->cleared_at) : '<span class="tx-color-03">Not cleared</span>' ?></td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'Acknowledged') ?></td>
                                    <td>
                                        <?php if ($alarm->acknowledged_at): ?>
                                            <?= Yii::$app->formatter->asDatetime($alarm->acknowledged_at) ?>
                                            <?php if ($alarm->acknowledgedByUser): ?>
                                                <br><small class="tx-color-03">by <?= Html::encode($alarm->acknowledgedByUser->names) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="tx-color-03">Not acknowledged</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tx-medium"><?= Yii::t('app', 'TB Alarm ID') ?></td>
                                    <td><code class="tx-11"><?= $alarm->tb_alarm_id ?? '-' ?></code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if ($alarm->message): ?>
                        <hr>
                        <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-10"><?= Yii::t('app', 'Message') ?></h6>
                        <p class="mg-b-0"><?= Html::encode($alarm->message) ?></p>
                    <?php endif; ?>

                    <?php if ($alarm->details): ?>
                        <hr>
                        <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-10"><?= Yii::t('app', 'Details') ?></h6>
                        <pre class="bg-gray-100 pd-15 rounded tx-12"><?= Html::encode($alarm->details) ?></pre>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?php if ($alarm->status === MeterAlarm::STATUS_ACTIVE): ?>
                        <?= Html::a(
                            '<i data-feather="check" class="wd-14 ht-14 mg-r-5"></i>' . Yii::t('app', 'Acknowledge Alarm'),
                            ['acknowledge', 'id' => $alarm->id],
                            [
                                'class' => 'btn btn-success',
                                'data-confirm' => Yii::t('app', 'Are you sure you want to acknowledge this alarm?'),
                            ]
                        ) ?>
                    <?php endif; ?>
                    <?= Html::a(Yii::t('app', 'Back to Alarms'), ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Meter Info Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mg-b-0"><?= Yii::t('app', 'Meter Information') ?></h6>
                </div>
                <div class="card-body">
                    <?php if ($alarm->meter): ?>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="tx-medium"><?= Yii::t('app', 'Serial') ?></td>
                                <td>
                                    <?= Html::a(
                                        $alarm->meter->serial_number,
                                        ['history', 'id' => $alarm->meter->id],
                                        ['class' => 'tx-semibold']
                                    ) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="tx-medium"><?= Yii::t('app', 'DevEUI') ?></td>
                                <td><code class="tx-11"><?= $alarm->meter->dev_eui ?></code></td>
                            </tr>
                            <tr>
                                <td class="tx-medium"><?= Yii::t('app', 'Type') ?></td>
                                <td><span class="badge bg-info"><?= $alarm->meter->meter_type ?></span></td>
                            </tr>
                            <tr>
                                <td class="tx-medium"><?= Yii::t('app', 'Supply No') ?></td>
                                <td><?= $alarm->meter->assignment->supply_no ?? '<span class="tx-color-03">Unassigned</span>' ?></td>
                            </tr>
                            <tr>
                                <td class="tx-medium"><?= Yii::t('app', 'Customer') ?></td>
                                <td><?= $alarm->meter->assignment->customer_phone ?? '-' ?></td>
                            </tr>
                        </table>
                        <hr>
                        <?= Html::a(
                            '<i data-feather="clock" class="wd-14 ht-14 mg-r-5"></i>' . Yii::t('app', 'View All Alarms for This Meter'),
                            ['history', 'id' => $alarm->meter->id],
                            ['class' => 'btn btn-sm btn-outline-primary btn-block']
                        ) ?>
                    <?php else: ?>
                        <p class="tx-color-03 mg-b-0"><?= Yii::t('app', 'Meter not found in system.') ?></p>
                        <p class="mg-b-0"><strong>DevEUI:</strong> <code><?= $alarm->dev_eui ?></code></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
