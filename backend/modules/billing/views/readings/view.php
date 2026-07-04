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
          ['attribute' => 'reading_value', 'value' => number_format($model->reading_value, 3)],
          'reading_time:datetime',
          'source',
          ['attribute' => 'status', 'value' => $model->statusLabel],
          'created_at:datetime',
          ['label' => Yii::t('app', 'Raw Payload'), 'value' => $model->payload],
        ],
      ]) ?>
      <?php if ($model->status == MeterReadingRaw::STATUS_PENDING): ?>
        <?= Html::a(Yii::t('app', 'Post Reading'), ['import', 'id' => $model->id], [
          'class' => 'btn btn-success',
          'data' => ['method' => 'post', 'confirm' => Yii::t('app', 'Post this reading to billing?')],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Reject'), ['reject', 'id' => $model->id], [
          'class' => 'btn btn-outline-danger',
          'data' => ['method' => 'post', 'confirm' => Yii::t('app', 'Reject this reading?')],
        ]) ?>
      <?php endif; ?>
    </div>
  </div>
</div>
