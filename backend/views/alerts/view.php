<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Alerts $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="alerts-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'level',
                'format' => 'raw',
                'value' => function($model) {
                    $levelBadgeClass = 'bg-secondary';
                    switch ($model->level) {
                        case \backend\models\Alerts::LEVEL_CRITICAL:
                            $levelBadgeClass = 'bg-danger';
                            break;
                        case \backend\models\Alerts::LEVEL_INTERRUPTION:
                            $levelBadgeClass = 'bg-warning';
                            break;
                        case \backend\models\Alerts::LEVEL_DELAY:
                            $levelBadgeClass = 'bg-info';
                            break;
                        case \backend\models\Alerts::LEVEL_REMINDER:
                            $levelBadgeClass = 'bg-primary';
                            break;
                        case \backend\models\Alerts::LEVEL_GENERAL:
                            $levelBadgeClass = 'bg-secondary';
                            break;
                    }
                    return '<span class="badge ' . $levelBadgeClass . '">' . $model->displayLevel() . '</span>';
                },
            ],
            [
                'attribute' => 'type',
                'value' => $model->displayType(),
            ],
            'title',
            'description:ntext',
            'expiry_date',
            [
                'attribute' => 'attachment',
                'format' => 'raw',
                'value' => $model->attachment 
                    ? Html::a('<i data-feather="file" class="wd-10 ht-10"></i> ' . Yii::t('app', 'View PDF'), Yii::getAlias('@web') . '/' . $model->attachment, [
                        'class' => 'btn btn-sm btn-outline-primary',
                        'target' => '_blank',
                    ])
                    : '<span class="text-muted">' . Yii::t('app', 'No attachment') . '</span>',
            ],
            'status',
            'date_created',
        ],
    ]) ?>

</div>
