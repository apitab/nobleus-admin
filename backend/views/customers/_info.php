<div class="card mg-b-10 bg-secondary tx-white">
    <div class="card-body">
        <div class="mg-b-20">
            <i data-feather="user"></i>
        </div>
        <h5 class="mg-b-2 tx-spacing--1 tx-white"><?= $model->alias; ?></h5>
        <p class="tx-white"><i class="feather-small" data-feather="phone"></i> <?= $model->phone_number; ?></p>
        <?php if (!is_null($model->primary_address)): ?>
            <p class="tx-white"><i class="feather-small" data-feather="map-pin"></i> <?= $model->primaryAddress->address; ?></p>
        <?php endif; ?>
        
    </div>
</div>
<a href="<?= Yii::$app->urlManager->createUrl(['customers/create-request', 'cid' => $model->id]); ?>" class="btn btn-primary w-100 mg-b-10"><i data-feather="plus-circle" class="feather-small"></i> <?= Yii::t('app', 'Create Order'); ?></a>
<a href="<?= Yii::$app->urlManager->createUrl(['customers/update', 'id' => $model->id]) ?>" class="btn btn-warning w-100"><i data-feather="edit"></i> <?= Yii::t('app', 'Update Customer') ?></a>