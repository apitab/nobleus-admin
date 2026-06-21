<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>

<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>
    
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xs mg-r-10">
                <?php if ($model->display_pic): ?>
                    <img src="<?= $model->display_pic ?>" class="rounded-circle" alt="">
                <?php else: ?>
                    <span class="avatar-initial rounded-circle bg-primary">
                        <?= strtoupper(substr($model->first_name, 0, 1)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="mg-l-10">
                <h6 class="tx-13 mg-b-0"><?= Html::encode($model->first_name . ' ' . $model->other_names) ?></h6>
                <small class="tx-12 tx-color-03"><?= Html::encode($model->vendorGroup->name) ?></small>
            </div>
        </div>
    </td>
    
    <td class="align-middle">
        <span class="tx-13"><?= Html::encode($model->mobile_number) ?></span>
    </td>

    <td class="align-middle">
        <span class="tx-13"><?= Html::encode($model->vendor_type) ?></span>
    </td>
    
    <td class="align-middle">
        <span class="tx-13"><?= Html::encode($model->tank_volume) ?> L</span>
    </td>
    
    <td class="align-middle">
        <?php
        if ($model->status == StatusCodes::ACTIVE_STATUS) {
            $statusClass = 'bg-success';
            $statusText = Yii::t('app', 'Active');
        } elseif ($model->status == 10) {
            $statusClass = 'bg-warning';
            $statusText = Yii::t('app', 'Pending');
        } else {
            $statusClass = 'bg-danger';
            $statusText = Yii::t('app', 'Inactive');
        }
        ?>
        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
    </td>
    
    <td class="align-middle text-nowrap">
        <div class="btn-group btn-group-sm">
            <?= Html::a(
                '<i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>',
                ['view', 'id' => $model->id],
                [
                    'class' => 'btn btn-white btn-sm',
                    'title' => Yii::t('app', 'View Vendor'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>
            
            <?= Html::a(
                '<i data-feather="edit" class="wd-15 ht-15 stroke-2"></i>',
                ['update', 'id' => $model->id],
                [
                    'class' => 'btn btn-white btn-sm',
                    'title' => Yii::t('app', 'Update Vendor'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>

            <?php if ($model->status == StatusCodes::ACTIVE_STATUS): ?>
                <?= Html::a(
                    '<i data-feather="message-square" class="wd-15 ht-15 stroke-2"></i>',
                    ['message', 'id' => $model->id],
                    [
                        'class' => 'btn btn-white btn-sm',
                        'title' => Yii::t('app', 'Message Vendor'),
                        'data-toggle' => 'tooltip'
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </td>
</tr>