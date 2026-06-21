<?php

use backend\helpers\StatusCodes;
?>
<tr style="<?= $updated == $model->id ? 'background-color: #cee7d9' : ''?>">
    <td>#</td>
    <td><?= json_encode($model->vendor); ?></td>
    <td><?= $model->customerAddress->address; ?></td>
    <td><?= $model->volume_requested; ?></td>
    <td><?= $model->total_amount; ?></td>
    <td><?= StatusCodes::getRequestStatusText($model->status)?></td>
    <?php if($model->payment_id != null): ?>
        <td><?= $model->payment->paymentMethod->name; ?></td>
        <td><?= $model->payment->status; ?></td>
    <?php else: ?>
        <td>Not Found</td>
        <td>Not Found</td>
     <?php endif; ?> 
     <td><?= $model->date_created; ?></td>
     <td> 
        <a href="<?= Yii::$app->urlManager->createUrl(['customer-requests/update', 'cid' => $model->customer->id, 'id' => $model->id]); ?>"><i data-feather="edit" class="feather-small"></i> Update</a>
     </td>
</tr>