<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-header pd-y-15 pd-x-20 d-flex align-items-center justify-content-between">
        <h6 class="tx-13 tx-spacing-1 tx-uppercase tx-semibold mg-b-0">
            <i data-feather="star" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app', 'Customer Reviews') ?>
        </h6>
    </div>
    <div class="card-body pd-20">
        <?php if (empty($ratings)): ?>
            <div class="text-center pd-y-30 tx-color-03">
                <i data-feather="star" class="wd-50 ht-50 stroke-1"></i>
                <p class="tx-16 mg-t-10"><?= Yii::t('app', 'No ratings yet') ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($ratings as $rating): ?>
                <div class="review-item pd-20 mg-b-20 bg-gray-50 bd rounded">
                    <div class="d-flex align-items-center justify-content-between mg-b-15">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm mg-r-10">
                                <span class="avatar-initial rounded-circle bg-primary">
                                    <?= strtoupper(substr($rating->order->customer->alias, 0, 1)) ?>
                                </span>
                            </div>
                            <div>
                                <h6 class="tx-13 tx-inverse tx-semibold mg-b-2">
                                    <?= Html::a(
                                        Html::encode($rating->order->customer->alias),
                                        ['customers/view', 'id' => $rating->order->customer->id],
                                        ['class' => 'tx-inherit']
                                    ) ?>
                                </h6>
                                <span class="tx-11 tx-color-03">
                                    <?= Yii::$app->formatter->asRelativeTime($rating->date_created) ?>
                                </span>
                            </div>
                        </div>
                        <div class="tx-warning">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $rating->rating): ?>
                                    <i data-feather="star" class="wd-15 ht-15 stroke-2 fill-warning"></i>
                                <?php else: ?>
                                    <i data-feather="star" class="wd-15 ht-15 stroke-2"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <?php if ($rating->order): ?>
                        <div class="tx-12 tx-color-03 mg-b-10">
                            <i data-feather="package" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                            <?= Yii::t('app', 'Order') ?> #<?= str_pad($rating->order->id, 6, '0', STR_PAD_LEFT) ?> - 
                            <?= number_format($rating->order->volume_requested) ?> L
                        </div>
                    <?php endif; ?>

                    <?php if ($rating->notes): ?>
                        <p class="tx-14 mg-b-0">
                            <?= Html::encode($rating->notes) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($rating->status === 1): ?>
                        <div class="mg-t-15">
                            <?= Html::a(
                                '<i data-feather="message-square" class="wd-12 ht-12 stroke-2 mg-r-5"></i>' . 
                                Yii::t('app', 'Reply to Review'),
                                ['reply-rating', 'id' => $rating->id],
                                [
                                    'class' => 'btn btn-sm btn-white',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#replyModal'
                                ]
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (count($ratings) >= 10): ?>
                <div class="text-center">
                    <?= Html::a(
                        Yii::t('app', 'View All Reviews'),
                        ['vendors/reviews', 'id' => $rating->vendor_id],
                        ['class' => 'btn btn-sm btn-white']
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <?= Yii::t('app', 'Reply to Review') ?>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Reply form will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Handle reply modal
    $('#replyModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var url = button.attr('href');
        var modal = $(this);
        
        $.get(url, function(data) {
            modal.find('.modal-body').html(data);
            if (typeof feather !== 'undefined') feather.replace();
        });
    });
    
    // Reinitialize feather icons after modal content loads
    $('#replyModal').on('shown.bs.modal', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
JS
);
?> 