<?php

namespace api\modules\v1\controllers;

use Yii;
use Exception;
use backend\helpers\StatusCodes;
use backend\models\AppSettings;
use backend\models\PaymentMethods;
use backend\models\VendorGroups;
use backend\models\Customers;
use backend\models\Faqs;
use backend\models\Notification;
use backend\models\InformationGuides;

class SettingsController extends ApiController
{

    public function actionVendorGroups($api_token)
    {
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $vendorGroups = VendorGroups::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Vendor groups retrieved successfully',
                'data' => array_values($vendorGroups)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error Fetching list of vendor groups',
                'data' => ''
            ]);
        }
    }

    /**
     * Get Payment methods
     */
    public function actionGetPaymentMethods($api_token) {
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $paymentMethods = PaymentMethods::find()->where(['enabled' => StatusCodes::ENABLED_PAYMENT_METHOD])->all();
            $data = [];
            foreach($paymentMethods as $method) {
                $data[] = [
                    'id' => $method->id,
                    'name' => $method->name,
                    'description' => $method->description,
                    'code' => $method->code,
                    'image' => $method->image,
                    'requires_phone' => $method->requires_phone == "1" ? true : false
                ];
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Payment methods retrieved successfully',
                'data' => array_values($data)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error Fetching payment methods',
                'data' => ''
            ]);
        }
    }

    public function actionGetFaqs() {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $faqs = Faqs::find()
            ->where(['status' => StatusCodes::ACTIVE_STATUS])
            ->andWhere(['target' => 'customers'])
            ->all();
            $data = [];
            foreach($faqs as $q) {
                $data[] = [
                    'title' => $q->title,
                    'description' => $q->description,
                    'type' => $q->type
                ];
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'FAQs retrieved successfully',
                'data' => array_values($data)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error Fetching Faqs',
                'data' => ''
            ]);
        }
    }

    public function actionGetNotifications($api_token) {
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $notifications = Notification::find()->where(['customer_id' => $customer->id])->orderBy(['date_created' => SORT_DESC])->limit(10)->all();
            $data = [];
            foreach($notifications as $n) {
                $data[] = [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'data_values' => $n->data_values,
                    'date_created' => $n->date_created,
                ];
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => array_values($data)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error Fetching Notifications',
                'data' => ''
            ]);
        }
    }

    public function actionGetInformationGuides() {
        try {
            $guides = InformationGuides::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->all();
            $data = [];
            foreach($guides as $g) {
                $data[] = [
                    'id' => $g->id,
                    'title' => $g->title,
                    'description' => $g->description,
                    'type' => $g->type,
                    'date_created' => $g->date_created,
                ];
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Information Guides retrieved successfully',
                'data' => array_values($data)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error Fetching Information Guides',
                'data' => ''
            ]);
        }
    }
}