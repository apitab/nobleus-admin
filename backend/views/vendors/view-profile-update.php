<?php

use backend\models\VendorGroups;
use backend\models\WaterSources;
use backend\models\Locations;
use backend\helpers\StatusCodes;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\web\YiiAsset;

use common\helpers\ViewHelper;
use backend\models\VendorSearchForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manage Vendors';
YiiAsset::register($this);
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Vendor profile update request #' . $model->id),
            'links' => [
                ['title' => 'Vendor Profile Updates', 'url' => Yii::$app->urlManager->createUrl('vendors/profile-updates')],
                ['title' => Yii::t('app', 'Vendor profile update #' . $model->id), 'active' => true]
            ],
        ]); ?>
        <?= ViewHelper::displayFlash(); ?>
        <div class="row">
            <div class="col col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Updated Details</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-responsive">
                            <tr>
                                <td>
                                    First Name
                                </td>
                                <td>
                                    <?= $model->first_name; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Other Names
                                </td>
                                <td>
                                    <?= $model->other_names; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Phone Number
                                </td>
                                <td>
                                    <?= $model->mobile_number; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Tank Volume
                                </td>
                                <td>
                                    <?= $model->tank_volume; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Vehicle registration No#
                                </td>
                                <td>
                                    <?= $model->vehicle_registration_number; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Minimum Order Quantity
                                </td>
                                <td>
                                    <?= $model->minimum_order_qty; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Operating Hours
                                </td>
                                <td>
                                    <?= $model->operating_hours; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col col-md-6">
            <div class="card">
                    <div class="card-header">
                        <h4>Current Details</h4>
                    </div>
                    <div class="card-body">
                    <table class="table table-striped table-responsive">
                            <tr>
                                <td>
                                    First Name
                                </td>
                                <td>
                                    <?= $vendor->first_name; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Other Names
                                </td>
                                <td>
                                    <?= $vendor->other_names; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Phone Number
                                </td>
                                <td>
                                    <?= $vendor->mobile_number; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Tank Volume
                                </td>
                                <td>
                                    <?= $vendor->tank_volume; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Vehicle registration No#
                                </td>
                                <td>
                                    <?= $vendor->vehicle_registration; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Minimum Order Quantity
                                </td>
                                <td>
                                    <?= $vendor->moq; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Operating Hours
                                </td>
                                <td>
                                    <?= $vendor->operating_hours; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mg-t-20">
            <div class="col col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Actions</h4>
                    </div>
                    <div class="card-body">
                        <?php \yii\widgets\Pjax::begin(); ?>
                        <?php echo Html::beginForm([Yii::$app->urlManager->createUrl(['vendors/view-update-request', 'id' => $model->id])], 'post', ['class' => 'form-inline']); ?>
                            <?= Html::hiddenInput('id', $model->id); ?>
                            <?= Html::submitButton('Verify', ['class' => 'btn btn-success mg-r-10', 'name' => 'decision', 'value' => 'approve']); ?>
                            <?= Html::submitButton('Deny', ['class' => 'btn btn-danger', 'name' => 'decision', 'value' => 'deny']); ?>
                        <?php echo Html::endForm(); ?>
                        <?php \yii\widgets\Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>