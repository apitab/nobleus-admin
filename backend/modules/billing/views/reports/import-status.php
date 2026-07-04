<?php

use common\helpers\ViewHelper;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $imported */
/** @var yii\data\ActiveDataProvider $pending */
/** @var string $from */
/** @var string $to */
$this->title = Yii::t('app', 'Posting Status Report');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="check-square" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Posting Status Report'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Posting Status'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <form method="get" class="form-inline">
        <label class="mg-r-10"><?= Yii::t('app', 'From') ?></label>
        <input type="date" name="date_from" value="<?= Html::encode($from) ?>" class="form-control mg-r-10">
        <label class="mg-r-10"><?= Yii::t('app', 'To') ?></label>
        <input type="date" name="date_to" value="<?= Html::encode($to) ?>" class="form-control mg-r-10">
        <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-primary']) ?>
      </form>
    </div>
  </div>

  <div class="row row-xs">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header"><h6 class="mg-b-0 text-success"><?= Yii::t('app', 'Posted') ?> (<?= $imported->getTotalCount() ?>)</h6></div>
        <div class="table-responsive">
          <?= GridView::widget([
            'dataProvider' => $imported,
            'tableOptions' => ['class' => 'table table-hover mg-b-0'],
            'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
            'columns' => [
              ['attribute' => 'supply_no', 'value' => fn($m) => $m->supply_no ?: '-'],
              'dev_eui',
              ['attribute' => 'reading_value', 'value' => fn($m) => number_format($m->reading_value, 3)],
              'reading_time:datetime',
              [
                'label' => Yii::t('app', 'Posted By'),
                'value' => fn($m) => $m->ledgerEntry->importedByUser->names ?? '-',
              ],
            ],
          ]) ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header"><h6 class="mg-b-0 text-warning"><?= Yii::t('app', 'Not Posted (Pending)') ?> (<?= $pending->getTotalCount() ?>)</h6></div>
        <div class="table-responsive">
          <?= GridView::widget([
            'dataProvider' => $pending,
            'tableOptions' => ['class' => 'table table-hover mg-b-0'],
            'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
            'columns' => [
              ['attribute' => 'supply_no', 'value' => fn($m) => $m->supply_no ?: '-'],
              'dev_eui',
              ['attribute' => 'reading_value', 'value' => fn($m) => number_format($m->reading_value, 3)],
              'reading_time:datetime',
              [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{import}',
                'buttons' => [
                  'import' => fn($url, $m) => Html::a(Yii::t('app', 'Post'), ['/billing/readings/import', 'id' => $m->id], [
                    'class' => 'btn btn-xs btn-success',
                    'data' => ['method' => 'post', 'confirm' => Yii::t('app', 'Post this reading to billing?')],
                  ]),
                ],
              ],
            ],
          ]) ?>
        </div>
      </div>
    </div>
  </div>
</div>
