<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Withdrawals $model */

$this->title = 'Create Withdrawals';
$this->params['breadcrumbs'][] = ['label' => 'Withdrawals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="withdrawals-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
