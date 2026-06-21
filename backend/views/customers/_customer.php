<?php

use yii\helpers\Html;
use backend\helpers\StatusCodes;
?>

<tr>
    <td class="align-middle">
        <?= $index + 1 ?>
    </td>

    <!-- Customer Info -->
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm mg-r-10">
                <span class="avatar-initial rounded-circle bg-primary">
                    <?= strtoupper(substr($model->alias, 0, 1)) ?>
                </span>
            </div>
            <div class="mg-l-10">
                <?= Html::a(
                    Html::encode($model->alias),
                    ['view', 'id' => $model->id],
                    ['class' => 'tx-14 tx-medium mb-0 text-decoration-none']
                ) ?>
                <small class="tx-12 tx-color-03 d-block">
                    <?= Yii::t('app', 'Joined: {date}', [
                        'date' => Yii::$app->formatter->asDate($model->date_created, 'php:M d, Y')
                    ]) ?>
                </small>
            </div>
        </div>
    </td>

    <!-- Contact Info -->
    <td class="align-middle">
        <div class="d-flex flex-column">
            <span class="tx-medium">
                <i data-feather="phone" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <?= Html::encode($model->phone_number) ?>
            </span>
        </div>
    </td>

    <!-- Orders Info -->
    <td class="align-middle">
        <div class="d-flex align-items-center">
            <span class="badge bg-info mg-r-5">
                <?= $model->getOrdersCount() ?>
            </span>
            <small class="tx-12"><?= Yii::t('app', 'Orders') ?></small>
        </div>
    </td>

    <!-- Status -->
    <td class="align-middle">
        <span class="badge <?= $model->status == StatusCodes::ACTIVE_STATUS ? 'bg-success' : 'bg-danger' ?>">
            <?= $model->status == StatusCodes::ACTIVE_STATUS ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive') ?>
        </span>
    </td>

    <!-- Actions -->
    <td class="align-middle">
        <div class="btn-group btn-group-sm">
            <?= Html::a(
                '<i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>',
                ['view', 'id' => $model->id],
                [
                    'class' => 'btn btn-white',
                    'title' => Yii::t('app', 'View Customer'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>
            
            <?= Html::a(
                '<i data-feather="edit" class="wd-15 ht-15 stroke-2"></i>',
                ['update', 'id' => $model->id],
                [
                    'class' => 'btn btn-white',
                    'title' => Yii::t('app', 'Update Customer'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>

            <?= Html::a(
                '<i data-feather="shopping-cart" class="wd-15 ht-15 stroke-2"></i>',
                ['customer-requests/create', 'customer_id' => $model->id],
                [
                    'class' => 'btn btn-white',
                    'title' => Yii::t('app', 'Create Order'),
                    'data-toggle' => 'tooltip'
                ]
            ) ?>
        </div>
    </td>
</tr>

<?php
$js = <<<JS
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        if (typeof feather !== 'undefined') feather.replace();
    });
JS;
$this->registerJs($js);
?>