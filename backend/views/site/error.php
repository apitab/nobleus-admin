<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception*/

use yii\helpers\Html;
use common\helpers\ViewHelper;

$this->title = $name;

?>
<?php if (Yii::$app->user->isGuest) : ?>
    <div class="hero-auth">
        <div class="container">
            <div class="text-center">
                <?= Html::img('/images/logos/logo.png', ['width' => '50px']) ?>
            </div>
            <div class="d-flex justify-content-center ht-100p ">
                <div class="align-items-center justify-content-center pd-t-20" style="min-width: 40%">
                    <h4 class="tx-20 tx-sm-24 text-center">Application Error</h4>
                    <h6 class="text-center mg-b-20">
                        Hargeisa Water Agency | Dhaamiye App
                    </h6>
                    <div class="wd-100p d-flex flex-column mg-b-40">
                        <h1><?= Html::encode($this->title) ?></h1>

                        <div class="alert alert-danger">
                            <?= nl2br(Html::encode($message)) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php else : ?>
    <div class="content pd-t-20">

        <div class="d-flex justify-content-center ht-100p ">
            <div class="align-items-center justify-content-center pd-t-20" style="min-width: 40%">
                <h4 class="tx-20 tx-sm-24 text-center">Application Error</h4>
                <h6 class="text-center mg-b-20">
                    Hargeisa Water Agency | Dhaamiye App
                </h6>
                <div class="wd-100p d-flex flex-column mg-b-40">
                    <h1><?= Html::encode($this->title) ?></h1>
                    <div class="alert alert-danger">
                        <?= nl2br(Html::encode($message)) ?>
                    </div>
                    <p>
                        The above error occurred while the Web server was processing your request.
                    </p>
                    <p>
                        <a href="<?= Yii::$app->homeUrl ?>">Home</a> | <a href="<?= Yii::$app->request->referrer ?>">Previous Page</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>