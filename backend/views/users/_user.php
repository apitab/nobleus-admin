<?php

use yii;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr class="<?= $model->status == StatusCodes::DELETE_STATUS ? "bg-warning" : ""?>">
    <td><?= $model->id; ?></td>
    <td><?= $model->phone_number; ?></td>
    <td><?= $model->email_address; ?></td>
    <td><?= $model->group->name; ?></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['users/reset-password','id'=>$model->id])?>"><i data-feather="alert-triangle" class="feather-small"></i> <?= Yii::t('app','Reset Password') ?></a> |
        <a href="<?= Yii::$app->urlManager->createUrl(['users/update','user_id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a> | 
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Deactivate'), ['delete', 'user_id' => $model->user_id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to de-activate this user?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>