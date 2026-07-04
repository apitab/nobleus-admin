<?php

use common\helpers\ViewHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var backend\modules\billing\models\CustomerAnalysisForm $model */
/** @var common\models\billing\MeterAssignment|null $assignment */
/** @var common\models\billing\BillingLedger[] $history */
/** @var common\models\billing\BillingLedger|null $latest */
$this->title = Yii::t('app', 'Customer Analysis');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="user" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Customer Analysis'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Customer Analysis'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
      <div class="row row-xs">
        <div class="col-md-4"><?= $form->field($model, 'supply_no')->textInput(['placeholder' => Yii::t('app', 'Enter Supply Number')]) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'date_from')->input('date') ?></div>
        <div class="col-md-3"><?= $form->field($model, 'date_to')->input('date') ?></div>
        <div class="col-md-2 d-flex align-items-end"><div class="form-group w-100"><?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary btn-block']) ?></div></div>
      </div>
      <?php ActiveForm::end(); ?>
    </div>
  </div>

  <?php if ($assignment): ?>
    <div class="row row-xs mg-b-15">
      <div class="col-md-4">
        <div class="card card-body">
          <h6 class="mg-b-15"><?= Yii::t('app', 'Customer Profile') ?></h6>
          <p class="mg-b-5"><strong><?= Yii::t('app', 'Name') ?>:</strong> <?= Html::encode($assignment->customer->alias ?? '-') ?></p>
          <p class="mg-b-5"><strong><?= Yii::t('app', 'Phone') ?>:</strong> <?= Html::encode($assignment->customer_phone ?: '-') ?></p>
          <p class="mg-b-5"><strong><?= Yii::t('app', 'Supply No') ?>:</strong> <?= Html::encode($assignment->supply_no) ?></p>
          <p class="mg-b-0"><strong><?= Yii::t('app', 'Meter (DevEUI)') ?>:</strong> <?= Html::encode($assignment->meter->dev_eui ?? '-') ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-body">
          <h6 class="mg-b-15"><?= Yii::t('app', 'Latest Reading') ?></h6>
          <h2 class="tx-normal mg-b-2"><?= $latest ? number_format($latest->current_reading, 3) : '-' ?></h2>
          <small class="tx-11 tx-color-03"><?= $latest ? Yii::$app->formatter->asDate($latest->reading_date) : Yii::t('app', 'No ledger entries yet') ?></small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-body">
          <h6 class="mg-b-15"><?= Yii::t('app', 'Previous Reading') ?></h6>
          <h2 class="tx-normal mg-b-2"><?= $latest && $latest->previous_reading !== null ? number_format($latest->previous_reading, 3) : '-' ?></h2>
          <small class="tx-11 tx-color-03"><?= Yii::t('app', 'Consumption') ?>: <?= $latest && $latest->consumption !== null ? number_format($latest->consumption, 3) : '-' ?></small>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h6 class="mg-b-0"><?= Yii::t('app', 'Historical Usage') ?></h6></div>
      <div class="table-responsive">
        <table class="table table-hover mg-b-0">
          <thead>
            <tr>
              <th><?= Yii::t('app', 'Date') ?></th>
              <th><?= Yii::t('app', 'Previous') ?></th>
              <th><?= Yii::t('app', 'Current') ?></th>
              <th><?= Yii::t('app', 'Consumption') ?></th>
              <th><?= Yii::t('app', 'Posted By') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $entry): ?>
              <tr>
                <td><?= Yii::$app->formatter->asDate($entry->reading_date) ?></td>
                <td><?= $entry->previous_reading !== null ? number_format($entry->previous_reading, 3) : '-' ?></td>
                <td><?= number_format($entry->current_reading, 3) ?></td>
                <td><?= $entry->consumption !== null ? number_format($entry->consumption, 3) : '-' ?></td>
                <td><?= Html::encode($entry->importedByUser->names ?? ('#' . $entry->imported_by)) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
              <tr><td colspan="5" class="text-center tx-color-03"><?= Yii::t('app', 'No history for the selected period') ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
