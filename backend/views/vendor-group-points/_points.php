<?php

use yii;
use yii\helpers\Html;
?>
<tr>
    <td>#<?= $model->id; ?></td>
    <td><?= $model->group->name; ?></td>
    <td><?= $model->min_distance; ?></td>
    <td><?= $model->max_distance; ?></td>
    <td><?= $model->points_per_km; ?></td>
    <td>
        <a href="<?= Yii::$app->urlManager->createUrl(['vendor-group-points/update', 'id' => $model->id]) ?>"><i
                data-feather="edit" class="feather-small"></i> Update</a>
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this reward points?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>