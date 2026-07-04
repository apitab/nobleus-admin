<?php

use common\helpers\ViewHelper;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $date */
$this->title = Yii::t('app', 'General Reading Report');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="file-text" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'General Reading Report'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'General Report'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <form method="get" class="form-inline">
        <label class="mg-r-10"><?= Yii::t('app', 'Report Date') ?></label>
        <input type="date" name="date" value="<?= Html::encode($date) ?>" class="form-control mg-r-10">
        <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-primary']) ?>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mg-b-0"><?= Yii::t('app', 'Readings for') ?> <?= Yii::$app->formatter->asDate($date) ?></h6></div>
    <div class="table-responsive">
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover mg-b-0'],
        'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
        'columns' => [
          'dev_eui',
          [
            'label' => Yii::t('app', 'Serial Number'),
            'value' => fn($m) => $m->meter->serial_number ?? '-',
          ],
          [
            'attribute' => 'supply_no',
            'value' => fn($m) => $m->supply_no ?: '-',
          ],
          [
            'attribute' => 'reading_value',
            'value' => fn($m) => number_format($m->reading_value, 3),
          ],
          'reading_time:datetime',
          [
            'attribute' => 'status',
            'value' => fn($m) => $m->statusLabel,
          ],
        ],
      ]) ?>
    </div>
  </div>
</div>
