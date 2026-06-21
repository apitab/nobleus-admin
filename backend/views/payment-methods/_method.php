<?php

use backend\helpers\StatusCodes;
?>
<tr>
    <td>#</td>
    <td><?= $model->name; ?></td>
    <td><?= $model->description; ?></td>
    <td><?= $model->method_metadata; ?></td>
    <td><?= StatusCodes::getStatusText($model->enabled); ?></td>
    <td class="tx-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['payment-methods/update','id'=>$model->id])?>"><i data-feather="edit" class="feather-small"></i> <?= Yii::t('app','Update')?></a>
    </td>
</tr>