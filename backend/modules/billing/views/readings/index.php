<?php

use common\helpers\ViewHelper;
use common\models\billing\MeterReadingRaw;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\modules\billing\models\ReadingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = Yii::t('app', 'Reading Panel');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="inbox" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Reading Panel'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Readings'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
      <div class="row row-xs">
        <div class="col-md-2"><?= $form->field($searchModel, 'supply_no')->textInput(['placeholder' => Yii::t('app', 'Supply No')])->label(false) ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'dev_eui')->textInput(['placeholder' => 'DevEUI'])->label(false) ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'status')->dropDownList(MeterReadingRaw::statusLabels(), ['prompt' => Yii::t('app', 'All statuses')])->label(false) ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'date_from')->input('date')->label(false) ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'date_to')->input('date')->label(false) ?></div>
        <div class="col-md-2"><?= Html::submitButton(Yii::t('app', 'Filter'), ['class' => 'btn btn-primary btn-block']) ?></div>
      </div>
      <?php ActiveForm::end(); ?>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover mg-b-0'],
        'layout' => "{items}\n<div class='card-footer'>{summary}{pager}</div>",
        'columns' => [
          'id',
          'dev_eui',
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
            'format' => 'raw',
            'value' => fn($m) => '<span class="badge badge-' . ($m->status == 1 ? 'success' : ($m->status == 2 ? 'danger' : 'warning')) . '">' . $m->statusLabel . '</span>',
          ],
          [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {import} {reject}',
            'buttons' => [
              'view' => fn($url, $m) => Html::a(Yii::t('app', 'View'), ['view', 'id' => $m->id], ['class' => 'btn btn-xs btn-white']),
              'import' => fn($url, $m) => $m->status == MeterReadingRaw::STATUS_PENDING
                ? Html::a(Yii::t('app', 'Post'), ['import', 'id' => $m->id], [
                    'class' => 'btn btn-xs btn-success',
                    'data' => ['method' => 'post', 'confirm' => Yii::t('app', 'Post this reading to billing?')],
                  ])
                : '',
              'reject' => fn($url, $m) => $m->status == MeterReadingRaw::STATUS_PENDING
                ? Html::a(Yii::t('app', 'Reject'), ['reject', 'id' => $m->id], [
                    'class' => 'btn btn-xs btn-outline-danger',
                    'data' => ['method' => 'post', 'confirm' => Yii::t('app', 'Reject this reading?')],
                  ])
                : '',
            ],
          ],
        ],
      ]) ?>
    </div>
  </div>
</div>
