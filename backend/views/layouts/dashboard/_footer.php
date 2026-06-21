<footer class="footer">
    <div>
        <span>&copy; <?= Date('Y')?> Hargeisa Water Agency | Dhaamiye App | All Rights Reserved </span>
    </div>
    <div>
        <nav class="nav">
            <a href="<?= Yii::$app->urlManager->createUrl('site/terms-of-use')?>" class="nav-link"><?= Yii::t('app','Terms of Use') ?></a>
            <a href="<?= Yii::$app->urlManager->createUrl('site/privacy-policy')?>" class="nav-link"><?= Yii::t('app','Privacy Policy') ?></a>
        </nav>
    </div>
</footer>