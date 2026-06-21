<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
use backend\helpers\Helpers;
?>

<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>

    <!-- Customer Column -->
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xs mg-r-10">
                <span class="avatar-initial rounded-circle bg-primary">
                    <?= strtoupper(substr($model->customer->alias, 0, 1)) ?>
                </span>
            </div>
            <div class="mg-l-10">
                <?= Html::a(
                    Html::encode($model->customer->alias),
                    ['customers/view', 'id' => $model->customer_id],
                    ['class' => 'tx-13 tx-medium mb-0 text-decoration-none']
                ) ?>
                <small class="tx-12 tx-color-03 d-block"><?= $model->customer->phone_number ?></small>
            </div>
        </div>
    </td>

    <!-- Vendor Column -->
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xs mg-r-10">
                <?php if ($model->vendor->display_pic): ?>
                    <img src="<?= Yii::$app->urlManager->createUrl($model->vendor->display_pic) ?>" class="rounded-circle" alt="">
                <?php else: ?>
                    <span class="avatar-initial rounded-circle bg-info">
                        <?= strtoupper(substr($model->vendor->first_name, 0, 1)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="mg-l-10">
                <?= Html::a(
                    Html::encode($model->vendor->first_name . ' ' . $model->vendor->other_names),
                    ['vendors/view', 'id' => $model->vendor_id],
                    ['class' => 'tx-13 tx-medium mb-0 text-decoration-none']
                ) ?>
                <small class="tx-12 tx-color-03 d-block"><?= $model->vendor->mobile_number ?></small>
            </div>
        </div>
    </td>

    <!-- Address Column -->
    <td class="align-middle">
        <p class="tx-13 tx-medium mg-b-0"><?= Html::encode($model->customerAddress->address) ?></p>
        <small class="tx-12 tx-color-03"><?= Yii::t('app', 'Delivery Location') ?></small>
    </td>

    <!-- Volume Column -->
    <td class="align-middle">
        <span class="tx-medium"><?= number_format($model->volume_requested) ?></span>
        <small class="tx-12 tx-color-03 d-block"><?= Yii::t('app', 'Barrels') ?></small>
    </td>

    <!-- Amount Column -->
    <td class="align-middle">
        <span class="tx-medium"><?= Helpers::localCurrencyFormatter(Helpers::convertAmount($model->total_amount)) ?></span>
    </td>

    <!-- Type Column -->
    <td class="align-middle">
        <span class="tx-medium"><?= $model->is_shared_request == 1 ? Yii::t('app', 'Shared') : Yii::t('app', 'Single') ?></span>
    </td>

    <!-- Request Status Column -->
    <td class="align-middle">
        <?php
        $statusClass = '';
        switch($model->status) {
            case StatusCodes::NEW_CUSTOMER_REQUEST:
            case StatusCodes::NEW_CUSTOMER_REQUEST_OPEN:
                $statusClass = 'bg-primary';
                break;
            case StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST:
                $statusClass = 'bg-info';
                break;
            case StatusCodes::DELIVERED_CUSTOMER_REQUEST:
                $statusClass = 'bg-warning';
                break;
            case StatusCodes::COMPLETED_CUSTOMER_REQUEST:
                $statusClass = 'bg-success';
                break;
            case StatusCodes::CUSTOMER_CANCELLED_REQUEST:
                $statusClass = 'bg-danger';
                break;
            default:
                $statusClass = 'bg-secondary';
        }
        ?>
        <span class="badge <?= $statusClass ?>"><?= StatusCodes::getRequestStatusText($model->status) ?></span>
    </td>

    <!-- Payment Status Column -->
    <td class="align-middle">
        <?php if ($model->payment_id): ?>
            <div class="d-flex flex-column">
                <small class="tx-12 tx-color-03"><?= $model->payment->paymentMethod->name ?></small>
                <span class="badge <?= $model->payment->status == StatusCodes::PAYMENT_SUCCESS ? 'bg-success' : 'bg-warning' ?>">
                    <?= StatusCodes::getPaymentStatusText($model->payment->status) ?>
                </span>
            </div>
        <?php else: ?>
            <span class="badge bg-secondary"><?= Yii::t('app', 'Not Available') ?></span>
        <?php endif; ?>
    </td>

    <!-- Date Column -->
    <td class="align-middle">
        <div class="d-flex flex-column">
            <span class="tx-medium tx-13"><?= Yii::$app->formatter->asDate($model->date_created, 'php:M d, Y') ?></span>
            <small class="tx-12 tx-color-03"><?= Yii::$app->formatter->asTime($model->date_created, 'php:h:i A') ?></small>
        </div>
    </td>

    <!-- Actions Column -->
    <td class="align-middle text-right">
        <div class="btn-group btn-group-sm">
            <?= Html::a(
                '<i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>',
                ['view', 'id' => $model->id],
                [
                    'class' => 'btn btn-white btn-sm',
                    'title' => Yii::t('app', 'View Request'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>
            
            <?php if ($model->status == StatusCodes::NEW_CUSTOMER_REQUEST): ?>
                <?= Html::a(
                    '<i data-feather="edit-2" class="wd-15 ht-15 stroke-2"></i>',
                    ['update', 'id' => $model->id],
                    [
                        'class' => 'btn btn-white btn-sm',
                        'title' => Yii::t('app', 'Update Request'),
                        'data-toggle' => 'tooltip'
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </td>
</tr>

<?php
// Initialize tooltips
$js = <<<JS
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        if (typeof feather !== 'undefined') feather.replace();
    });
JS;
$this->registerJs($js);
?>