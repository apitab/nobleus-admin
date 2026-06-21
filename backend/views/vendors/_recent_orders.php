<?php
use backend\helpers\StatusCodes;
use yii\helpers\Html;
?>
<div class="card mg-b-20">
    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
            <i data-feather="clock" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Recent Orders') ?>
        </h6>
    </div>
    <div class="card-body pd-0">
        <div class="table-responsive">
            <table class="table table-hover mg-b-0">
                <thead class="thead-light">
                    <tr>
                        <th class="tx-12"><?= Yii::t('app', 'Date') ?></th>
                        <th class="tx-12"><?= Yii::t('app', 'Customer') ?></th>
                        <th class="tx-12"><?= Yii::t('app', 'Volume') ?></th>
                        <th class="tx-12"><?= Yii::t('app', 'Order Status') ?></th>
                        <th class="tx-12"><?= Yii::t('app', 'Payment Status') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_deliveries)): ?>
                        <tr>
                            <td colspan="4" class="text-center tx-color-03 pd-y-15">
                                <?= Yii::t('app', 'No recent deliveries found') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_deliveries as $delivery): ?>
                            <tr>
                                <td class="tx-12">
                                    <?= Yii::$app->formatter->asDate($delivery->delivery_date, 'php:M d, Y') ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs mg-r-5">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                <?= strtoupper(substr($delivery->customer->alias, 0, 1)) ?>
                                            </span>
                                        </div>
                                        <a href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $delivery->customer_id]) ?>"
                                            class="tx-13 tx-medium">
                                            <?= Html::encode($delivery->customer->alias) ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="tx-12">
                                    <?= number_format($delivery->volume_requested) ?> <?= Yii::t('app', 'Barrels') ?>
                                </td>
                                <td class="align-middle">
                                    <?php
                                    $statusClass = '';
                                    switch ($delivery->status) {
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
                                    <span
                                        class="badge <?= $statusClass ?>"><?= StatusCodes::getRequestStatusText($delivery->status) ?></span>
                                </td>

                                <!-- Payment Status Column -->
                                <td class="align-middle">
                                    <?php if ($delivery->payment_id): ?>
                                        <div class="d-flex flex-column">
                                            <small class="tx-12 tx-color-03"><?= $delivery->payment->paymentMethod->name ?></small>
                                            <span
                                                class="badge <?= $delivery->payment->status == StatusCodes::PAYMENT_SUCCESS ? 'bg-success' : 'bg-warning' ?>">
                                                <?= StatusCodes::getPaymentStatusText($delivery->payment->status) ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= Yii::t('app', 'Not Available') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle text-right">
                                    <div class="btn-group btn-group-sm">
                                        <?= Html::a(
                                            '<i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>',
                                            ['customer-requests/view', 'id' => $delivery->id],
                                            [
                                                'class' => 'btn btn-white btn-sm',
                                                'title' => Yii::t('app', 'View Request'),
                                                'data-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>