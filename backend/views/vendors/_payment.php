<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
use backend\helpers\Helpers;
?>

<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xs mg-r-10">
                <span class="avatar-initial rounded-circle bg-primary">
                    <?= strtoupper(substr($model->request->customer->alias, 0, 1)) ?>
                </span>
            </div>
            <div class="mg-l-10">
                <?= Html::a(
                    Html::encode($model->request->customer->alias),
                    ['customers/view', 'id' => $model->request->customer_id],
                    ['class' => 'tx-13 tx-medium mb-0 text-decoration-none']
                ) ?>
                <small class="tx-12 tx-color-03 d-block"><?= $model->request->customer->phone_number ?></small>
                <small class="tx-12 tx-color-03 d-block"><?= $model->request->volume_requested . ' barrels' ?></small>
                <small class="tx-12 tx-color-02 d-block">Delivery date: <?= Date('d/m/Y', strtotime($model->request->delivery_date)) ?></small>
            </div>
        </div>
    </td>
    <td>
        <?= 'SlSh ' .  $model->amount ?>
    </td>
    <td><?= $model->paymentMethod->name ?></td>
    <td><?= Date('d/m/Y', strtotime($model->date_created)) ?></td>
    <td><?= StatusCodes::getPaymentStatusText($model->status) ?></td>
</tr>