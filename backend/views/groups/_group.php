<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr class="<?= $model->status == StatusCodes::DELETE_STATUS ? "bg-warning" : ""?>">
    <td><?= $model->id; ?></td>
    <td><?= $model->name; ?></td>
    <td><?= $model->description; ?></td>
    <td><a href="<?= Yii::$app->urlManager->createUrl(['permissions/index','group_id'=>$model->id])?>"><i data-feather="lock" class="feather-small"></i> <?= Yii::t('app','Group Permissions') ?></a></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['groups/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a> | 
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Deactivate'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>