<?php
use yii;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr>
    <td>#</td>
    <td><?= $model->displayTarget(); ?></td>
    <td><?= $model->type; ?></td>
    <td><?= $model->title; ?></td>
    <td><?= $model->description; ?></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['faqs/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a> | 
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to delete this FAQ?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>