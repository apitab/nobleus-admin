<?php 
use yii\helpers\Html;

?>
<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>
    <td>
        <?= $model->first_name . ', ' . $model->other_names; ?>
    </td>
    <td>
        <?= $model->mobile_number;?>
    </td>
    <td>
        <span class="badge bg-danger"><?= $model->status?></span>
    </td>
    <td>
    <div class="btn-group btn-group-sm">
            <?= Html::a(
                '<i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>',
                ['vendors/view-update-request', 'id' => $model->id],
                [
                    'class' => 'btn btn-white btn-sm',
                    'title' => Yii::t('app', 'View Request'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>
    </td>
</tr>