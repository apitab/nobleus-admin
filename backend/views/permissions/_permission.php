<?php

use yii\helpers\Html;
?>
<tr>
    <td>#</td>
    <td><?= $model->group->name; ?></td>
    <td><?= $model->module->description; ?></td>
    <td><?= $model->action->description; ?></td>
    <td>    
    <?= Html::a('<i class="feather-small" data-feather="delete"></i> Delete', ['delete', 'id' => $model->id], [
        'data' => [
            'confirm' => 'Are you sure you want to delete this item?',
            'method' => 'post',
        ],
    ]) ?>
    </td>

</tr>