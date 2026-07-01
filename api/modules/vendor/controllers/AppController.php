<?php

namespace api\modules\vendor\controllers;

use api\modules\v1\controllers\ApiController;
use backend\models\VendorDeliveries;
use backend\models\VendorGroupPoints;
use backend\models\VendorRefills;
use backend\models\VendorUpdates;
use Exception;
use yii;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;

use backend\models\Vendors;
use backend\helpers\StatusCodes;
use backend\models\VendorNotifications;
use backend\models\CustomerRequests;
use backend\models\OutboundSms;
use api\helpers\Helpers;
use backend\models\Payments;
use backend\models\Complaints;
use backend\models\Notifications;
use yii\web\UploadedFile;
use backend\models\Alerts;
use backend\models\InformationGuides;
use backend\models\Faqs;
use backend\models\PaymentMethods;
use backend\models\Withdrawals;
use backend\models\VideoTutorials;
class AppController extends ApiController
{
    public function beforeAction($action)
    {
        // Get language from query parameter, default to 'en'
        $lang = Yii::$app->request->get('lang', 'en');

        // Validate language and set it
        $supportedLanguages = ['en', 'so'];
        if (in_array($lang, $supportedLanguages)) {
            Yii::$app->language = $lang;
        }

        return parent::beforeAction($action);
    }

    public function actionGetDashboardData()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            //Get notifications for the vendor
            $notificationsList = Notifications::find()->where(['key_id' => $vendor->id, 'key_type' => 'vendor', 'is_read' => 0])->orderBy(['id' => SORT_DESC])->all();
            $notifications = [];
            foreach ($notificationsList as $notification) {
                $notifications[] = [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'timestamp' => $notification->date_created,
                    'type' => $notification->type,
                    'is_read' => $notification->status
                ];
            }

            //Get the list of orders for the vendor - 4 requests of each status
            $orders = [];
            $statuses = [10, 12, 13, 14, 15, 16, 17];
            foreach ($statuses as $status) {
                $query = CustomerRequests::find()
                    ->where(['vendor_id' => $vendor->id, 'status' => $status]);

                // For statuses 10 (new) and 11 (open), ensure delivery_date is today or in the future
                if (in_array($status, [10, 11], true)) {
                    $query->andWhere(['>=', 'delivery_date', date('Y-m-d')]);
                }

                $statusOrders = $query
                    ->orderBy(['id' => SORT_ASC])
                    ->limit(8)
                    ->all();
                $orders = array_merge($orders, $statusOrders);
            }

            //Open orders should be only the ones that are open and have a delivery date today or in the future and the vendor is not this one (or vendor_id is null)
            $openOrders = CustomerRequests::find()
                ->where(['status' => 11])
                ->andWhere(['>=', 'delivery_date', date('Y-m-d')])
                ->andWhere(['or', ['!=', 'vendor_id', $vendor->id], ['IS', 'vendor_id', null]])
                ->orderBy(['id' => SORT_ASC])
                ->all();
            $orders = array_merge($orders, $openOrders);

            // Organize orders by status into separate arrays
            $newOrders = [];
            $openOrders = [];
            $acceptedOrders = [];
            $deliveredOrders = [];
            $paymentInitiatedOrders = [];
            $awaitingConfirmationOrders = [];
            $completedPayments = [];
            $pendingPaymentOrders = [];

            foreach ($orders as $order) {
                $locationCoordinates = explode(',', $order->customerAddress->location_coordinates);
                $vendorCoordinates = explode(',', $vendor->location_coordinates);
                $distance = Helpers::haversineDistance($vendorCoordinates[0], $vendorCoordinates[1], $locationCoordinates[0], $locationCoordinates[1]);
                //Set distance to 1 decimal place
                $distance = number_format($distance, 1);

                switch ($order->status) {
                    case 10:
                        $newOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status,
                            'distance' => $distance
                        ];
                        break;
                    case 11:
                        $openOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status,
                            'distance' => $distance
                        ];
                        break;
                    case 12:
                        $acceptedOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status,
                            'distance' => $distance
                        ];
                        break;
                    case 13:
                        $deliveredOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status,
                            'distance' => $distance
                        ];
                        break;
                    case 14:
                        $paymentInitiatedOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 15:
                        $awaitingConfirmationOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 16:
                        $completedPayments[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                        case 17:
                            $pendingPaymentOrders[] = [
                                'id' => $order->id,
                                'customer_name' => $order->customer->alias,
                                'customer_phone' => $order->customer->phone_number,
                                'volume_requested' => $order->volume_requested,
                                'total_amount' => $order->total_amount,
                                'delivery_date' => $order->delivery_date,
                                'delivery_notes' => $order->delivery_notes,
                                'customer_address' => $order->customerAddress->address,
                                'is_shared_order' => $order->is_shared_request,
                                'latitude' => $locationCoordinates[0],
                                'longitude' => $locationCoordinates[1],
                                'date_created' => $order->date_created,
                                'status' => $order->status
                            ];
                            break;
                }
            }

            $locationCoordinates = explode(',', $vendor->location_coordinates);

            $alerts = Alerts::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                ->andWhere(['type' => Alerts::TYPE_VENDORS])
                ->orderBy(['expiry_date' => SORT_ASC])
                ->all();
            $alertList = [];
            foreach ($alerts as $alert) {
                $alertList[] = [
                    'id' => $alert->id,
                    'level' => $alert->level,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'expiry_date' => $alert->expiry_date,
                    'date_created' => $alert->date_created,
                    'attachment' => $alert->attachment,
                    'attachment_url' => $alert->attachment ? Yii::getAlias('@web') . '/' . $alert->attachment : null,
                ];
            }


            $informationGuides = InformationGuides::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['target' => 'vendors'])
                ->all();
            $informationGuideList = [];
            foreach ($informationGuides as $informationGuide) {
                $informationGuideList[] = [
                    'id' => $informationGuide->id,
                    'title' => $informationGuide->title,
                    'description' => $informationGuide->description,
                    'type' => $informationGuide->type,
                    'date_created' => $informationGuide->date_created,
                ];
            }

            $faqsList = [];
            $faqs = Faqs::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['target' => 'vendors'])
                ->all();
            foreach ($faqs as $q) {
                $faqsList[] = [
                    'title' => $q->title,
                    'description' => $q->description,
                    'type' => $q->type
                ];
            }

            //Find payment methods
            $paymentMethods = PaymentMethods::find()
                ->where(['enabled' => 1])
                ->all();
            $paymentMethodList = [];
            foreach ($paymentMethods as $paymentMethod) {
                $paymentMethodList[] = [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                    'code' => $paymentMethod->code,
                    'image' => $paymentMethod->image,
                ];
            }

            //payments - combine Payments and Withdrawals, get latest 10
            $payments = Payments::find()
                ->joinWith(['request'])
                ->where(['customer_requests.vendor_id' => $vendor->id])
                ->all();
            
            $withdrawals = Withdrawals::find()
                ->where(['from_type' => 'vendor', 'from_id' => $vendor->id])
                ->all();
            
            // Combine and normalize
            $combinedTransactions = [];
            
            // Add payments
            foreach ($payments as $payment) {
                $combinedTransactions[] = [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'amount' => (float)$payment->amount,
                    'description' => $payment->description,
                    'status' => $payment->status,
                    'date_created' => $payment->date_created,
                    'transaction_date' => $payment->date_created,
                    'payment_method_id' => $payment->payment_method_id,
                    'request_id' => $payment->request_id,
                ];
            }
            
            // Add withdrawals
            foreach ($withdrawals as $withdrawal) {
                $combinedTransactions[] = [
                    'id' => $withdrawal->id,
                    'type' => 'withdrawal',
                    'amount' => (float)$withdrawal->amount,
                    'description' => 'Withdrawal',
                    'status' => $withdrawal->status,
                    'date_created' => $withdrawal->created_at,
                    'transaction_date' => $withdrawal->created_at,
                    'payment_method_id' => null,
                    'request_id' => null,
                ];
            }
            
            // Sort by transaction_date descending and get latest 10
            usort($combinedTransactions, function($a, $b) {
                $timeA = strtotime($a['transaction_date'] ?? '1970-01-01');
                $timeB = strtotime($b['transaction_date'] ?? '1970-01-01');
                return $timeB - $timeA;
            });
            
            $paymentList = array_slice($combinedTransactions, 0, 10);

            //Video tutorials
            $videoTutorials = VideoTutorials::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['target' => 'vendors'])
                ->all();
            $videoTutorialList = [];
            // Build full URL to backend web/videos directory from config param
            $backendBaseUrl = rtrim(Yii::$app->params['backendBaseUrl'] ?? '', '/') . '/';
            foreach ($videoTutorials as $videoTutorial) {
                $videoTutorialList[] = [
                    'id' => $videoTutorial->id,
                    'title' => $videoTutorial->name,
                    'description' => $videoTutorial->description,
                    // Full URL to video file in backend webroot (video_name already has 'videos/' prepended)
                    'video_url' => $backendBaseUrl . ltrim($videoTutorial->video_name, '/'),
                    'date_created' => $videoTutorial->date_created,
                ];
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Dashboard data fetched successfully'),
                'data' => [
                    'alerts' => $alertList,
                    'informationGuides' => $informationGuideList,
                    'faqs' => $faqsList,
                    //'complaints' => $complaintsList,
                    'wallet_balance' => number_format($vendor->wallet_balance, 2),
                    'availableVolume' => $vendor->available_volume,
                    'isOnline' => $vendor->is_online,
                    'latitude' => $locationCoordinates[0],
                    'longitude' => $locationCoordinates[1],
                    'locationDescription' => $vendor->location_description,
                    'restrictToSpecificLocations' => $vendor->restrict_to_specific_locations,
                    'specificLocations' => $vendor->specific_locations,
                    'minimumOrderQuantity' => $vendor->moq,
                    'orders' => [
                        'new' => $newOrders,
                        'open' => $openOrders,
                        'accepted' => $acceptedOrders,
                        'delivered' => $deliveredOrders,
                        'paymentInitiated' => $paymentInitiatedOrders,
                        'awaitingConfirmation' => $awaitingConfirmationOrders,
                        'completedPayments' => $completedPayments,
                        'pendingPayment' => $pendingPaymentOrders
                    ],
                    'notifications' => array_values($notifications),
                    'paymentMethods' => $paymentMethodList,
                    'payments' => $paymentList,
                    'video_tutorials' => $videoTutorialList
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetPaymentHistory($page = 1, $pageSize = 10)
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            // Get pagination parameters
            $page = (int)Yii::$app->request->get('page', $page);
            $pageSize = (int)Yii::$app->request->get('pageSize', $pageSize);
            
            // Get filter parameters
            $paymentMethodId = Yii::$app->request->get('payment_method_id');
            $transactionType = Yii::$app->request->get('type'); // 'payment' or 'withdrawal'
            $startDate = Yii::$app->request->get('start_date');
            $endDate = Yii::$app->request->get('end_date');
            
            // Validate parameters
            if ($page < 1) {
                $page = 1;
            }
            if ($pageSize < 1) {
                $pageSize = 10;
            }
            if ($pageSize > 100) {
                $pageSize = 100; // Limit max page size
            }
            
            // Validate transaction type
            if ($transactionType && !in_array($transactionType, ['payment', 'withdrawal'])) {
                throw new Exception("Invalid transaction type. Must be 'payment' or 'withdrawal'");
            }
            
            // Validate payment method ID if provided
            if ($paymentMethodId !== null && $paymentMethodId !== '') {
                $paymentMethodId = (int)$paymentMethodId;
                if ($paymentMethodId < 1) {
                    throw new Exception("Invalid payment method ID");
                }
            } else {
                $paymentMethodId = null;
            }
            
            // Validate and parse dates
            if ($startDate) {
                $startDate = date('Y-m-d H:i:s', strtotime($startDate));
                if ($startDate === false) {
                    throw new Exception("Invalid start date format");
                }
            }
            if ($endDate) {
                $endDate = date('Y-m-d H:i:s', strtotime($endDate));
                if ($endDate === false) {
                    throw new Exception("Invalid end date format");
                }
                // Set end date to end of day
                $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            }

            // Get all payments for the vendor
            $payments = Payments::find()
                ->joinWith(['request'])
                ->where(['customer_requests.vendor_id' => $vendor->id])
                ->all();
            
            // Get all withdrawals for the vendor
            $withdrawals = Withdrawals::find()
                ->where(['from_type' => 'vendor', 'from_id' => $vendor->id])
                ->all();
            
            // Combine and normalize
            $combinedTransactions = [];
            
            // Add payments
            foreach ($payments as $payment) {
                $combinedTransactions[] = [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'amount' => (float)$payment->amount,
                    'description' => $payment->description,
                    'status' => $payment->status,
                    'date_created' => $payment->date_created,
                    'transaction_date' => $payment->date_created,
                    'payment_method_id' => $payment->payment_method_id,
                    'request_id' => $payment->request_id,
                ];
            }
            
            // Add withdrawals
            foreach ($withdrawals as $withdrawal) {
                $combinedTransactions[] = [
                    'id' => $withdrawal->id,
                    'type' => 'withdrawal',
                    'amount' => (float)$withdrawal->amount,
                    'description' => 'Withdrawal',
                    'status' => $withdrawal->status,
                    'date_created' => $withdrawal->created_at,
                    'transaction_date' => $withdrawal->created_at,
                    'payment_method_id' => null,
                    'request_id' => null,
                ];
            }
            
            // Apply filters
            $filteredTransactions = [];
            foreach ($combinedTransactions as $transaction) {
                // Filter by transaction type
                if ($transactionType && $transaction['type'] !== $transactionType) {
                    continue;
                }
                
                // Filter by payment method (only applies to payments)
                if ($paymentMethodId !== null && $transaction['type'] === 'payment') {
                    if ($transaction['payment_method_id'] != $paymentMethodId) {
                        continue;
                    }
                }
                
                // Filter by date range
                if ($startDate || $endDate) {
                    $transactionTime = strtotime($transaction['transaction_date'] ?? '1970-01-01');
                    
                    if ($startDate && $transactionTime < strtotime($startDate)) {
                        continue;
                    }
                    
                    if ($endDate && $transactionTime > strtotime($endDate)) {
                        continue;
                    }
                }
                
                $filteredTransactions[] = $transaction;
            }
            
            // Sort by transaction_date descending
            usort($filteredTransactions, function($a, $b) {
                $timeA = strtotime($a['transaction_date'] ?? '1970-01-01');
                $timeB = strtotime($b['transaction_date'] ?? '1970-01-01');
                return $timeB - $timeA;
            });
            
            // Calculate pagination on filtered results
            $totalCount = count($filteredTransactions);
            $totalPages = ceil($totalCount / $pageSize);
            $offset = ($page - 1) * $pageSize;
            
            // Get paginated results
            $paymentList = array_slice($filteredTransactions, $offset, $pageSize);

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Payment history fetched successfully'),
                'data' => $paymentList,
                'pagination' => [
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'totalCount' => $totalCount,
                    'totalPages' => $totalPages,
                    'hasNextPage' => $page < $totalPages,
                    'hasPreviousPage' => $page > 1
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionToggleOnlineStatus()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            $vendor->is_online = (int) $decodedData['is_online'];
            $vendor->save();
            if (!$vendor->save()) {
                throw new Exception(Yii::t('app', 'Failed to update vendor online status'));
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor online status updated successfully'),
                'data' => []
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionUpdateLocation()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['latitude']) || !isset($decodedData['longitude']) || !isset($decodedData['location_description'])) {
                throw new Exception(Yii::t('app', 'Missing required fields'));
            }

            $vendor->location_coordinates = $decodedData['latitude'] . ',' . $decodedData['longitude'];
            $vendor->location_description = $decodedData['location_description'];
            $vendor->restrict_to_specific_locations = isset($decodedData['restrict_to_specific_locations']) && $decodedData['restrict_to_specific_locations'] == 1 ? 1 : 0;
            $vendor->specific_locations = isset($decodedData['specific_locations']) ? $decodedData['specific_locations'] : '';
            $vendor->save();

            if (!$vendor->save()) {
                throw new Exception(Yii::t('app', 'Failed to update vendor location'));
            }

            //Add a notification to vendor that their location has been updated
            $notification = new Notifications();
            $notification->device_id = $vendor->device_token;
            $notification->data_values = json_encode(['location_updated' => true]);
            $notification->key_type = 'vendor';
            $notification->key_id = $vendor->id;
            $notification->type = 'location';
            $notification->title = Yii::t('app', 'Location Updated');
            $message = Yii::t('app', 'Your location has been updated successfully to %location%');
            $message = str_replace("%location%", $vendor->location_description, $message);
            $notification->message = $message;
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->not_type = 'success';
            $notification->is_read = 0;
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->save();


            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor location updated successfully'),
                'data' => []
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionMarkAllNotificationsAsRead()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $updated = Notifications::updateAll(
                ['status' => 1, 'is_read' => 1],
                ['key_id' => $vendor->id, 'key_type' => 'vendor', 'is_read' => 0]
            ); 


            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function actionMarkNotificationAsRead()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['notification_id'])) {
                throw new Exception("Missing notification ID");
            }

            $notification = VendorNotifications::find()->where(['id' => $decodedData['notification_id'], 'vendor_id' => $vendor->id])->one();
            if (!$notification) {
                throw new Exception("Notification not found");
            }

            $notification->status = 1;

            if (!$notification->save()) {
                throw new Exception("Failed to mark notification as read");
            }


            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function actionGetNotifications()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['api_token'])) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $decodedData['api_token']])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $notifications = VendorNotifications::find()->where(['vendor_id' => $vendor->id])->orderBy(['id' => SORT_DESC])->all();
            if (!$notifications) {
                throw new Exception("No notifications found");
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Notifications fetched successfully',
                'data' => $notifications
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetOrders($page = 1, $pageSize = 10)
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            //$vendor = Vendors::find()->where(['id' => 2])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            // Get and validate parameters (both required)
            $startDateInput = $decodedData['start_date'] ?? null;
            $endDateInput = $decodedData['end_date'] ?? null;

            if (!$startDateInput || !$endDateInput) {
                throw new Exception("Start date and end date are required");
            }

            if (strtotime($startDateInput) === false || strtotime($endDateInput) === false) {
                throw new Exception("Invalid date format");
            }

            $startDate = date('Y-m-d 00:00:00', strtotime($startDateInput));
            $endDate = date('Y-m-d 23:59:59', strtotime($endDateInput));

            if (strtotime($startDate) > strtotime($endDate)) {
                throw new Exception("Start date must be before end date");
            }

            // New orders (status = 10): created today or scheduled for delivery today
            $todayStart = date('Y-m-d 00:00:00');
            $todayEnd = date('Y-m-d 23:59:59');


            $newRequests = CustomerRequests::find()
                ->where(['vendor_id' => $vendor->id])
                ->andWhere(['status' => 10])
                ->andWhere([
                    'or',
                    ['between', 'delivery_date', $todayStart, $todayEnd],
                    ['between', 'date_created', $todayStart, $todayEnd]
                ])
                ->all();

            $newOrders = [];
            //Handle new orders first
            foreach ($newRequests as $order) {
                $locationCoordinates = explode(',', $order->customerAddress->location_coordinates);
                $newOrders[] = [
                    'id' => $order->id,
                    'customer_name' => $order->customer->alias,
                    'customer_phone' => $order->customer->phone_number,
                    'volume_requested' => $order->volume_requested,
                    'total_amount' => $order->total_amount,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'customer_address' => $order->customerAddress->address,
                    'is_shared_order' => $order->is_shared_request,
                    'latitude' => $locationCoordinates[0],
                    'longitude' => $locationCoordinates[1],
                    'date_created' => $order->date_created,
                    'status' => $order->status
                ];
            }
            // Other statuses (12, 13, 14, 15, 16): apply provided date range
            $orders = CustomerRequests::find()
                ->where(['vendor_id' => $vendor->id])
                ->andWhere(['in', 'status', [12, 13, 14, 15, 16]])
                ->andWhere(['between', 'date_created', $startDate, $endDate])
                ->all();
            // Organize orders by status into separate arrays
            $onTheWayOrders = [];
            $deliveredOrders = [];
            $paymentInitiatedOrders = [];
            $awaitingConfirmationOrders = [];
            $completedPayments = [];

            foreach ($orders as $order) {
                $locationCoordinates = explode(',', $order->customerAddress->location_coordinates);

                switch ($order->status) {
                    case 12:
                        $onTheWayOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 13:
                        $deliveredOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 14:
                        $paymentInitiatedOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 15:
                        $awaitingConfirmationOrders[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                    case 16:
                        $completedPayments[] = [
                            'id' => $order->id,
                            'customer_name' => $order->customer->alias,
                            'customer_phone' => $order->customer->phone_number,
                            'volume_requested' => $order->volume_requested,
                            'total_amount' => $order->total_amount,
                            'delivery_date' => $order->delivery_date,
                            'delivery_notes' => $order->delivery_notes,
                            'customer_address' => $order->customerAddress->address,
                            'is_shared_order' => $order->is_shared_request,
                            'latitude' => $locationCoordinates[0],
                            'longitude' => $locationCoordinates[1],
                            'date_created' => $order->date_created,
                            'status' => $order->status
                        ];
                        break;
                }
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Orders fetched successfully',
                'data' => [
                    'orders' => [
                        'new' => $newOrders,
                        'on_the_way' => $onTheWayOrders,
                        'delivered' => $deliveredOrders,
                        'payment_initiated' => $paymentInitiatedOrders,
                        'awaiting_confirmation' => $awaitingConfirmationOrders,
                        'completed_payments' => $completedPayments
                    ]
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage(),
                'logout' => true
            ]);
        }
    }

    public function actionCancelOrder()
    {
        try {

            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['order_id'])) {
                throw new Exception("Missing required fields");
            }

            $orderId = $decodedData['order_id'];

            $order = CustomerRequests::find()->where(["id" => $orderId, "vendor_id" => $vendor->id])->one();
            if (!$order) {
                throw new Exception("Invalid Request");
            }

            $order->status = StatusCodes::NEW_CUSTOMER_REQUEST_OPEN;
            $order->cancel_track = $order->cancel_track + 1;
            if ($order->save()) {
                $template = Helpers::getSMSTemplate('ORDER_CANCELED_BY_VENDOR', $order->language);
                $template = str_replace("%name%", $order->customer->alias, $template);
                $template = str_replace("%vendor_name%", $vendor->other_names, $template);
                $template = str_replace("%volume%", $order->volume_requested, $template);
                $sms = new OutboundSms();
                $sms->msisdn = $order->customer->phone_number;
                $sms->message = $template;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                if (!$sms->save()) {
                    throw new Exception("SMS ERROR");
                }

                //Add a cancel notification
                $notification = new Notifications();
                $notification->device_id = $order->customer->device_token;
                $notification->data_values = json_encode(['order_id' => $order->id, 'status' => 12]);
                $notification->key_type = 'customer';
                $notification->key_id = $order->customer->id;
                $notification->type = 'order';
                $notification->title = 'Order Cancelled';
                $notification->message = 'Your order with ' . $order->vendor->first_name . ' for ' . $order->volume_requested . ' barrels was rejected by the vendor. You will receive confirmation from another vendor to continue.';
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();
            } else {
                throw new Exception("ORDER SAVE ERROR");
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Order has been cancelled successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionAcceptOrder()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception(Yii::t('app', 'Missing or invalid Authorization header'));
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception(Yii::t('app', 'Missing API token'));
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'));
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor is inactive'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['order_id'])) {
                throw new Exception(Yii::t('app', 'Missing required fields'));
            }

            $orderId = $decodedData['order_id'];

            $order = CustomerRequests::find()->where(["id" => $orderId, "vendor_id" => $vendor->id])->one();
            $openOrder = null;
            if (!$order) {
                //Check if this is an open order
                $openOrder = CustomerRequests::find()->where(["id" => $orderId, "status" => 11])->one();
                if (!$openOrder) {
                    throw new Exception(Yii::t('app', 'Invalid Request'));
                }
                $order = $openOrder;
                $order->vendor_id = $vendor->id;
            }

            if ($order->status == StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST) {
                throw new Exception(Yii::t('app', 'Order is already accepted'));
            }

            if ($vendor->available_volume < $order->volume_requested) {
                throw new Exception(Yii::t('app', 'Vendor has no available volume to deliver this order'));
            }

            $order->status = StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST;
            if ($order->save()) {           
                //Message to customer
                // Set language based on order's language attribute
                $originalLanguage = Yii::$app->language;    
                if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                    Yii::$app->language = $order->language;
                }
                $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_ACCEPTED');
                $customerTemplate = Yii::t('app', $templateMessage);
                $customerTemplate = str_replace("%name%", $order->customer->alias, $customerTemplate);
                $customerTemplate = str_replace("%vendor_name%", $vendor->other_names, $customerTemplate);
                $customerTemplate = str_replace("%volume%", $order->volume_requested, $customerTemplate);
                $customerTemplate = str_replace("%date%", date('Y-m-d', strtotime($order->delivery_date)), $customerTemplate);

                // Restore original language
                Yii::$app->language = $originalLanguage;
                $sms = new OutboundSms();
                $sms->msisdn = $order->customer->phone_number;
                $sms->message = $customerTemplate;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = Yii::t('app', 'pending');
                $sms->save();

                //Message to vendor
                $template = Helpers::getSMSTemplate('VENDOR_ORDER_ACCEPTED');
                $template = str_replace("%name%", $order->vendor->first_name, $template);
                $template = str_replace("%volume%", $order->volume_requested, $template);
                $template = str_replace("%address%", $order->customerAddress->address, $template);
                $template = str_replace("%date%", date('Y-m-d', strtotime($order->delivery_date)), $template);
                $template = str_replace("%ref%", $order->id, $template);
                $sms = new OutboundSms();
                $sms->msisdn = $order->vendor->mobile_number;
                $sms->message = $template;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();

                //Log a notification for the customer
                $notification = new Notifications();
                $notification->device_id = $order->customer->device_token;
                $notification->data_values = json_encode(['order_id' => $order->id, 'status' => 12]);
                $notification->key_type = 'customer';
                $notification->key_id = $order->customer->id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order Accepted');
                $notification->message = $customerTemplate;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();

                //Log a vendor notification 
                $notification = new Notifications();
                $notification->device_id = $order->vendor->device_token;
                $notification->data_values = json_encode(['order_id' => $order->id, 'status' => 12]);
                $notification->key_type = 'vendor';
                $notification->key_id = $order->vendor->id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order Accepted');
                $notification->message = $template;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();

                //Add a vendor_delivery
                $customerCoordinates = explode(',', $order->customerAddress->location_coordinates);
                $vendorCoordinates = explode(',', $order->vendor->location_coordinates);
                $delivery = new VendorDeliveries();
                $delivery->cr_id = $order->id;
                $delivery->end_lat = $customerCoordinates[0];
                $delivery->end_lng = $customerCoordinates[1];
                $delivery->start_lat = $vendorCoordinates[0];
                $delivery->start_lng = $vendorCoordinates[1];
                $delivery->distance_km = Helpers::haversineDistance($delivery->end_lat, $delivery->end_lng, $delivery->start_lat, $delivery->start_lng);
                //Points
                $groupId = $order->vendor->vendor_group;
                $distance = $delivery->distance_km;
                $pointsRule = VendorGroupPoints::find()
                    ->where(['group_id' => $groupId])
                    ->andWhere(['<=', 'min_distance', $distance])
                    ->andWhere(['or', ['max_distance' => null], ['>=', 'max_distance', $distance]])
                    ->orderBy(['min_distance' => SORT_DESC])
                    ->one();
                $delivery->points_earned = $pointsRule ? round($distance * (float) $pointsRule->points_per_km, 2) : 0;
                $delivery->delivery_date = $order->delivery_date;
                $delivery->created_at = Date('Y-m-d');
                $delivery->status = 0;
                if (!$delivery->save()) {
                    error_log(json_encode($delivery->errors));
                }
            } else {
                throw new Exception(Yii::t('app', 'Order Save Error'));
            }


            $locationCoordinates = explode(',', $order->customerAddress->location_coordinates);
            $vendorCoordinates = explode(',', $vendor->location_coordinates);
            $distance = Helpers::haversineDistance($vendorCoordinates[0], $vendorCoordinates[1], $locationCoordinates[0], $locationCoordinates[1]);
            //Set distance to 1 decimal place
            $distance = number_format($distance, 1);

            //Log a notification for the vendor
            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Order has been accepted successfully'),
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer->alias,
                    'customer_phone' => $order->customer->phone_number,
                    'customer_address' => $order->customerAddress->address,
                    'latitude' => $customerCoordinates[0],
                    'longitude' => $customerCoordinates[1],
                    'volume_requested' => $order->volume_requested,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'date_created' => $order->date_created,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'is_shared_order' => $order->is_shared_request,
                    'distance' => $distance,
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionRejectOrder()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['order_id'])) {
                throw new Exception("Missing required fields");
            }

            $orderId = $decodedData['order_id'];
            $reason = $decodedData['reason'] ?? null;

            $order = CustomerRequests::find()->where(["id" => $orderId, "vendor_id" => $vendor->id])->one();
            if (!$order) {
                throw new Exception("Invalid Request");
            }

            $order->cancel_track = $order->cancel_track + 1;
            $order->status = StatusCodes::NEW_CUSTOMER_REQUEST_OPEN;
            $order->cancel_reason = $reason;
            if ($order->save()) {
                //Send SMS to customer 
                $originalLanguage = Yii::$app->language;
                if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                    Yii::$app->language = $order->language;
                }

                $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_REJECTED');
                // Translate the template text using the order's language, then replace placeholders
                $translatedTemplate = Yii::t('app', $templateMessage);
                $customerTemplate = str_replace(
                    ['%name%', '%vendor_name%', '%volume%', '%date%'],
                    [
                        $order->customer->alias,
                        $vendor->other_names,
                        $order->volume_requested,
                        date('Y-m-d', strtotime($order->delivery_date))
                    ],
                    $translatedTemplate
                );

                // Restore original language
                Yii::$app->language = $originalLanguage;

                $sms = new OutboundSms();
                $sms->msisdn = $order->customer->phone_number;
                $sms->message = $customerTemplate;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();


                //Log a notification for the customer
                $notification = new Notifications();
                $notification->device_id = $order->customer->device_token;
                $notification->data_values = json_encode(['order_id' => $order->id, 'status' => 12]);
                $notification->key_type = 'customer';
                $notification->key_id = $order->customer->id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order Declined');
                $notification->message = $customerTemplate;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();
            } else {
                throw new Exception("Order Save Error");
            }

            $this->setHeader(ApiController::STATUS_OK);
            $coordinates = explode(',', $order->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Order has been declined successfully'),
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer->alias,
                    'customer_phone' => $order->customer->phone_number,
                    'customer_address' => $order->customerAddress->address,
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'volume_requested' => $order->volume_requested,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'date_created' => $order->date_created,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'is_shared_order' => $order->is_shared_request,
                    'can_pick' => false
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetPayments($page = 1, $pageSize = 10, $status = 'all', $start_date = null, $end_date = null)
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            //$vendor = Vendors::findOne(2);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            // Get and validate parameters
            $pageSize = Yii::$app->request->get('pageSize', 10);
            $page = Yii::$app->request->get('page', 1) - 1;
            $statusParam = Yii::$app->request->get('status', 'all');
            $startDate = Yii::$app->request->get('start_date');
            $endDate = Yii::$app->request->get('end_date');

            // Build query
            $payments = Payments::find()
                ->joinWith('request')
                ->where(['customer_requests.vendor_id' => $vendor->id]);

            // Handle status filter
            if ($statusParam !== 'all') {
                $statuses = explode(',', $statusParam);
                $statuses = array_map('intval', $statuses); // Convert to integers
                $payments = $payments->andWhere(['payments.status' => $statuses]);
            }

            // Handle date range filter
            if ($startDate) {
                $startDate = date('Y-m-d H:i:s', strtotime($startDate));
                $payments = $payments->andWhere(['>=', 'payments.date_created', $startDate]);
            }
            if ($endDate) {
                $endDate = date('Y-m-d H:i:s', strtotime($endDate));
                $payments = $payments->andWhere(['<=', 'payments.date_created', $endDate]);
            }

            // Get total count for pagination
            $totalCount = $payments->count();

            // Apply pagination
            $payments = $payments->orderBy(['payments.id' => SORT_DESC])
                ->limit($pageSize)
                ->offset($page * $pageSize)
                ->all();

            $data = [];
            foreach ($payments as $payment) {
                $address_coordinates = explode(',', $payment->request->customerAddress->location_coordinates);
                $data[] = [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'description' => $payment->description,
                    'payment_method' => [
                        'name' => $payment->paymentMethod->name,
                        'description' => $payment->paymentMethod->description,
                        'image' => 'images/logos/' . $payment->paymentMethod->image
                    ],
                    'status' => StatusCodes::getPaymentStatusText($payment->status),
                    'date_created' => $payment->date_created,
                    'customer_request' => [
                        'id' => $payment->request->id,
                        'total_amount' => $payment->request->total_amount,
                        'volume_requested' => $payment->request->volume_requested,
                        'status' => $payment->request->status,
                        'date_created' => $payment->request->date_created,
                        'customer_name' => $payment->request->customer->alias,
                        'customer_phone' => $payment->request->customer->phone_number,
                        'customer_address' => $payment->request->customerAddress->address,
                        'vendor' => [
                            'id' => $payment->request->vendor_id,
                            'first_name' => $payment->request->vendor->first_name,
                            'other_names' => $payment->request->vendor->other_names,
                            'mobile_number' => $payment->request->vendor->mobile_number,
                            'residential_location' => $payment->request->vendor->residential_location,
                            'location_coordinates' => $payment->request->vendor->location_coordinates,
                            'tank_volume' => $payment->request->vendor->tank_volume,
                            'isFavorite' => true,
                            'rating' => $payment->request->vendor->rating,
                            'dp' => $payment->request->vendor->display_pic ?? 'images/water_tank.png',
                            'vendor_group' => [
                                'id' => $payment->request->vendor->vendor_group,
                                'name' => $payment->request->vendor->vendorGroup->name,
                                'description' => $payment->request->vendor->vendorGroup->description
                            ]
                        ],
                        'customer' => [
                            'alias' => $payment->request->customer->alias,
                            'phoneNumber' => $payment->request->customer->phone_number,
                            'address' => [
                                'id' => $payment->request->customer_address,
                                'address' => $payment->request->customerAddress->address,
                                'latitude' => $address_coordinates[0],
                                'longitude' => $address_coordinates[1],
                                'default' => $payment->request->customer_address == $payment->request->customer->primary_address ? true : false,
                                'user_id' => $payment->request->customer->id
                            ]
                        ]
                    ]
                ];
            }

            if (!$payments) {
                $this->setHeader(ApiController::STATUS_OK);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Payments fetched successfully',
                    'data' => [
                        'payments' => [],
                        'currentPage' => 1,
                        'has_more' => false,
                        'total_count' => $totalCount
                    ]
                ]);
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Payments fetched successfully',
                'data' => [
                    'payments' => $data,
                    'currentPage' => $page + 1,
                    'has_more' => ($page + 1) * $pageSize < $totalCount,
                    'total_count' => $totalCount
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage(),
                'logout' => true
            ]);
        }
    }

    public function actionCreateComplaint()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['api_token'])) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $decodedData['api_token']])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            if (!isset($decodedData['complaint_type'])) {
                throw new Exception("Missing complaint type");
            }

            if (!isset($decodedData['complaint_description'])) {
                throw new Exception("Missing complaint description");
            }

            if (!isset($decodedData['complaint_date'])) {
                throw new Exception("Missing complaint date");
            }

            $complaint = new Complaints();
            $complaint->order_id = $decodedData['order_id'] ?? null;
            $complaint->customer_id = $decodedData['customer_id'] ?? null;
            $complaint->complaint_type = $decodedData['complaint_type'];
            //@todo fix this
            $complaint->complaint_description = $decodedData['complaint_description'];
            $complaint->complaint_date = $decodedData['complaint_date'];



        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionUpdateProfile()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            // Handle multipart/form-data from Flutter app
            $postData = Yii::$app->request->post();

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            // Extract fields from POST data (matching Flutter field names)
            $phoneNumber = isset($postData['phoneNumber']) ? Helpers::formatMsisdn($postData['phoneNumber']) : null;
            $firstName = $postData['firstName'] ?? null;
            $otherNames = $postData['otherNames'] ?? null;
            $tankVolume = isset($postData['tankVolume']) && !empty($postData['tankVolume']) ? (int) $postData['tankVolume'] : null;
            $vehicleRegistration = $postData['vehicleRegistration'] ?? null;
            $priceOfWater = isset($postData['priceOfWater']) && !empty($postData['priceOfWater']) ? $postData['priceOfWater'] : null;
            $operatingHours = $postData['operatingHours'] ?? null;
            $moq = $postData['moq'] ?? null;

            //Find if has pending update
            $vendorUpdate = VendorUpdates::find()->where(['vendor_id' => $vendor->id, 'status' => 'pending'])->one();
            if ($vendorUpdate) {
                $coordinates = explode(',', $vendor->location_coordinates);
                $this->setHeader(ApiController::STATUS_OK);
                return $this->asJson([
                    'status' => false,
                    'message' => 'Vendor profile update has a pending review',
                    'data' => [
                        'pending' => true
                    ]
                ]);
            }

            $vendorUpdate = new VendorUpdates();
            $vendorUpdate->vendor_id = $vendor->id;
            $vendorUpdate->first_name = $firstName;
            $vendorUpdate->other_names = $otherNames;
            $vendorUpdate->mobile_number = $phoneNumber;
            $vendorUpdate->tank_volume = "$tankVolume";
            $vendorUpdate->operating_hours = $operatingHours;
            $vendorUpdate->vehicle_registration_number = $vehicleRegistration;
            $vendorUpdate->price_of_water = $priceOfWater;
            $vendorUpdate->minimum_order_qty = $moq;
            $vendorUpdate->status = 'pending';
            $vendorUpdate->date_created = $vendorUpdate->date_modified = Date('Y-m-d H:i:s');

            // Handle vehicle image upload
            $vehicleImage = UploadedFile::getInstanceByName('vehicleImage');
            if ($vehicleImage) {
                // Use backend webroot path
                $uploadDir = 'uploads/vendors/';
                $backendWebRoot = Yii::getAlias('@backend') . '/web';
                $uploadPath = $backendWebRoot . '/' . $uploadDir;

                // Check if directory exists, if not create it
                if (!is_dir($uploadPath)) {
                    // Suppress errors if directory already exists (race condition)
                    @mkdir($uploadPath, 0755, true);

                    // Verify directory was created or already exists
                    if (!is_dir($uploadPath)) {
                        throw new Exception(Yii::t('app', 'Failed to create upload directory. Please contact support.'));
                    }
                }

                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    throw new Exception(Yii::t('app', 'Upload directory is not writable. Please contact support.'));
                }

                $fileName = uniqid() . '.' . $vehicleImage->extension;
                // Use absolute path for saveAs since we're in API module
                $filePath = $uploadPath . $fileName;

                if (!$vehicleImage->saveAs($filePath)) {
                    throw new Exception(Yii::t('app', 'Failed to save uploaded image. Please try again.'));
                }

                // Save relative path to database (relative to backend web root) to dp field
                $vendorUpdate->dp = $uploadDir . $fileName;
            }

            if ($vendorUpdate->save()) {
                $coordinates = explode(',', $vendor->location_coordinates);
                $this->setHeader(ApiController::STATUS_OK);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Vendor profile update request sent successfully',
                    'data' => []
                ]);
            } else {
                error_log(print_r($vendorUpdate->errors, true));
                throw new Exception("Vendor profile update failed " . json_encode($vendorUpdate->errors));
            }


        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionWalletBalance()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Wallet balance fetched successfully',
                'data' => [
                    'balance' => number_format($vendor->wallet_balance, 2)
                ]
            ]);


        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionWithdrawBalance()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            if (!isset($decodedData['amount'])) {
                throw new Exception("Missing amount");
            }

            if ($decodedData['amount'] > $vendor->wallet_balance) {
                throw new Exception("Insufficient balance");
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $vendor->wallet_balance -= $decodedData['amount'];
                if (!$vendor->save()) {
                    throw new Exception("Failed to update wallet balance");
                }

                //Add a withdrawal transaction 
                $withdrawal = new \backend\models\Withdrawals();
                $withdrawal->from_type = 'vendor';
                $withdrawal->from_id = $vendor->id;
                $withdrawal->amount = $decodedData['amount'];
                $withdrawal->status = 'pending';
                $withdrawal->created_at = $withdrawal->updated_at = Date('Y-m-d H:i:s');
                if (!$withdrawal->save()) {
                    throw new Exception("Failed to add withdrawal transaction");
                }

                // If we get here, commit the transaction
                $transaction->commit();

                $this->setHeader(ApiController::STATUS_OK);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Balance withdrawn successfully',
                    'data' => [
                        'balance' => number_format($vendor->wallet_balance, 2)
                    ]
                ]);
            } catch (Exception $e) {
                // If anything fails, rollback the transaction
                $transaction->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionUpdateTankVolume()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $vendor->available_volume += $decodedData['tank_volume'];
            if ($vendor->save()) {
                $refill = new VendorRefills();
                $refill->vendor_id = $vendor->id;
                $refill->kiosk = $decodedData['kiosk'];
                $refill->volume = $decodedData['tank_volume'];
                $refill->status = StatusCodes::ACTIVE_STATUS;
                $refill->created_at = $refill->update_at = date('Y-m-d H:i:s');
                $refill->save();


                $this->setHeader(ApiController::STATUS_OK);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Tank volume updated successfully',
                    'data' => [
                        'volume' => $vendor->available_volume
                    ]
                ]);

            } else {
                throw new Exception('Vendor details could not be saved');
            }


        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetWithdrawals()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            // Get and validate parameters
            $pageSize = Yii::$app->request->get('pageSize', 10);
            $page = Yii::$app->request->get('page', 1) - 1;
            $statusParam = Yii::$app->request->get('status', 'all');
            $startDate = Yii::$app->request->get('start_date');
            $endDate = Yii::$app->request->get('end_date');

            // Build query
            $withdrawals = \backend\models\Withdrawals::find()
                ->where(['from_type' => 'vendor', 'from_id' => $vendor->id]);

            // Handle status filter
            if ($statusParam !== 'all') {
                $statuses = explode(',', $statusParam);
                $withdrawals = $withdrawals->andWhere(['status' => $statuses]);
            }

            // Handle date range filter
            if ($startDate) {
                $startDate = date('Y-m-d H:i:s', strtotime($startDate));
                $withdrawals = $withdrawals->andWhere(['>=', 'created_at', $startDate]);
            }
            if ($endDate) {
                $endDate = date('Y-m-d H:i:s', strtotime($endDate));
                $withdrawals = $withdrawals->andWhere(['<=', 'created_at', $endDate]);
            }

            // Get total count for pagination
            $totalCount = $withdrawals->count();

            // Apply pagination
            $withdrawals = $withdrawals->orderBy(['id' => SORT_DESC])
                ->limit($pageSize)
                ->offset($page * $pageSize)
                ->all();

            $data = [];
            foreach ($withdrawals as $withdrawal) {
                $data[] = [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at,
                ];
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Withdrawals fetched successfully',
                'data' => [
                    'withdrawals' => $data,
                    'currentPage' => $page + 1,
                    'has_more' => ($page + 1) * $pageSize < $totalCount,
                    'total_count' => $totalCount
                ]
            ]);

        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionMarkOrderAsPaid()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $order = CustomerRequests::findOne(['id' => $decodedData['order_id'], 'vendor_id' => $vendor->id]);
            if (!$order) {
                throw new Exception("Order not found");
            }

            // if ($order->status != StatusCodes::DELIVERED_CUSTOMER_REQUEST && $order->status != StatusCodes::PAYMENT_AWAITING_CONFIRMATION && $order->status != StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST) {
            //     throw new Exception("Order is not in payment awaiting confirmation status");
            // }

            $paymentMethod = $decodedData['payment_method_id'];
            if(!$paymentMethod) {
                throw new Exception("Payment method is required");
            }

            $paymentMethod = PaymentMethods::findOne(['id' => $paymentMethod]);
            if (!$paymentMethod) {
                throw new Exception("Payment method not found");
            }

            $payment = Payments::findOne(['request_id' => $order->id]);
            if (!$payment) {
                //Create a new payment
                $payment = new Payments();
                $payment->request_id = $order->id;
                $payment->amount = $order->total_amount;
                $payment->description = 'Payment for order ' . $order->id;
                $payment->phone_number = $order->customer->phone_number;
                $payment->payment_method_id = $paymentMethod->id;
                $payment->status = StatusCodes::PAYMENT_SUCCESS;
                $payment->date_created = $payment->date_modified = Date('Y-m-d H:i:s');
                $payment->save();
            } else {
                $payment->payment_method_id = $paymentMethod->id;
                $payment->status = StatusCodes::PAYMENT_SUCCESS;
                $payment->date_modified = Date('Y-m-d H:i:s');
                $payment->save();
            }

            $order->status = StatusCodes::COMPLETED_CUSTOMER_REQUEST;
            $order->payment_id = $payment->id;
            if (!$order->save()) {
                throw new Exception("Failed to update order status");
            }

            //Send notifications for customer
            $originalLanguage = Yii::$app->language;
            if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                Yii::$app->language = $order->language;
            }

            $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_MARKED_AS_PAID');
            $customerTemplate = str_replace(
                ['%name%', '%vendor_name%', '%volume%', '%date%'],
                [
                    $order->customer->alias,
                    $order->vendor->first_name,
                    $order->volume_requested,
                    date('Y-m-d', strtotime($order->delivery_date))
                ],
                $templateMessage
            );

            //Send customer notifications
            $sms = new OutboundSms();
            $sms->msisdn = $order->customer->phone_number;
            $sms->message = $customerTemplate;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();


            //@todo Log notification to customer
            $notification = new Notifications();
            $notification->device_id = $order->customer->device_token;
            $notification->key_type = 'customer';
            $notification->key_id = $order->customer_id;
            $notification->type = 'order';
            $notification->title = Yii::t('app', 'Order Marked as Paid');
            $notification->message = $customerTemplate;
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->not_type = 'success';
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->data_values = json_encode([
                'request_id' => $order->id,
                'deep_link' => "dhaamiye_user://order/" . $order->id,
                'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST
            ]);
            $notification->save();

            Yii::$app->language = $originalLanguage;

            //Send SMS to customer

            //Add a notification for the customer
            $notification = new Notifications();
            $notification->device_id = $order->customer->device_token;
            $notification->data_values = json_encode(['order_id' => $order->id, 'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST]);
            $notification->key_type = 'customer';
            $notification->key_id = $order->customer->id;
            $notification->type = 'order';
            $notification->title = 'Order Marked as Paid';
            $notification->message = 'Your order with ' . $order->customer->alias . ' for ' . $order->volume_requested . ' barrels has been marked as paid.';
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->not_type = 'success';
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->save();

            //mark reward points also as complete
            $vendorPoints = VendorDeliveries::find()->where(['cr_id' => $order->id])->one();
            if ($vendorPoints && $vendorPoints->status == 0) {
                $vendorPoints->status = 1;
                $vendorPoints->save();

                //Log a notification for this delivery
                $notification = new Notifications();
                $notification->device_id = $order->vendor->device_token;
                $notification->data_values = json_encode(['order_id' => $order->id, 'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST]);
                $notification->key_type = 'vendor';
                $notification->key_id = $order->vendor->id;
                $notification->type = 'points';
                $notification->title = 'Rewards Earned';
                $notification->message = 'You earned ' . $vendorPoints->points_earned . ' Demo points for delivery DEMO#' . $order->id . '. Distance covered was ' . $vendorPoints->distance_km . ' KM';
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();
            }

            $this->setHeader(ApiController::STATUS_OK);
            $coordinates = explode(',', $order->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => 'Order marked as paid successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'customer_name' => $order->customer->alias,
                        'customer_phone' => $order->customer->phone_number,
                        'customer_address' => $order->customerAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                        'volume_requested' => $order->volume_requested,
                        'delivery_date' => $order->delivery_date,
                        'delivery_notes' => $order->delivery_notes,
                        'date_created' => $order->date_created,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'is_shared_order' => $order->is_shared_request,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionMarkOrderOnTheWay()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }


            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['order_id'])) {
                throw new Exception("Missing required fields");
            }

            $orderId = $decodedData['order_id'];

            $order = CustomerRequests::find()->where(["id" => $orderId, "vendor_id" => $vendor->id])->one();
            if (!$order) {
                throw new Exception("Invalid Request");
            }

            if ($vendor->available_volume < $order->volume_requested) {
                throw new Exception("Vendor has no available volume to deliver this order");
            }

            $order->status = StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST;
            if ($order->save()) {

                //Send SMS to customer 
                $originalLanguage = Yii::$app->language;
                if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                    Yii::$app->language = $order->language;
                }

                $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_ON_THE_WAY');
                // Translate the template text using the order's language, then replace placeholders
                $translatedTemplate = Yii::t('app', $templateMessage);
                $customerTemplate = str_replace(
                    ['%name%', '%vendor_name%', '%volume%', '%date%'],
                    [
                        $order->customer->alias,
                        $vendor->other_names,
                        $order->volume_requested,
                        date('Y-m-d', strtotime($order->delivery_date))
                    ],
                    $translatedTemplate
                );



                //Send SMS to customer 
                $sms = new OutboundSms();
                $sms->msisdn = $order->customer->phone_number;
                $sms->message = $customerTemplate;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();


                //@todo Log notification to customer
                $notification = new Notifications();
                $notification->device_id = $order->customer->device_token;
                $notification->key_type = 'customer';
                $notification->key_id = $order->customer_id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order On The Way');
                $notification->message = $customerTemplate;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->data_values = json_encode([
                    'request_id' => $order->id,
                    'deep_link' => "dhaamiye://order/" . $order->id,
                    'status' => StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST
                ]);
                $notification->save();
                //Restore original language
                Yii::$app->language = $originalLanguage;

            } else {
                throw new Exception("Order Save Error");
            }

            $this->setHeader(ApiController::STATUS_OK);
            $coordinates = explode(',', $order->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => 'Order has been marked as on the way successfully',
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer->alias,
                    'customer_phone' => $order->customer->phone_number,
                    'customer_address' => $order->customerAddress->address,
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'volume_requested' => $order->volume_requested,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'date_created' => $order->date_created,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'is_shared_order' => $order->is_shared_request,
                    'tank_volume' => $vendor->available_volume,
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }



    public function actionMarkOrderAsDelivered()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }


            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['order_id'])) {
                throw new Exception("Missing required fields");
            }

            $orderId = $decodedData['order_id'];

            $order = CustomerRequests::find()->where(["id" => $orderId, "vendor_id" => $vendor->id])->one();
            if (!$order) {
                throw new Exception("Invalid Request");
            }

            if ($vendor->available_volume < $order->volume_requested) {
                throw new Exception("Vendor has no available volume to deliver this order");
            }

            $order->status = StatusCodes::DELIVERED_CUSTOMER_REQUEST;
            if ($order->save()) {

                //Decrement the vendor's tank volume
                $vendor->available_volume = $vendor->available_volume - $order->volume_requested;
                $vendor->save();

                //Send SMS to customer 
                $originalLanguage = Yii::$app->language;
                if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                    Yii::$app->language = $order->language;
                }

                $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_DELIVERED');
                // Translate the template text using the order's language, then replace placeholders
                $translatedTemplate = Yii::t('app', $templateMessage);
                $customerTemplate = str_replace(
                    ['%name%', '%vendor_name%', '%volume%', '%date%'],
                    [
                        $order->customer->alias,
                        $vendor->other_names,
                        $order->volume_requested,
                        date('Y-m-d', strtotime($order->delivery_date))
                    ],
                    $translatedTemplate
                );



                //Send SMS to customer 
                $sms = new OutboundSms();
                $sms->msisdn = $order->customer->phone_number;
                $sms->message = $customerTemplate;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();


                //@todo Log notification to customer
                $notification = new Notifications();
                $notification->device_id = $order->customer->device_token;
                $notification->key_type = 'customer';
                $notification->key_id = $order->customer_id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order Delivered');
                $notification->message = $customerTemplate;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->data_values = json_encode([
                    'request_id' => $order->id,
                    'deep_link' => "dhaamiye_user://order/" . $order->id,
                    'status' => StatusCodes::DELIVERED_CUSTOMER_REQUEST
                ]);
                $notification->save();
                //Restore original language
                Yii::$app->language = $originalLanguage;


                //Send Vendor notifications
                $templateMessage = Helpers::getSMSTemplate('VENDOR_ORDER_DELIVERED');
                $vendorTemplate = str_replace(
                    ['%name%', '%volume%', '%address%', '%date%', '%vendor_name%'],
                    [
                        $order->vendor->first_name,
                        $order->volume_requested,
                        $order->customerAddress->address,
                        date('Y-m-d', strtotime($order->delivery_date)),
                        $order->vendor->first_name
                    ],
                    $translatedTemplate
                );
                $notification = new Notifications();
                $notification->device_id = $order->vendor->device_token;
                $notification->key_type = 'vendor';
                $notification->key_id = $order->vendor_id;
                $notification->type = 'order';
                $notification->title = Yii::t('app', 'Order Delivered');
                $notification->message = $vendorTemplate;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->data_values = json_encode([
                    'request_id' => $order->id,
                    'deep_link' => "dhaamiye_user://order/" . $order->id,
                    'status' => StatusCodes::DELIVERED_CUSTOMER_REQUEST
                ]);
                $notification->save();

            } else {
                throw new Exception("Order Save Error");
            }

            $this->setHeader(ApiController::STATUS_OK);
            $coordinates = explode(',', $order->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => 'Order has been marked as delivered successfully',
                'data' => [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer->alias,
                    'customer_phone' => $order->customer->phone_number,
                    'customer_address' => $order->customerAddress->address,
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'volume_requested' => $order->volume_requested,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'date_created' => $order->date_created,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'is_shared_order' => $order->is_shared_request,
                    'tank_volume' => $vendor->available_volume,
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function actionMarkUnpaidOrderAsPaid()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $order = CustomerRequests::findOne(['id' => $decodedData['order_id'], 'vendor_id' => $vendor->id]);
            if (!$order || $order->status != StatusCodes::PAYMENT_PAY_LATER_CUSTOMER_REQUEST) {
                throw new Exception("Order not found");
            }

            // if ($order->status != StatusCodes::DELIVERED_CUSTOMER_REQUEST && $order->status != StatusCodes::PAYMENT_AWAITING_CONFIRMATION && $order->status != StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST) {
            //     throw new Exception("Order is not in payment awaiting confirmation status");
            // }


            $payment = Payments::findOne(['request_id' => $order->id]);
            if (!$payment) {
                //Create a new payment
                $payment = new Payments();
                $payment->request_id = $order->id;
                $payment->amount = $order->total_amount;
                $payment->description = 'Payment for order ' . $order->id;
                $payment->phone_number = $order->customer->phone_number;
                $payment->payment_method_id = 3;
                $payment->status = StatusCodes::PAYMENT_SUCCESS;
                $payment->date_created = $payment->date_modified = Date('Y-m-d H:i:s');
                $payment->save();
            } else {
                $payment->payment_method_id = 3;
                $payment->status = StatusCodes::PAYMENT_SUCCESS;
                $payment->date_modified = Date('Y-m-d H:i:s');
                $payment->save();
            }

            $order->status = StatusCodes::COMPLETED_CUSTOMER_REQUEST;
            if (!$order->save()) {
                throw new Exception("Failed to update order status");
            }

            //Send notifications for customer
            $originalLanguage = Yii::$app->language;
            if (isset($order->language) && in_array($order->language, ['en', 'so'])) {
                Yii::$app->language = $order->language;
            }

            $templateMessage = Helpers::getSMSTemplate('CUSTOMER_ORDER_MARKED_AS_PAID');
            $customerTemplate = str_replace(
                ['%name%', '%vendor_name%', '%volume%', '%date%'],
                [
                    $order->customer->alias,
                    $order->vendor->first_name,
                    $order->volume_requested,
                    date('Y-m-d', strtotime($order->delivery_date))
                ],
                $templateMessage
            );

            //Send customer notifications
            $sms = new OutboundSms();
            $sms->msisdn = $order->customer->phone_number;
            $sms->message = $customerTemplate;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();


            //@todo Log notification to customer
            $notification = new Notifications();
            $notification->device_id = $order->customer->device_token;
            $notification->key_type = 'customer';
            $notification->key_id = $order->customer_id;
            $notification->type = 'order';
            $notification->title = Yii::t('app', 'Order Marked as Paid');
            $notification->message = $customerTemplate;
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->not_type = 'success';
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->data_values = json_encode([
                'request_id' => $order->id,
                'deep_link' => "dhaamiye_user://order/" . $order->id,
                'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST
            ]);
            $notification->save();

            Yii::$app->language = $originalLanguage;

            //Send SMS to customer

            //Add a notification for the vendor
            $notification = new Notifications();
            $notification->device_id = $order->customer->device_token;
            $notification->data_values = json_encode(['order_id' => $order->id, 'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST]);
            $notification->key_type = 'customer';
            $notification->key_id = $order->customer->id;
            $notification->type = 'order';
            $notification->title = 'Order Marked as Paid';
            $notification->message = 'Your order with ' . $order->customer->alias . ' for ' . $order->volume_requested . ' barrels has been marked as paid.';
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->not_type = 'success';
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->save();

            $this->setHeader(ApiController::STATUS_OK);
            $coordinates = explode(',', $order->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => 'Order marked as paid successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'customer_name' => $order->customer->alias,
                        'customer_phone' => $order->customer->phone_number,
                        'customer_address' => $order->customerAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                        'volume_requested' => $order->volume_requested,
                        'delivery_date' => $order->delivery_date,
                        'delivery_notes' => $order->delivery_notes,
                        'date_created' => $order->date_created,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'is_shared_order' => $order->is_shared_request,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionGetComplaints()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');
            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::find()->where(['api_token' => $apiToken])->one();
            if (!$vendor) {
                throw new Exception("Vendor not found");
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception("Vendor is inactive");
            }

            $complaints = Complaints::find()
                ->where(['customer_id' => $vendor->id])
                ->andWhere(['type' => Complaints::TYPE_VENDORS])
                ->orderBy(['id' => SORT_DESC])
                ->all();
            $complaintsList = [];
            foreach ($complaints as $complaint) {
                $complaintsList[] = [
                    'id' => $complaint->id,
                    'category' => $complaint->category,
                    'title' => $complaint->title,
                    'description' => $complaint->description,
                    'status' => $complaint->status,
                    'resolution_notes' => $complaint->resolution_notes,
                    'created_at' => $complaint->date_created,
                ];
            }

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => 'Complaints fetched successfully',
                'data' => $complaintsList
            ]);
        } catch (Exception $e) {
            $this->setHeader(ApiController::STATUS_ERROR);
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actionSubmitComplaint()
    {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $vendor = Vendors::findOne(['api_token' => $apiToken]);
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['title']) || !isset($decodedData['description'])) {
                throw new Exception("Missing complaint title or description");
            }

            $orderId = isset($decodedData['order_id']) ? $decodedData['order_id'] : null;
            $title = $decodedData['title'];
            $description = $decodedData['description'];
            $category = $decodedData['category_code'];
            $type = Complaints::TYPE_VENDORS;

            $complaint = new Complaints();
            $complaint->customer_id = $vendor->id;
            $complaint->type = $type;
            $complaint->order_id = $orderId;

            $complaint->category = $category;
            $complaint->title = $title;
            $complaint->description = $description;
            $complaint->status = StatusCodes::ACTIVE_STATUS;
            $complaint->date_created = $complaint->date_modified = Date('Y-m-d H:i:s');

            if ($complaint->save()) {
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Complaint submitted successfully',
                    'data' => [
                        'id' => $complaint->id,
                        'category' => $complaint->category,
                        'title' => $complaint->title,
                        'description' => $complaint->description,
                        'response' => $complaint->resolution_notes,
                        'status' => $complaint->status,
                        'created_at' => $complaint->date_created,
                    ]
                ]);
            } else {
                throw new Exception('Error submitting complaint: ' . json_encode($complaint->errors));
            }
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }
}
