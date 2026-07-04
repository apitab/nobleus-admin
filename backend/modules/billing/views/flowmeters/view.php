<?php

use common\helpers\ViewHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var common\models\billing\Meter $model */
$this->title = Yii::t('app', 'Flowmeter') . ' ' . $model->serial_number;
$assignment = $model->assignment;
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => Html::encode($this->title),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Flowmeters'), 'url' => \yii\helpers\Url::to(['index'])],
      ['title' => Html::encode($model->serial_number), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card">
    <div class="card-body">
      <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-bordered'],
        'attributes' => [
          ['label' => Yii::t('app', 'Customer Name'), 'value' => $assignment->customer->alias ?? '-'],
          ['label' => Yii::t('app', 'Phone Number'), 'value' => $assignment->customer_phone ?? '-'],
          ['label' => Yii::t('app', 'Supply ID'), 'value' => $assignment->supply_no ?? Yii::t('app', 'Not assigned')],
          ['label' => Yii::t('app', 'Flowmeter Number (DevEUI)'), 'value' => $model->dev_eui],
          ['label' => Yii::t('app', 'Serial Number'), 'value' => $model->serial_number],
          'meter_type',
          ['attribute' => 'status', 'value' => $model->status == 1 ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive')],
          'installed_at:datetime',
        ],
      ]) ?>
    </div>
  </div>
</div>
