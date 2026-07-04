<?php

use common\helpers\ViewHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
$this->title = Yii::t('app', 'Billing Dashboard');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="zap" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Billing Dashboard'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="row row-xs mg-b-25">
    <div class="col-sm-6 col-lg-3">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="activity" class="wd-30 ht-30 stroke-2 text-primary"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app', 'Meters Read Today') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($metersReadToday) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app', 'Unique meters reporting today') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="inbox" class="wd-30 ht-30 stroke-2 text-warning"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app', 'Pending Postings') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($pendingImports) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app', 'Readings awaiting clerk posting') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="check-circle" class="wd-30 ht-30 stroke-2 text-success"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app', 'Posted Today') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($importedToday) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app', 'Finalized ledger entries today') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="users" class="wd-30 ht-30 stroke-2 text-info"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app', 'Active Customers') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($activeCustomers) ?></h2>
        <small class="tx-11 tx-color-03"><?= number_format($totalMeters) . ' ' . Yii::t('app', 'meters deployed') ?></small>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6 class="mg-b-0"><?= Yii::t('app', 'Latest Incoming Readings') ?></h6>
      <?= Html::a(Yii::t('app', 'Open Reading Panel'), Url::to(['/billing/readings/index']), ['class' => 'btn btn-xs btn-primary']) ?>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mg-b-0">
        <thead>
          <tr>
            <th><?= Yii::t('app', 'DevEUI') ?></th>
            <th><?= Yii::t('app', 'Supply No') ?></th>
            <th><?= Yii::t('app', 'Reading') ?></th>
            <th><?= Yii::t('app', 'Time') ?></th>
            <th><?= Yii::t('app', 'Status') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentReadings as $reading): ?>
            <tr>
              <td><?= Html::encode($reading->dev_eui) ?></td>
              <td><?= Html::encode($reading->supply_no ?: '-') ?></td>
              <td><?= number_format($reading->reading_value, 3) ?></td>
              <td><?= Yii::$app->formatter->asDatetime($reading->reading_time) ?></td>
              <td><span class="badge badge-<?= $reading->status == 1 ? 'success' : ($reading->status == 2 ? 'danger' : 'warning') ?>"><?= $reading->statusLabel ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recentReadings)): ?>
            <tr><td colspan="5" class="text-center tx-color-03"><?= Yii::t('app', 'No readings received yet') ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
