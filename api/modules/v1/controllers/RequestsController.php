<?php

namespace api\modules\v1\controllers;

use Exception;
use yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use backend\models\CustomerRequests;
use backend\models\Customers;
use backend\models\Vendors;
use backend\helpers\StatusCodes;
use backend\models\CustomerAddress;
use DateTime;
use backend\models\CustomerFavorites;
use api\helpers\Helpers as ApiHelpers;
use backend\models\OutboundSms;
use backend\models\Ratings;
use backend\models\Complaints;
use backend\models\Notifications;

class RequestsController extends ApiController
{
    //Find a list of customer requests
    public function actionFind($status = StatusCodes::NEW_CUSTOMER_REQUEST, $pageSize = 4, $page = 1)
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

            $user = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $query = CustomerRequests::find()->where('customer_id = :cid', [':cid' => $user->id]);
            $query->andWhere('status in (' . $status . ')');
            // print_r($status);
            // die();

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page' => $page - 1
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ]
                ]
            ]);

            $customerRequests = [];
            $requests = [];
            foreach ($dataProvider->getModels() as $model) {
                $req = $model;

                //Is this a favorite vendor?
                $favorite = false;
                if ($req->vendor_id != null && CustomerFavorites::findOne(['customer_id' => $user->id, 'vendor_id' => $req->vendor_id])) {
                    $favorite = true;
                }
                $address_coordinates = explode(',', $req->customerAddress->location_coordinates);
                $data = [
                    'id' => $req->id,
                    'total_amount' => $req->total_amount,
                    'volume_requested' => $req->volume_requested,
                    'status' => $req->status,
                    'date_created' => $req->date_created,
                    'delivery_date' => $req->delivery_date,
                    'delivery_notes' => $req->delivery_notes,
                    'is_shared_order' => $req->is_shared_request,
                    'vendor' => $req->vendor_id != null ? [
                        'id' => $req->vendor_id,
                        'first_name' => $req->vendor->first_name,
                        'other_names' => $req->vendor->other_names,
                        'mobile_number' => $req->vendor->mobile_number,
                        'residential_location' => $req->vendor->residential_location,
                        'location_coordinates' => $req->vendor->location_coordinates,
                        'tank_volume' => $req->vendor->tank_volume,
                        'isFavorite' => $favorite,
                        'date_created' => $req->vendor->date_created,
                        'rating' => $req->vendor->rating,
                        'dp' => $req->vendor->display_pic ?? 'images/water_tank.png',
                        'vendor_group' => [
                            'id' => $req->vendor->vendor_group,
                            'name' => $req->vendor->vendorGroup->name,
                            'description' => $req->vendor->vendorGroup->description
                        ]
                    ] : null,
                    'customer' => [
                        'alias' => $req->customer->alias,
                        'phoneNumber' => $req->customer->phone_number,
                        'address' => [
                            'id' => $req->customer_address,
                            'address' => $req->customerAddress->address,
                            'latitude' => $address_coordinates[0],
                            'longitude' => $address_coordinates[1],
                            'default' => $req->customer_address == $req->customer->primary_address ? true : false,
                            'user_id' => $req->customer->id
                        ]
                    ]
                ];

                if ($req->payment_id != '') {
                    $data = array_merge($data, [
                        'payment' => [
                            'description' => $req->payment->description,
                            'amount' => $req->payment->amount,
                            'payment_method' => [
                                'name' => $req->payment->paymentMethod->name,
                                'description' => $req->payment->paymentMethod->description,
                                'code' => $req->payment->paymentMethod->code,
                                'image' => $req->payment->paymentMethod->image
                            ],
                            'payment_status' => [
                                'status' => $req->payment->status,
                                'description' => StatusCodes::getPaymentStatusText($req->payment->status)
                            ],
                        ]
                    ]);
                }

                $requests[] = $data;
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Requests retrieved successfully',
                'data' => array_values($requests)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }

    public function actionCancel()
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

            $user = Customers::findOne(['api_token' => $apiToken, 'status' => StatusCodes::ACTIVE_STATUS]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            $req_id = $decodedData['order_id'];
            $reason = $decodedData['reason'];
            if (!$request = CustomerRequests::find()->where('id = :id AND customer_id = :cid', [':cid' => $user->id, ':id' => $req_id])->one()) {
                throw new Exception(Yii::t('app', 'Customer Request Not Found'));
            }

            $request->cancel_reason = $reason;
            $request->status = StatusCodes::CUSTOMER_CANCELLED_REQUEST;
            $request->save();


            //Add a cancel notification
            $notification = new Notifications();    
            $notification->device_id = $request->customer->device_token;
            $notification->data_values = json_encode(['order_id' => $request->id, 'reason' => $reason]);
            $notification->key_type  = 'customer';
            $notification->key_id = $request->customer->id;
            $notification->type = 'order';
            $notification->title = 'Order Cancelled';
            // If there is a vendor assigned, include their name; otherwise treat it as an open order
            if (!empty($request->vendor_id) && $request->vendor) {
                $notification->message = 'Your order with ' . $request->vendor->first_name . ' for ' . $request->volume_requested . ' barrels has been cancelled';
            } else {
                $notification->message = 'Your open order for ' . $request->volume_requested . ' barrels has been cancelled';
            }
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->is_read = 0;
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
            $notification->not_type = 'alert';
            $notification->save();

            $this->setHeader(200);
            $address_coordinates = explode(',', $request->customerAddress->location_coordinates);
            return $this->asJson([
                'status' => true,
                'message' => 'Request cancelled successfully',
                'data' => [
                    'id' => $request->id,
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }

    public function actionVendorOrders($vendor_id)
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

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $vendor = Vendors::findOne($vendor_id);
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor Not Found'));
            }

            $requests = CustomerRequests::find()->where('vendor_id = :vid AND customer_id = :cid', [':vid' => $vendor_id, ':cid' => $user->id])->orderBy(['id' => SORT_DESC])->all();

            $response = [];
            foreach ($requests as $request) {
                $address_coordinates = explode(',', $request->customerAddress->location_coordinates);
                $response[] = [
                    'id' => $request->id,
                    'total_amount' => $request->total_amount,
                    'volume_requested' => $request->volume_requested,
                    'status' => $request->status,
                    'date_created' => $request->date_created,
                    'delivery_date' => $request->delivery_date,
                    'delivery_notes' => $request->delivery_notes,
                    'is_shared_order' => $request->is_shared_request,
                    'vendor' => [
                        'id' => $request->vendor_id,
                        'first_name' => $request->vendor->first_name,
                        'other_names' => $request->vendor->other_names,
                        'mobile_number' => $request->vendor->mobile_number,
                        'residential_location' => $request->vendor->residential_location,
                        'location_coordinates' => $request->vendor->location_coordinates,
                        'tank_volume' => $request->vendor->tank_volume,
                        'isFavorite' => false,
                        'date_created' => $request->vendor->date_created,
                        'rating' => $request->vendor->rating,
                        'dp' => $request->vendor->display_pic ?? 'images/water_tank.png',
                        'vendor_group' => [
                            'id' => $request->vendor->vendor_group,
                            'name' => $request->vendor->vendorGroup->name,
                            'description' => $request->vendor->vendorGroup->description
                        ]
                    ],
                    'customer' => [
                        'alias' => $request->customer->alias,
                        'phoneNumber' => $request->customer->phone_number,
                        'address' => [
                            'id' => $request->customer_address,
                            'address' => $request->customerAddress->address,
                            'latitude' => $address_coordinates[0],
                            'longitude' => $address_coordinates[1],
                            'default' => $request->customer_address == $request->customer->primary_address ? true : false,
                            'user_id' => $request->customer->id
                        ]
                    ]
                ];
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $response
            ]);

        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }

    /**
     * Create a customer request
     */
    public function actionCreate()
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

            $user = Customers::find()->where(['api_token' => $apiToken])->one();

            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['vendor_id']) || !isset($decodedData['volume_requested'])) {
                throw new Exception("Missing Vendor, address or water amount");
            }

            $vendor_id = $decodedData['vendor_id'];
            if (!$vendor = Vendors::findOne($vendor_id)) {
                $vendor_id = null;
            }

            $volume = $decodedData['volume_requested'];
            $address_id = isset($decodedData['address_id']) ? $decodedData['address_id'] : $user->primary_address;
            $delivery_date = $decodedData['delivery_date'] ?? null;
            $delivery_type = $decodedData['delivery_type'] ?? null;
            $isSharedRequest = $decodedData['is_shared_order'] ? $decodedData['is_shared_order'] : 0;
            $delivery_notes = $decodedData['delivery_notes'] ?? null;
            
            if (!is_null($delivery_date) && strtolower($delivery_type) == 'scheduled') {
                $date = new DateTime($delivery_date);
                $delivery_date = $date->format('Y-m-d H:i:s');
            } else {
                $delivery_date = date('Y-m-d H:i:s');
            }

            $request = new CustomerRequests();
            $request->customer_id = $user->id;
            $request->customer_address = $address_id;
            $request->volume_requested = $volume;
            $request->total_amount = $volume * Yii::$app->params['price_of_water'];
            $request->status = StatusCodes::NEW_CUSTOMER_REQUEST;
            $request->date_created = $request->date_modified = Date('Y-m-d H:i:s');
            $request->vendor_id = $vendor_id;
            $request->delivery_date = $delivery_date;
            $request->is_shared_request = $isSharedRequest;
            $request->delivery_notes = $delivery_notes;
            
            if ($request->save()) {
                //Send notifications to the vendor and customer
                if($vendor_id != null) {
                    ApiHelpers::sendVendorNotifications($request->vendor, $request);
                }
                ApiHelpers::sendCustomerNotifications($request->customer, $request, $vendor_id != null);

                $favorite = false;
                if (CustomerFavorites::findOne(['customer_id' => $user->id, 'vendor_id' => $request->vendor_id])) {
                    $favorite = true;
                }

                $address_coordinates = explode(',', $request->customerAddress->location_coordinates);
                $response = [
                    'id' => $request->id,
                    'total_amount' => $request->total_amount,
                    'volume_requested' => $request->volume_requested,
                    'status' => $request->status,
                    'date_created' => $request->date_created,
                    'delivery_date' => $request->delivery_date,
                    'delivery_notes' => $request->delivery_notes,
                    'is_shared_order' => $request->is_shared_request,
                    'vendor' => [
                        'id' => $request->vendor_id,
                        'first_name' => $request->vendor->first_name,
                        'other_names' => $request->vendor->other_names,
                        'mobile_number' => $request->vendor->mobile_number,
                        'residential_location' => $request->vendor->residential_location,
                        'location_coordinates' => $request->vendor->location_coordinates,
                        'tank_volume' => $request->vendor->tank_volume,
                        'isFavorite' => $favorite,
                        'rating' => $request->vendor->rating,
                        'date_created' => $request->vendor->date_created,
                        'dp' => $request->vendor->display_pic ?? 'images/water_tank.png',
                        'vendor_group' => [
                            'id' => $request->vendor->vendor_group,
                            'name' => $request->vendor->vendorGroup->name,
                            'description' => $request->vendor->vendorGroup->description
                        ]
                    ],
                    'customer' => [
                        'alias' => $request->customer->alias,
                        'phoneNumber' => $request->customer->phone_number,
                        'address' => [
                            'id' => $request->customer_address,
                            'address' => $request->customerAddress->address,
                            'latitude' => $address_coordinates[0],
                            'longitude' => $address_coordinates[1],
                            'default' => $request->customer_address == $request->customer->primary_address ? true : false,
                            'user_id' => $request->customer->id
                        ]
                    ]
                ];
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Request completed successfully',
                    'data' => $response
                ]);
            } else {
                throw new Exception('Error creating customer request');
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


    //Create a request without a vendor
    public function actionCreateWithoutVendor() {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user || !$user->primary_address) {
                throw new Exception(Yii::t('app', 'Customer Not Found or no primary address'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['volume_requested'])) {
                throw new Exception("Missing water amount");
            }

            $volume = $decodedData['volume_requested'];
            $address_id = $user->primary_address;
            $delivery_date = $decodedData['delivery_date'] ?? null;
            $delivery_type = $decodedData['delivery_type'] ?? null;
            $isSharedRequest = $decodedData['is_shared_order'] ? $decodedData['is_shared_order'] : 0;
            $delivery_notes = $decodedData['delivery_notes'] ?? null;
            
            if (!is_null($delivery_date) && strtolower($delivery_type) == 'scheduled') {
                $date = new DateTime($delivery_date);
                $delivery_date = $date->format('Y-m-d H:i:s');
            } else {
                $delivery_date = date('Y-m-d H:i:s');
            }

            $request = new CustomerRequests();
            $request->customer_id = $user->id;
            $request->customer_address = $address_id;
            $request->volume_requested = $volume;
            $request->total_amount = $volume * Yii::$app->params['price_of_water']; //20,000
            $request->status = StatusCodes::NEW_CUSTOMER_REQUEST_OPEN;
            $request->cancel_track = 2;
            $request->date_created = $request->date_modified = Date('Y-m-d H:i:s');
            $request->is_shared_request = $isSharedRequest;
            $request->delivery_date = $delivery_date;
            $request->delivery_notes = $delivery_notes;

            $address_coordinates = explode(',', $request->customerAddress->location_coordinates);
            $response = [
                'id' => $request->id,
                    'total_amount' => $request->total_amount,
                    'volume_requested' => $request->volume_requested,
                    'status' => $request->status,
                    'date_created' => $request->date_created,
                    'delivery_date' => $request->delivery_date,
                    'delivery_notes' => $request->delivery_notes,
                    'is_shared_order' => $request->is_shared_request,
                    'customer' => [
                        'alias' => $request->customer->alias,
                        'phoneNumber' => $request->customer->phone_number,
                        'address' => [
                            'id' => $request->customer_address,
                            'address' => $request->customerAddress->address,
                            'latitude' => $address_coordinates[0],
                            'longitude' => $address_coordinates[1],
                            'default' => $request->customer_address == $request->customer->primary_address ? true : false,
                            'user_id' => $request->customer->id
                        ]
                    ]
            ];

            if ($request->save()) {
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Request completed successfully',
                    'data' => $response
                ]);
            } else {
                throw new Exception('Error creating customer request ' . json_encode($request->errors));
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

    public function actionSubmitRating() {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user) {
                        throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['order_id']) || !isset($decodedData['rating'])) {
                throw new Exception("Missing Order ID or rating");
            }

            $ratingValue = (int)$decodedData['rating']; // Cast to integer
            $request_id = $decodedData['order_id'];

            $request = CustomerRequests::findOne(['id' => $request_id, 'customer_id' => $user->id]);
            if (!$request) {
                throw new Exception(Yii::t('app', 'Request Not Found'));
            }

            $rating = new Ratings();
            $rating->vendor_id = $request->vendor_id;
            $rating->rating = $ratingValue; // Use the renamed variable
            $rating->order_id = $request_id;
            $rating->status = StatusCodes::ACTIVE_STATUS;
            $rating->date_created = $rating->date_modified = Date('Y-m-d H:i:s');
            
            if($rating->save()) {
                // Calculate new average rating for vendor
                $averageRating = Ratings::find()
                    ->where(['vendor_id' => $request->vendor_id, 'status' => StatusCodes::ACTIVE_STATUS])
                    ->average('rating');
                
                // Update vendor's rating
                $vendor = Vendors::findOne($request->vendor_id);
                $vendor->rating = round($averageRating, 1); // Round to 1 decimal place
                $vendor->save();
                
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Rating submitted successfully',
                    'data' => [
                        'rating' => $rating,
                        'vendor_average_rating' => $vendor->rating
                    ]
                ]);
            } else {
                throw new Exception('Error submitting rating: ' . json_encode($rating->errors)  );
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

    public function actionSubmitComplaint() {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['title']) || !isset($decodedData['description'])) {
                throw new Exception("Missing order ID or complaint");
            }

            $orderId = isset($decodedData['order_id']) ? $decodedData['order_id'] : null    ;
            $title = $decodedData['title'];
            $description = $decodedData['description'];
            $category = $decodedData['category_code'];

            if(!is_null($orderId)) {
                $order = CustomerRequests::findOne(['id' => $orderId, 'customer_id' => $user->id]);
                if (!$order) {
                    throw new Exception(Yii::t('app', 'Request Not Found'));
                }
            }

            $complaint = new Complaints();
            $complaint->customer_id = $user->id;
            $complaint->type = Complaints::TYPE_CUSTOMERS;
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

    public function actionFindOrder($order_id) {
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $order = CustomerRequests::findOne(['id' => $order_id, 'customer_id' => $user->id]);
            if (!$order) {
                throw new Exception(Yii::t('app', 'Order Not Found'));
            }

            $favorite = false;
            if (CustomerFavorites::findOne(['customer_id' => $user->id, 'vendor_id' => $order->vendor_id])) {
                $favorite = true;
            }

            $address_coordinates = explode(',', $order->customerAddress->location_coordinates);
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Order found successfully',
                'data' => [
                    'id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'volume_requested' => $order->volume_requested,
                    'status' => $order->status,
                    'date_created' => $order->date_created,
                    'delivery_date' => $order->delivery_date,
                    'delivery_notes' => $order->delivery_notes,
                    'is_shared_order' => $order->is_shared_request,
                    'vendor' => [
                        'id' => $order->vendor_id,
                        'first_name' => $order->vendor->first_name,
                        'other_names' => $order->vendor->other_names,
                        'mobile_number' => $order->vendor->mobile_number,
                        'residential_location' => $order->vendor->residential_location,
                        'location_coordinates' => $order->vendor->location_coordinates,
                        'tank_volume' => $order->vendor->tank_volume,
                        'isFavorite' => $favorite,
                        'date_created' => $order->vendor->date_created,
                        'rating' => $order->vendor->rating,
                        'dp' => $order->vendor->display_pic ?? 'images/water_tank.png',
                        'vendor_group' => [
                            'id' => $order->vendor->vendor_group,
                            'name' => $order->vendor->vendorGroup->name,
                            'description' => $order->vendor->vendorGroup->description
                        ]
                    ],
                    'customer' => [
                        'alias' => $order->customer->alias,
                        'phoneNumber' => $order->customer->phone_number,
                        'address' => [
                            'id' => $order->customer_address,
                            'address' => $order->customerAddress->address,
                            'latitude' => $address_coordinates[0],
                            'longitude' => $address_coordinates[1],
                            'default' => $order->customer_address == $order->customer->primary_address ? true : false,
                            'user_id' => $order->customer->id
                        ]
                    ]
                ]
            ]);
            
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
