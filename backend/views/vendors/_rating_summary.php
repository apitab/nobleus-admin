<?php
use yii\helpers\Html;
?>
<div class="card mg-t-20">
    <div class="card-header pd-y-15 pd-x-20">
        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Rating Summary') ?>
        </h6>
    </div>
    <div class="card-body pd-20">
        <div class="d-flex align-items-center justify-content-between mg-b-10">
            <h1 class="tx-rubik tx-40 mg-b-0"><?= number_format($averageRating, 1) ?></h1>
            <div class="tx-18 tx-warning">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php if($i <= round($averageRating)): ?>
                        <i data-feather="star" class="wd-18 ht-18 stroke-2 fill-warning"></i>
                    <?php else: ?>
                        <i data-feather="star" class="wd-18 ht-18 stroke-2"></i>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
        <div class="tx-12 tx-color-03"><?= Yii::t('app', 'Based on {n} reviews', ['n' => $totalRatings]) ?></div>
        
        <?php for($i = 5; $i >= 1; $i--): ?>
            <?php 
                $percentage = $totalRatings > 0 ? ($ratingBreakdown[$i] / $totalRatings) * 100 : 0;
            ?>
            <div class="d-flex align-items-center mg-t-10">
                <div class="tx-12 mg-r-10"><?= $i ?></div>
                <div class="flex-1 mg-r-10">
                    <div class="progress ht-5 mg-b-0">
                        <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%"></div>
                    </div>
                </div>
                <div class="tx-12"><?= $ratingBreakdown[$i] ?></div>
            </div>
        <?php endfor; ?>
    </div>
</div> 