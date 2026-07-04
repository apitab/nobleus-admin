<?php

use common\helpers\ViewHelper;
use yii\helpers\Html;

/** @var array $rows */
/** @var string $date */
/** @var string $prevDate */
$this->title = Yii::t('app', 'Comparative Analysis');
?>
<div class="content pd-t-20">
  <?= $this->render('@backend/views/layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="trending-up" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . Yii::t('app', 'Comparative Analysis Report'),
    'links' => [
      ['title' => Yii::t('app', 'Billing'), 'url' => \yii\helpers\Url::to(['/billing/dashboard/index'])],
      ['title' => Yii::t('app', 'Comparative'), 'active' => true],
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <div class="card mg-b-15">
    <div class="card-body">
      <form method="get" class="form-inline">
        <label class="mg-r-10"><?= Yii::t('app', 'Reading Date') ?></label>
        <input type="date" name="date" value="<?= Html::encode($date) ?>" class="form-control mg-r-10">
        <?= Html::submitButton(Yii::t('app', 'Compare'), ['class' => 'btn btn-primary']) ?>
        <span class="mg-l-15 tx-color-03"><?= Yii::t('app', 'Compared with') ?>: <strong><?= Yii::$app->formatter->asDate($prevDate) ?></strong></span>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mg-b-0">
        <thead>
          <tr>
            <th><?= Yii::t('app', 'Supply No') ?></th>
            <th><?= Yii::t('app', 'Reading') ?> (<?= Yii::$app->formatter->asDate($prevDate) ?>)</th>
            <th><?= Yii::t('app', 'Reading') ?> (<?= Yii::$app->formatter->asDate($date) ?>)</th>
            <th><?= Yii::t('app', 'Consumption Difference') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= Html::encode($row['supply_no']) ?></td>
              <td><?= $row['previous_reading'] !== null ? number_format($row['previous_reading'], 3) : '<span class="tx-color-03">' . Yii::t('app', 'No data') . '</span>' ?></td>
              <td><?= number_format($row['current_reading'], 3) ?></td>
              <td>
                <?php if ($row['difference'] !== null): ?>
                  <span class="tx-medium <?= $row['difference'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($row['difference'], 3) ?></span>
                <?php else: ?>
                  <span class="tx-color-03">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="4" class="text-center tx-color-03"><?= Yii::t('app', 'No ledger entries for the selected date') ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
