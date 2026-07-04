<?php

use common\helpers\ViewHelper;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var backend\modules\billing\models\FlowmeterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = Yii::t('app', 'Flowmeters');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="cpu" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Flowmeter Panel'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Flowmeters'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
      <div class="row row-xs">
        <div class="col-md-3"><?= $form->field($searchModel, 'serial_number')->textInput(['placeholder' => Yii::t('app', 'Serial Number')])->label(false) ?></div>
        <div class="col-md-3"><?= $form->field($searchModel, 'dev_eui')->textInput(['placeholder' => 'DevEUI'])->label(false) ?></div>
        <div class="col-md-3"><?= $form->field($searchModel, 'supply_no')->textInput(['placeholder' => Yii::t('app', 'Supply No')])->label(false) ?></div>
        <div class="col-md-3"><?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary btn-block']) ?></div>
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
          'serial_number',
          'dev_eui',
          'meter_type',
          [
            'label' => Yii::t('app', 'Supply No'),
            'value' => fn($m) => $m->assignment->supply_no ?? '-',
          ],
          [
            'label' => Yii::t('app', 'Customer Phone'),
            'value' => fn($m) => $m->assignment->customer_phone ?? '-',
          ],
          [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => fn($m) => '<span class="badge badge-' . ($m->status == 1 ? 'success' : 'secondary') . '">' . ($m->status == 1 ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive')) . '</span>',
          ],
          [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
              'view' => fn($url, $m) => Html::a(Yii::t('app', 'Details'), ['view', 'id' => $m->id], ['class' => 'btn btn-xs btn-white']),
            ],
          ],
        ],
      ]) ?>
    </div>
  </div>
</div>
