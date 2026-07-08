<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterReadingRaw;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var common\models\billing\MeterReadingRaw $model */
$this->title = Yii::t('app', 'Reading') . ' #' . $model->id;
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => Html::encode($this->title),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Readings'), 'url' => \yii\helpers\Url::to(['index'])],
      ['title' => '#' . $model->id, 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card">
    <div class="card-body">
      <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-bordered'],
        'attributes' => [
          'id',
          'dev_eui',
          'supply_no',
          ['attribute' => 'reading_value', 'value' => number_format((float) $model->reading_value / 1000, 3) . ' m³'],
          'reading_time:datetime',
          'source',
          ['attribute' => 'status', 'value' => $model->statusLabel],
          'created_at:datetime',
          ['label' => Yii::t('app', 'Raw Payload'), 'value' => $model->payload],
        ],
      ]) ?>
      <?php if ($model->status == MeterReadingRaw::STATUS_PENDING): ?>
        <?= Html::beginForm(['import', 'id' => $model->id], 'post', ['style' => 'display:inline-block']) ?>
          <?= Html::submitButton(Yii::t('app', 'Post Reading'), [
            'class' => 'btn btn-success',
            'onclick' => 'return confirm(' . json_encode(Yii::t('app', 'Post this reading to billing?')) . ');',
          ]) ?>
        <?= Html::endForm() ?>
        <?= Html::beginForm(['reject', 'id' => $model->id], 'post', ['style' => 'display:inline-block']) ?>
          <?= Html::submitButton(Yii::t('app', 'Reject'), [
            'class' => 'btn btn-outline-danger',
            'onclick' => 'return confirm(' . json_encode(Yii::t('app', 'Reject this reading?')) . ');',
          ]) ?>
        <?= Html::endForm() ?>
      <?php endif; ?>
    </div>
  </div>
</div>
