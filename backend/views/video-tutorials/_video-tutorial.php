<?php
use yii;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr>
    <td>#</td>
    <td><?= $model->displayTarget(); ?></td>
    <td><?= $model->name; ?></td>
    <td><?= $model->description; ?></td>
    <td><?= $model->video_name ? Html::a($model->video_name, Yii::getAlias('@web') . '/' . $model->video_name, ['target' => '_blank']) : '-'; ?></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['video-tutorials/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a> | 
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to delete this video tutorial?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>

