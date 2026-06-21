<?php
use yii;
use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>
<tr>
    <td>#</td>
    <td><?= $model->title; ?></td>
    <td><?= $model->description; ?></td>
    <td>
        <?php
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
        ?>
        <span class="badge <?= $levelBadgeClass ?>"><?= $model->displayLevel(); ?></span>
    </td>
    <td><?= $model->displayType(); ?></td>
    <td><?= $model->expiry_date; ?></td>
    <td class="tx-center">
        <?php if ($model->attachment): ?>
            <a href="<?= Yii::getAlias('@web') . '/' . $model->attachment ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= Yii::t('app', 'View PDF') ?>">
                <i data-feather="file" class="feather-small"></i> <?= Yii::t('app', 'PDF') ?>
            </a>
        <?php else: ?>
            <span class="text-muted"><?= Yii::t('app', 'No attachment') ?></span>
        <?php endif; ?>
    </td>
    <td><?= StatusCodes::getStatusText($model->status); ?></td>
    <td class="tx-center">
        <?= Html::a('<i data-feather="send" class="feather-small"></i> ' . Yii::t('app','Resend'), ['resend-alerts', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-primary',
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to resend this alert to all active customers?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['alerts/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a> | 
        <?= Html::a('<i data-feather="delete" class="feather-small"></i> ' . Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'data' => [
                'confirm' => Yii::t('app','Are you sure you want to delete this alert?'),
                'method' => 'post',
            ],
        ]) ?>
    </td>
</tr>

