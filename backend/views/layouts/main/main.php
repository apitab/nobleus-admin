<?php

/**
 * Non Auth Layout Handler
 * 
 * @author Maritim, Kiprotich <kip@piecommerce.com>
 * @copyright 2024 Pie Commerce
 */

/** @var \yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Html;
use backend\assets\FrontAppAsset;

FrontAppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="Hargeisa Water Agency">
    <meta name="author" content="HWA/UNITAC">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?= $this->render('_footer'); ?>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage();
