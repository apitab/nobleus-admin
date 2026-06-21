<?php

use backend\models\InformationGuides;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Information Guides');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => '<i data-feather="book-open" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Information Guides'),
            'links' => [
                ['title' => Yii::t('app', 'Information Guides'), 'active' => true]
        ],
    ]); ?>
        <?= ViewHelper::displayFlash(); ?>

        <div class="row">
            <!-- Left Sidebar - Guide Types -->
            <div class="col-sm-12 col-md-3">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                            <i data-feather="filter" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Guide Types') ?>
                        </h6>
                        <?= Html::a(
                            '<i data-feather="plus-circle" class="wd-15 ht-15 stroke-2"></i>',
                            ['create'],
                            [
                                'class' => 'btn btn-sm btn-primary',
                                'data-toggle' => 'tooltip',
                                'title' => Yii::t('app', 'Create New Guide')
                            ]
                        ) ?>
                    </div>
                    <div class="card-body pd-20">
                        <nav class="nav nav-pills flex-column">
                            <a class="nav-link <?= empty($type) ? 'active' : '' ?>" href="<?= Url::to(['index']) ?>">
                                <i data-feather="layers" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'All Guides') ?>
                                <span class="badge badge-primary"><?= $dataProvider->getTotalCount() ?></span>
                            </a>
                            <?php foreach ($guideTypes as $guideType): ?>
                                <a class="nav-link <?= $type === $guideType ? 'active' : '' ?>" 
                                   href="<?= Url::to(['index', 'type' => $guideType]) ?>">
                                    <?php
                                    $icon = 'help-circle';
                                    switch ($guideType) {
                                        case 'vendor':
                                            $icon = 'truck';
                                            break;
                                        case 'customer':
                                            $icon = 'users';
                                            break;
                                        case 'payment':
                                            $icon = 'credit-card';
                                            break;
                                        case 'general':
                                            $icon = 'info';
                                            break;
                                    }
                                    ?>
                                    <i data-feather="<?= $icon ?>" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                    <?= Yii::t('app', ucfirst($guideType)) ?>
                                    <span class="badge badge-light"><?= $typeCount[$guideType] ?? 0 ?></span>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content - Guides List -->
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
                        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
                            <?php if (!empty($type)): ?>
                                <i data-feather="book" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', '{type} Guides', ['type' => ucfirst($type)]) ?>
                            <?php else: ?>
                                <i data-feather="book" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                <?= Yii::t('app', 'All Guides') ?>
                            <?php endif; ?>
                        </h6>
                        <div class="d-none d-md-block">
                            <input type="text" class="form-control form-control-sm" 
                                   id="guideSearch" 
                                   placeholder="<?= Yii::t('app', 'Search guides...') ?>">
                        </div>
                    </div>
                    <div class="card-body pd-0">
                        <div class="table-responsive">
                            <table class="table table-hover mg-b-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="tx-12"><?= Yii::t('app', 'Title') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Target') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Type') ?></th>
                                        <th class="tx-12"><?= Yii::t('app', 'Description') ?></th>
                                        <th class="tx-12 text-center"><?= Yii::t('app', 'Status') ?></th>
                                        <th class="tx-12 text-right"><?= Yii::t('app', 'Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($dataProvider->getTotalCount() === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center pd-y-30 tx-color-03">
                                                <i data-feather="book-open" class="wd-50 ht-50 stroke-1"></i>
                                                <p class="tx-16 mg-t-10"><?= Yii::t('app', 'No guides available') ?></p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dataProvider->getModels() as $guide): ?>
                                            <tr class="guide-item" data-title="<?= Html::encode($guide->title) ?>">
                                                <td class="tx-medium">
                                                    <?= Html::encode($guide->title) ?>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        <?= $guide->displayTarget() ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $icon = 'help-circle';
                                                    switch ($guide->type) {
                                                        case 'vendor':
                                                            $icon = 'truck';
                                                            break;
                                                        case 'customer':
                                                            $icon = 'users';
                                                            break;
                                                        case 'payment':
                                                            $icon = 'credit-card';
                                                            break;
                                                        case 'general':
                                                            $icon = 'info';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="d-flex align-items-center">
                                                        <i data-feather="<?= $icon ?>" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
                                                        <?= Yii::t('app', ucfirst($guide->type)) ?>
                                                    </span>
                                                </td>
                                                <td class="tx-12">
                                                    <?= \yii\helpers\StringHelper::truncate(Html::encode($guide->description), 100) ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $guide->status ? 'success' : 'danger' ?>">
                                                        <?= $guide->status ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive') ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <?= Html::a(
                                                        '<i data-feather="edit-2" class="wd-15 ht-15 stroke-2"></i>',
                                                        ['update', 'id' => $guide->id],
                                                        [
                                                            'class' => 'btn btn-white btn-sm btn-icon mg-r-5',
                                                            'data-toggle' => 'tooltip',
                                                            'title' => Yii::t('app', 'Edit Guide')
                                                        ]
                                                    ) ?>
                                                    <?= Html::a(
                                                        '<i data-feather="trash-2" class="wd-15 ht-15 stroke-2"></i>',
                                                        ['delete', 'id' => $guide->id],
                                                        [
                                                            'class' => 'btn btn-white btn-sm btn-icon',
                                                            'data-toggle' => 'tooltip',
                                                            'title' => Yii::t('app', 'Delete Guide'),
                                                            'data' => [
                                                                'confirm' => Yii::t('app', 'Are you sure you want to delete this guide?'),
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Guide search functionality
        $('#guideSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.guide-item').filter(function() {
                $(this).toggle($(this).data('title').toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Reinitialize feather icons
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>

<?php $this->registerCss(<<<CSS
    .nav-pills .nav-link {
        padding: 8px 15px;
        margin-bottom: 5px;
    }
    
    .nav-pills .nav-link.active {
        background-color: #0168fa;
    }
CSS
); ?>