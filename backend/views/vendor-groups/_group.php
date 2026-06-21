<?php

use yii;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr>
    <td>#</td>
    <td><?= $model->name; ?></td>
    <td><?= $model->description; ?></td>
    <td><?= $model->price_of_water; ?></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td>
        <a href="<?= Yii::$app->urlManager->createUrl(['vendor-groups/update','id' => $model->id])?>"><i data-feather="edit" class="feather-small"></i> Update</a>
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to delete this vendor group?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>