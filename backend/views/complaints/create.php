<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Complaints $model */

$this->title = Yii::t('app', 'Create Complaints');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Complaints'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="complaints-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
