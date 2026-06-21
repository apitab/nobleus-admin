<?php 
    use yii\helpers\Html;
    use backend\helpers\StatusCodes;
?>
<tr>
    <td><?= $model->id; ?></td>
    <td><?= $model->name; ?></td>
    <td><?= $model->location->name ?? ''; ?></td>
    <td><?= $model->location_desc; ?></td>
    <td><?= $model->location_coordinates; ?></td>
    <td><?= $model->operating_hours; ?></td>
    <td><?= $model->is_open ? 'Open' : 'Closed'; ?></td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td>
        <a href="<?= Yii::$app->urlManager->createUrl(['kiosks/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update') ?> </a>
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to delete this Kiosk?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>