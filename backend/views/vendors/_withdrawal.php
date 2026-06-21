<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
use backend\models\Withdrawals;
?>

<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <?php if ($model->from_type === Withdrawals::FROM_TYPE_VENDOR && $model->vendor): ?>
                <div class="avatar avatar-xs mg-r-10">
                    <span class="avatar-initial rounded-circle bg-primary">
                        <?= strtoupper(substr($model->vendor->first_name, 0, 1)) ?>
                    </span>
                </div>
                <div class="mg-l-10">
                    <?= Html::a(
                        Html::encode(trim($model->vendor->first_name . ' ' . $model->vendor->other_names)),
                        ['vendors/view', 'id' => $model->vendor->id],
                        ['class' => 'tx-13 tx-medium mb-0 text-decoration-none']
                    ) ?>
                    <small class="tx-12 tx-color-03 d-block"><?= $model->vendor->mobile_number ?></small>
                    <small class="tx-12 tx-color-02 d-block"><?= Yii::t('app', 'Vendor Withdrawal') ?></small>
                </div>
            <?php elseif ($model->from_type === Withdrawals::FROM_TYPE_CUSTOMER && $model->customer): ?>
                <div class="avatar avatar-xs mg-r-10">
                    <span class="avatar-initial rounded-circle bg-primary">
                        <?= strtoupper(substr($model->customer->alias, 0, 1)) ?>
                    </span>
                </div>
                <div class="mg-l-10">
                    <?= Html::a(
                        Html::encode($model->customer->alias),
                        ['customers/view', 'id' => $model->customer->id],
                        ['class' => 'tx-13 tx-medium mb-0 text-decoration-none']
                    ) ?>
                    <small class="tx-12 tx-color-03 d-block"><?= $model->customer->phone_number ?></small>
                    <small class="tx-12 tx-color-02 d-block"><?= Yii::t('app', 'Customer Withdrawal') ?></small>
                </div>
            <?php else: ?>
                <div class="mg-l-10">
                    <span class="tx-13 tx-medium mb-0"><?= Yii::t('app', 'Unknown') ?></span>
                    <small class="tx-12 tx-color-03 d-block"><?= Yii::t('app', 'ID: {id}', ['id' => $model->from_id]) ?></small>
                </div>
            <?php endif; ?>
        </div>
    </td>
    <td>
        <?= 'SlSh ' . number_format($model->amount, 2) ?>
    </td>
    <td><?= Yii::t('app', 'Withdrawal') ?></td>
    <td><?= $model->created_at ? Date('d/m/Y', strtotime($model->created_at)) : '-' ?></td>
    <td>
        <?php
        $statusClass = 'bg-secondary';
        $statusText = $model->displayStatus();
        
        switch ($model->status) {
            case Withdrawals::STATUS_SUCCESS:
                $statusClass = 'bg-success';
                break;
            case Withdrawals::STATUS_PENDING:
                $statusClass = 'bg-warning';
                break;
            case Withdrawals::STATUS_PROCESSING:
                $statusClass = 'bg-info';
                break;
            case Withdrawals::STATUS_FAILED:
                $statusClass = 'bg-danger';
                break;
            case Withdrawals::STATUS_CANCELLED:
                $statusClass = 'bg-secondary';
                break;
        }
        ?>
        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
    </td>
</tr>

