<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\billing\models\ReadingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Meter Readings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="readings-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Reading', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Post to Billing (28th)', ['post-to-billing'], [
            'class' => 'btn btn-primary',
            'data' => [
                'confirm' => 'Post the readings for the 28th of this month to the billing server?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="readings-search">
        <?php $form = \yii\widgets\ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>

        <div class="row">
            <div class="col-md-4">
                <label class="control-label">Meter No</label>
                <div style="display:flex; gap:5px; align-items:center; margin-bottom:10px;">
                    <?= Html::activeInput('text', $searchModel, 'supply_part1', [
                        'class' => 'form-control', 
                        'placeholder' => 'Zone (J)', 
                        'style' => 'width:80px;'
                    ]) ?>
                    <span>-</span>
                    <?= Html::activeInput('text', $searchModel, 'supply_part2', [
                        'class' => 'form-control', 
                        'placeholder' => 'Area (1)', 
                        'style' => 'width:80px;'
                    ]) ?>
                    <span>-</span>
                    <?= Html::activeInput('text', $searchModel, 'supply_part3', [
                        'class' => 'form-control', 
                        'placeholder' => 'Customer (23)', 
                        'style' => 'width:80px;'
                    ]) ?>
                </div>
            </div>

            <div class="col-md-3">
                <label class="control-label">Date Range</label>
                <div style="display:flex; gap:5px; align-items:center; margin-bottom:10px;">
                    <?= Html::activeInput('date', $searchModel, 'date_from', [
                        'class' => 'form-control', 
                        'placeholder' => 'From'
                    ]) ?>
                    <span>to</span>
                    <?= Html::activeInput('date', $searchModel, 'date_to', [
                        'class' => 'form-control', 
                        'placeholder' => 'To'
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Meter No',
                'value' => function ($model) {
                    return $model->meter->assignment->supply_no
                        ?? ($model->supply_no ?: '-');
                },
            ],
            [
                'label' => 'Meter Type',
                'value' => function ($model) {
                    if (!$model->meter) return '-';
                    $t = strtolower((string) $model->meter->meter_type);
                    if ($t === 'ultrasonic') return $model->meter->frame_type ?: 'Ultrasonic';
                    if ($t === 'bmeter')     return $model->meter->diameter ?: 'B-Meter';
                    return $model->meter->meter_type;
                },
            ],
            'reading_time',
            [
                'label' => 'Reading (m³)',
                'format' => 'raw',
                'value' => function ($model) {
                    // Raw reading_value is in liters; m³ = liters / 1000
                    $m3 = (float) $model->reading_value / 1000;
                    $display = number_format($m3, 2) . ' m³';
                    // Zero readings are shown but never posted to billing
                    if ((int) floor($m3) === 0) {
                        return $display . ' <span class="label label-warning" style="background:#faa405;color:#fff;padding:1px 6px;border-radius:3px;font-size:11px;">0 — not posted</span>';
                    }
                    return $display;
                },
            ],
            [
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    $active = $model->meter && (int) $model->meter->status === 1;
                    return $active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                },
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
