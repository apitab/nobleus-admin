<?php

use backend\helpers\Helpers;
use yii\helpers\Html;
use yii\helpers\Url;
    use backend\helpers\StatusCodes;

/** @var \backend\models\Payments $model */
?>
<tr class="align-middle">
    <td class="tx-12">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xs mg-r-10">
                <?php if ($model->status === StatusCodes::ACTIVE_STATUS): ?>
                    <div class="avatar-initial rounded-circle bg-success">
                        <i data-feather="check" class="wd-12 ht-12 stroke-2"></i>
                    </div>
                <?php else: ?>
                    <div class="avatar-initial rounded-circle bg-danger">
                        <i data-feather="x" class="wd-12 ht-12 stroke-2"></i>
                    </div>
                <?php endif; ?>
            </div>
            <span class="tx-medium">#<?= str_pad($model->id, 6, '0', STR_PAD_LEFT) ?></span>
        </div>
    </td>
    
    <td>
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm mg-r-10">
                <div class="avatar-initial rounded-circle bg-primary">
                    <?= strtoupper(substr($model->request->customer->alias, 0, 1)) ?>
                </div>
            </div>
            <div>
                <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">
                    <?= Html::a(
                        Html::encode($model->request->customer->alias),
                        ['customers/view', 'id' => $model->request->customer->id],
                        ['class' => 'tx-inherit']
                    ) ?>
                </h6>
                <span class="tx-12 tx-color-03">
                    <i data-feather="phone" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                    <?= $model->request->customer->phone_number ?>
                </span>
            </div>
        </div>
    </td>
    
    <td>
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm mg-r-10">
                <div class="avatar-initial rounded-circle bg-secondary">
                    <?= strtoupper(substr($model->request->vendor->first_name, 0, 1)) ?>
                </div>
            </div>
            <div>
                <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">
                    <?= Html::a(
                        Html::encode($model->request->vendor->other_names . ', ' . $model->request->vendor->first_name),
                        ['vendors/view', 'id' => $model->request->vendor->id],
                        ['class' => 'tx-inherit']
                    ) ?>
                </h6>
                <span class="tx-12 tx-color-03">
                    <i data-feather="phone" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                    <?= $model->request->vendor->mobile_number ?>
                </span>
            </div>
        </div>
    </td>
    
    <td>
        <div class="d-flex flex-column">
            <h6 class="tx-13 tx-inverse tx-semibold mg-b-0">
                <?= Helpers::localCurrencyFormatter($model->amount); ?>
            </h6>
            <div class="d-flex align-items-center tx-12">
                <i data-feather="credit-card" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <span class="tx-color-03"><?= $model->paymentMethod->name ?></span>
            </div>
        </div>
    </td>
    
    <td>
        <div class="d-flex flex-column">
            <span class="tx-12 tx-medium">
                <i data-feather="calendar" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <?= Yii::$app->formatter->asDate($model->date_created, 'php:M d, Y') ?>
            </span>
            <span class="tx-11 tx-color-03">
                <i data-feather="clock" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <?= Yii::$app->formatter->asTime($model->date_created, 'php:h:i A') ?>
            </span>
        </div>
    </td>
    
    <td>
        <?= StatusCodes::getPaymentStatusText($model->status) ?>
    </td>
    
    <td class="text-right">
        <a href="<?= Yii::$app->urlManager->createUrl(['payments/update','id'=>$model->id, 'rid' => $model->request_id]); ?>"><i data-feather="edit" class="feather-small"></i> Update</a>
    </td>
</tr>

<?php
// Add these CSS classes if they're not already in your theme
$this->registerCss(<<<CSS
    .avatar-xs {
        width: 24px;
        height: 24px;
    }
    
    .avatar-xs .avatar-initial {
        font-size: 12px;
    }
    
    .tx-inherit {
        color: inherit;
    }
    
    .tx-inherit:hover {
        color: #0168fa;
        text-decoration: none;
    }
    
    .badge i {
        margin-top: -2px;
    }
CSS
);
?>