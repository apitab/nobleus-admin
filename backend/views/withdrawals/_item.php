<?php
use yii\helpers\Url;
use backend\helpers\Helpers;
?>
<tr>
    <td><?= $model->id ?></td>
    <td><?= ucfirst($model->from_type) ?></td>
    <td>
        <?php if ($model->from_type == 'vendor'): ?>
            <?= $model->vendor ? $model->vendor->first_name . ' ' . $model->vendor->other_names : 'N/A' ?>
        <?php else: ?>
            <?= $model->customer ? $model->customer->alias : 'N/A' ?>
        <?php endif; ?>
    </td>
    <td><?= Helpers::localCurrencyFormatter($model->amount) ?></td>
    <td><?= date('d-m-Y H:i:s', strtotime($model->created_at)) ?></td>
    <td><?= $model->status ?></td>
    <td class="text-right">
        <a href="<?= Url::to(['withdrawals/view', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary">
            <i data-feather="eye"></i>
        </a>
    </td>
</tr>