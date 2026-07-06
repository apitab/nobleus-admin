<?php

use common\helpers\ViewHelper;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $supplyNo */
/** @var string $phone */
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
      <form method="get" action="<?= \yii\helpers\Url::to(['index']) ?>">
        <div class="row row-xs">
          <div class="col-md-4"><input name="supply_no" class="form-control" placeholder="<?= Yii::t('app', 'Supply No') ?>" value="<?= Html::encode($supplyNo) ?>"></div>
          <div class="col-md-4"><input name="phone" class="form-control" placeholder="<?= Yii::t('app', 'Phone') ?>" value="<?= Html::encode($phone) ?>"></div>
          <div class="col-md-2"><?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary btn-block']) ?></div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover mg-b-0'],
        'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
        'columns' => [
          [
            'label' => Yii::t('app', 'Customer Name'),
            'value' => fn($m) => $m->customer->alias ?? '-',
          ],
          [
            'label' => Yii::t('app', 'Phone'),
            'value' => fn($m) => $m->customer_phone ?: '-',
          ],
          [
            'label' => Yii::t('app', 'Meter ID'),
            'value' => fn($m) => $m->meter->serial_number ?? '-',
          ],
          [
            'label' => Yii::t('app', 'Supply No'),
            'value' => fn($m) => $m->supply_no,
          ],
        ],
      ]) ?>
    </div>
  </div>
</div>
