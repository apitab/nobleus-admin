<?php

namespace backend\controllers;

use backend\helpers\StatusCodes;
use yii;

use backend\models\ChangePasswordForm;
use backend\models\CustomerRequests;
use backend\models\Customers;
use backend\models\MyAccountForm;
use backend\models\Users;
use backend\models\Vendors;
use backend\models\Kiosks;

class DashboardController extends CustomController
{

    public $layout = 'dashboard/main';

    public function actionIndex()
    {
        //Get the number of active vendors on the system
        $vendors_count = Vendors::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->count();
        $vendors = Vendors::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->orderBy('id DESC')->limit(5)->all();

        //Kiosks Data
        $kiosks_count = Kiosks::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->count();

        //Recent Customers
        $customers = Customers::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->orderBy('id DESC')->limit(5)->all();
        $customers_count = Customers::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->count();

        //Find the list of vendor locations
        $vendorLocations = Vendors::find()
            ->select('other_names, first_name, location_coordinates')
            ->where(['status' => StatusCodes::ACTIVE_STATUS, 'is_online' => 1])
            ->all();

        $kiosksLocations = Kiosks::find()
            ->select('name, location_coordinates')
            ->where(['status' => StatusCodes::ACTIVE_STATUS])
            ->all();

        //Find the number of order the system has processed
        $customer_requests = CustomerRequests::find()->where('status IN (13,14,15)')->count();

        //Find the average  amount
        $average_request_amount = CustomerRequests::find()->where('status IN (13,14,15)')->average('total_amount') ?? 0;

        //Find the average  volume
        $average_request_volume = CustomerRequests::find()->where('status IN (13,14,15)')->average('volume_requested') ?? 0;


        return $this->render('index', [
            'vendors_count' => $vendors_count,
            'kiosks_count' => $kiosks_count,
            'customers' => $customers,
            'vendorLocations' => $vendorLocations,
            'kiosksLocations' => $kiosksLocations,
            'vendors' => $vendors,
            'customers_count' => $customers_count,
            'customer_requests' => $customer_requests,
            'average_request_amount' => $average_request_amount,
            'average_request_volume' => $average_request_volume
        ]);
    }

    /**
     * Change password
     * 
     * @return mixed
     */
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if (!$model->validate()) {
                return $this->render('change-password', ['model' => $model]);
            }
            $user = Users::findByUsername(Yii::$app->user->identity->email_address);
            $user->password_hash = Yii::$app->security->generatePasswordHash($model->newPassword);
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Your new password has been saved!');
            } else {
                Yii::$app->session->setFlash('error', 'We encountered an error. Contact Support for Help!');
            }
            return $this->redirect(Yii::$app->homeUrl);
        }
        return $this->render('change-password', ['model' => $model]);
    }

    /**
     * Manage my Account settings
     */
    public function actionSettings()
    {
        $model = new MyAccountForm();
        $user = Users::findByUsername(Yii::$app->user->identity->email_address);
        $model->phone_number = $user->phone_number;
        $model->names = $user->names;
        $model->language = $user->language;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if (!$model->validate()) {
                return $this->render('my-account', [
                    'model' => $model
                ]);
            }
            $user = Users::findByUsername(Yii::$app->user->identity->email_address);
            $user->names = $model->names;
            $user->phone_number = $model->phone_number;
            $user->language = $model->language;
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Account settings updated successfully');
            } else {
                Yii::$app->session->setFlash('error', 'We encountered an error. Contact Support for Help!');
            }
            return $this->redirect(Yii::$app->homeUrl);
        }

        return $this->render('my-account', [
            'model' => $model
        ]);
    }
}
