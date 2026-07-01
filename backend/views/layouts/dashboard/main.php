<?php

/**
 * Dashboard Layout Handler
 * 
 * @author Maritim, Kiprotich <kip@piecommerce.com>
 * @copyright 2024 Pie Commerce
 */

/** @var \yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;
use backend\assets\AppAsset;
AppAsset::register($this);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/logos/icon.png']);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta name="description" content="Demo">
    <meta name="author" content="Demo">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <?= $this->render('_header'); ?>
    <?= $content ?>
    <?= $this->render('_footer'); ?>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage();
