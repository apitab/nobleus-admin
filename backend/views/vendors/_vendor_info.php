<?php

use backend\helpers\StatusCodes;
use yii\helpers\Html;

?>
<!-- Vendor Info Card -->
<div class="card">
    <div class="card-body pd-25 text-center">
        <div class="avatar avatar-xl mg-b-15">
            <?php if ($model->display_pic != ''): ?>
                <img src="<?= Yii::$app->urlManager->createUrl($model->display_pic) ?>" class="rounded-circle" alt="">
            <?php else: ?>
                <span class="avatar-initial rounded-circle bg-primary">
                    <?= strtoupper(substr($model->first_name, 0, 1)) ?>
                </span>
            <?php endif; ?>
            <?php if ($model->status === StatusCodes::ACTIVE_STATUS): ?>
                <div class="avatar-badge bg-success"><i data-feather="check" class="wd-12 ht-12 stroke-2"></i></div>
            <?php else: ?>
                <div class="avatar-badge bg-danger"><i data-feather="x" class="wd-12 ht-12 stroke-2"></i>
                </div>
            <?php endif; ?>
        </div>
        <h5 class="tx-semibold mg-b-5">
            <?= Html::encode($model->other_names . ', ' . $model->first_name) ?>
        </h5>
        <p class="tx-color-03 mg-b-20"><?= Html::encode($model->vendorGroup->name ?? '') ?></p>
        <div class="d-flex align-items-center justify-content-center mg-b-25">
            <div class="d-flex">
                <i data-feather="phone" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                <span><?= Html::encode($model->mobile_number) ?></span>
            </div>
        </div>
        <?php if ($model->status === StatusCodes::ACTIVE_STATUS): ?>
            <div class="badge badge-success">
                <i data-feather="check-circle" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <?= Yii::t('app', 'Active') ?>
            </div>
        <?php else: ?>
            <div class="badge badge-danger">
                <i data-feather="x-circle" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                <?= Yii::t('app', 'Inactive') ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer pd-20">
        <div class="row row-xs">
            <div class="col">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-5">
                    <?= Yii::t('app', 'Joined') ?>
                </h6>
                <span class="tx-12">
                    <?= Yii::$app->formatter->asDate($model->date_created, 'php:M d, Y') ?>
                </span>
            </div>
            <div class="col">
                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-5">
                    <?= Yii::t('app', 'Source') ?>
                </h6>
                <span class="tx-12">
                    <?= Html::encode($model->waterSource->name) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Rating Summary Card -->
<?= $this->render('_rating_summary', [
    'averageRating' => $averageRating,
    'totalRatings' => $totalRatings,
    'ratingBreakdown' => $ratingBreakdown,
]) ?>

<!-- Vendor Details Card -->
<div class="card mg-t-20">
    <div class="card-header">
        <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-5">
            <?= Yii::t('app', 'Vendor Details') ?>
        </h6>
    </div>
    <div class="card-body pd-10">
        <table class="table table-striped table-bordered">
            <tr>
                <td><?= Yii::t('app', 'Vehicle Registration') ?></td>
                <td><?= Html::encode($model->vehicle_registration) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Owner/Rental') ?></td>
                <td><?= Html::encode($model->owner_rental) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Owner Name') ?></td>
                <td><?= Html::encode($model->owners_name) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Owner Phone Number') ?></td> 
                <td><?= Html::encode($model->owner_phone_number) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Govt Registration') ?></td>
                <td><?= Html::encode($model->govt_reg) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Water Source') ?></td>
                <td><?= Html::encode($model->waterSource->name) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Operating Hours') ?></td>
                <td><?= Html::encode($model->operating_hours) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Minimum Order Quantity') ?></td>
                <td><?= Html::encode($model->moq) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Restrict to Specific Locations') ?></td>
                <td><?= Html::encode($model->restrict_to_specific_locations ? 'Yes' : 'No') ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', 'Specific Locations') ?></td>
                <td><?= Html::encode($model->specific_locations) ?></td>
            </tr>
        </table>
    </div>
</div>

