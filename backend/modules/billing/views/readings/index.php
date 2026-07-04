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
            
            <div class="col-md-2">
                <?= $form->field($searchModel, 'status')->dropDownList([
                    '' => 'All Status',
                    '0' => 'Inactive',
                    '1' => 'Active',
                ]) ?>
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
                'attribute' => 'supply_no',
                'label' => 'Meter No',
                'filter' => false,
            ],
            [
                'attribute' => 'meter_id',
                'label' => 'Meter ID',
                'value' => function ($model) {
                    return $model->meter ? $model->meter->id : null;
                },
                'filter' => false,
            ],
            [
                'label' => 'Meter Type',
                'value' => function ($model) {
                    return $model->meter ? $model->meter->meter_type : null;
                },
            ],
            'reading_time',
            'reading_value',
            'status',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
