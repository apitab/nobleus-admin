<?php

namespace api\modules\v1\controllers;

use api\helpers\Helpers;
use backend\helpers\StatusCodes;
use Exception;
use yii;
use yii\helpers\Json;
use backend\models\Customers;
use backend\models\CustomerFavorites;
use backend\models\CustomerRequests;
use backend\models\CustomerAddress;
use backend\models\OutboundSms;
use backend\models\Vendors;
use backend\models\Payments;
use backend\models\Currencies;
use yii\filters\VerbFilter;
use backend\models\Complaints;
use backend\models\TopupRequests;
use backend\models\VendorCertifications;
use backend\models\Notifications;
use backend\models\Alerts;
use backend\models\InformationGuides;
use backend\models\AppSettings;
use backend\models\PaymentMethods;
use backend\models\VideoTutorials;


class CustomersController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'update' => ['PUT'],
            ],
        ];
        return $behaviors;
    }

    /**
     * Register a new Customer
     */
    public function actionRegister($language = "en-US", $express = 0)
    {
        Yii::$app->language = $language;
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['phone_number']) || !isset($decodedData['password']) || !isset($decodedData['alias'])) {
                throw new Exception("Missing Phone number or password or name");
            }

            //@todo Clean the input data / Format 
            $phoneNumber = $decodedData['phone_number'];
            $password = $decodedData['password'];
            $alias = $decodedData['alias'];
            $deviceToken = $decodedData['device_token'];

            $model = Customers::findOne(['phone_number' => $phoneNumber]);
            if (!$model) {
                $customer = new Customers();
                $customer->alias = $alias;
                $customer->phone_number = Helpers::formatMsisdn($phoneNumber);
                $customer->currency_id = 1;
                $customer->language = 'en';
                $customer->password_hash = Yii::$app->security->generatePasswordHash($password);
                $customer->api_token = $express ? Yii::$app->security->generateRandomString(32) : '';
                $customer->device_token = $deviceToken;
                //@renove
                $customer->code = '12345'; //rand(10000, 99999);
                $customer->status = $express ? StatusCodes::ACTIVE_STATUS : StatusCodes::CREATE_STATUS;
                $customer->date_created = $customer->date_modified = Date('Y-m-d H:i:s');

                if ($customer->save()) {

                    //Send Activation SMS
                    if (!$express) {
                        $template = Helpers::getSMSTemplate('ACTIVATION_SMS');
                        $template = str_replace("%name%", $customer->alias, $template);
                        $template = str_replace("%code%", $customer->code, $template);
                        $sms = new OutboundSms();
                        $sms->msisdn = $customer->phone_number;
                        $sms->message = $template;
                        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                        $sms->status = 'pending';
                        $sms->save();
                    }

                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Customer registered successfully',
                        'data' => [
                            'alias' => $customer->alias,
                            'phoneNumber' => $customer->phone_number,
                            'api_token' => $customer->api_token,
                            'auth' => $express ? true : false,
                            'address' => null
                        ]
                    ]);
                } else {
                    $this->setHeader(500);
                    return $this->asJson([
                        'status' => false,
                        'message' => 'Error creating account. Contact Customer care',
                        'data' => ''
                    ]);
                }
            } else {
                $this->setHeader(500);
                return $this->asJson([
                    'status' => false,
                    'message' => 'Customer is already registered',
                    'data' => ''
                ]);
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

    /**
     * Sign in Action
     */
    public function actionLogin($language = "en-US")
    {
        Yii::$app->language = $language;
        try {
            if (Yii::$app->request->isPost) {
                $data = Yii::$app->request->getRawBody();
                $decodedData = Json::decode($data);
                if (!isset($decodedData['phone_number'])) {
                    throw new Exception("Missing or invalid Phone number");
                }

                //@todo Clean the input data / Format 
                $phoneNumber = $decodedData['phone_number'];
                $formattedPhoneNumber = Helpers::formatMsisdn($phoneNumber);

                if (!$formattedPhoneNumber) {
                    throw new Exception("Invalid phone number format");
                }

                $model = Customers::find()->where(['phone_number' => $formattedPhoneNumber])->one();
                $code = rand(10000, 99999);
                if (!$model) {
                    $model = new Customers();
                    $model->alias = $phoneNumber;
                    $model->phone_number = $formattedPhoneNumber;
                    $model->currency_id = 1;
                    $model->language = 'en';
                    $model->password_hash = Yii::$app->security->generatePasswordHash('12345');
                    $model->status = StatusCodes::CREATE_STATUS;
                    $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
                    $model->api_token = Yii::$app->security->generateRandomString(32);
                    $model->code = $code;
                    if (!$model->save()) {
                        throw new Exception('Error Logging in. Contact customer support');
                    }
                } else {
                    $model->api_token = Yii::$app->security->generateRandomString(32);
                    $model->code = $code;
                    if (!$model->save()) {
                        throw new Exception('Error Logging in. Contact customer support');
                    }
                }

                //Send OTP SMS
                $template = Helpers::getSMSTemplate('ACTIVATION_SMS');
                $template = str_replace("%name%", $model->alias, $template);
                $template = str_replace("%code%", $code, $template);
                $sms = new OutboundSms();
                $sms->msisdn = $model->phone_number;
                $sms->message = $template;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();


                //Format if has primary address
                $locationDescription = '';
                $longitude = '0.0';
                $latitude = '0.0';
                $address = [];
                if (!is_null($model->primary_address)) {
                    $coordinates = explode(',', $model->primaryAddress->location_coordinates);
                    $longitude = $coordinates[0];
                    $latitude = $coordinates[1];
                    $locationDescription = $model->primaryAddress->address;
                    $address = [
                        'id' => $model->primary_address,
                        'address' => $model->primaryAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                    ];
                }
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Customer logged in successfully',
                    'data' => [
                        'alias' => $model->alias,
                        'phoneNumber' => $model->phone_number,
                        'api_token' => $model->api_token,
                        'isActive' => $model->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $model->status ? true : false,
                        'otp' => $code,
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $model->currency->name,
                            'code' => $model->currency->code,
                            'symbol' => $model->currency->symbol,
                        ],
                        'address' => $address,
                        'wallet_balance' => $model->wallet_balance
                    ]
                ]);
            }
            $this->setHeader(405);
            return $this->asJson([
                'status' => false,
                'message' => '405 Method Not Allowed',
                'data' => ''
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
     * Activate a customer account
     */
    public function actionVerify($language = "en-US")
    {
        Yii::$app->language = $language;
        try {
            if (Yii::$app->request->isPost) {
                $data = Yii::$app->request->getRawBody();
                $decodedData = Json::decode($data);
                if (!isset($decodedData['phone_number']) || !isset($decodedData['code'])) {
                    throw new Exception("Missing Phone number or code");
                }

                //@todo Clean the input data / Format 
                $phoneNumber = $decodedData['phone_number'];
                $code = $decodedData['code'];
                $deviceToken = $decodedData['device_token'];

                $model = Customers::findOne(['phone_number' => $phoneNumber]);
                if (!$model) {
                    $this->setHeader(404);
                    return $this->asJson([
                        'status' => false,
                        'message' => 'The Requested Account does not exist',
                        'data' => ''
                    ]);
                } else {
                    if ($model->code != $code) {
                        $this->setHeader(500);
                        return $this->asJson([
                            'status' => false,
                            'message' => 'Invalid verification code',
                            'data' => ''
                        ]);
                    }
                    $model->device_token = $deviceToken;
                    $model->api_token = Yii::$app->security->generateRandomString(32);
                    $model->save();
                    $this->setHeader(200);
                    //Format if has primary address
                    //Format if has primary address
                    $locationDescription = '';
                    $longitude = '0.0';
                    $latitude = '0.0';
                    $address = [];
                    if (!is_null($model->primary_address)) {
                        $coordinates = explode(',', $model->primaryAddress->location_coordinates);
                        $longitude = $coordinates[1];
                        $latitude = $coordinates[0];
                        $locationDescription = $model->primaryAddress->address;
                        $address = [
                            'id' => $model->primary_address,
                            'address' => $model->primaryAddress->address,
                            'latitude' => $coordinates[0],
                            'longitude' => $coordinates[1],
                        ];
                    }
                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Customer logged in successfully',
                        'data' => [
                            'alias' => $model->alias,
                            'phoneNumber' => $model->phone_number,
                            'api_token' => $model->api_token,
                            'isActive' => $model->status == StatusCodes::DELETE_STATUS ? false : true,
                            'verified' => StatusCodes::ACTIVE_STATUS == $model->status ? true : false,
                            'otp' => $code,
                            'locationDescription' => $locationDescription,
                            'longitude' => $longitude,
                            'latitude' => $latitude,
                            'currency' => [
                                'name' => $model->currency->name,
                                'code' => $model->currency->code,
                                'symbol' => $model->currency->symbol,
                            ],
                            'address' => $address,
                            'wallet_balance' => $model->wallet_balance
                        ]
                    ]);
                }
            }
            $this->setHeader(405);
            return $this->asJson([
                'status' => false,
                'message' => '405 Method Not Allowed',
                'data' => ''
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
     * Activate a customer and append their alias
     */
    public function actionActivate()
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            if ($customer->status == StatusCodes::ACTIVE_STATUS) {
                $this->setHeader(200);
                //Format if has primary address
                $locationDescription = '';
                $longitude = '0.0';
                $latitude = '0.0';
                $address = [];
                if (!is_null($customer->primary_address)) {
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $longitude = $coordinates[1];
                    $latitude = $coordinates[0];
                    $locationDescription = $customer->primaryAddress->address;
                    $address = [
                        'id' => $customer->primary_address,
                        'address' => $customer->primaryAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                    ];
                }
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Customer logged in successfully',
                    'data' => [
                        'alias' => $customer->alias,
                        'phoneNumber' => $customer->phone_number,
                        'api_token' => $customer->api_token,
                        'isActive' => $customer->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $customer->status ? true : false,
                        'otp' => '',
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $customer->currency->name,
                            'code' => $customer->currency->code,
                            'symbol' => $customer->currency->symbol,
                        ],
                        'address' => $address,
                        'wallet_balance' => $customer->wallet_balance
                    ]
                ]);
            }

            if (Yii::$app->request->isPost) {
                $data = Yii::$app->request->getRawBody();
                $decodedData = Json::decode($data);
                if (!isset($decodedData['alias'])) {
                    throw new Exception("Enter your full names");
                }

                //@todo Clean the input data / Format 
                $alias = $decodedData['alias'];
                $customer->alias = $alias;
                $customer->api_token = Yii::$app->security->generateRandomString(32);
                $customer->status = StatusCodes::ACTIVE_STATUS;
                $customer->save();

                $this->setHeader(200);
                //Format if has primary address
                $locationDescription = '';
                $longitude = '0.0';
                $latitude = '0.0';
                $address = [];
                if (!is_null($customer->primary_address)) {
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $longitude = $coordinates[1];
                    $latitude = $coordinates[0];
                    $locationDescription = $customer->primaryAddress->address;
                    $address = [
                        'id' => $customer->primary_address,
                        'address' => $customer->primaryAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                    ];
                }
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Customer logged in successfully',
                    'data' => [
                        'alias' => $customer->alias,
                        'phoneNumber' => $customer->phone_number,
                        'api_token' => $customer->api_token,
                        'isActive' => $customer->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $customer->status ? true : false,
                        'otp' => '',
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $customer->currency->name,
                            'code' => $customer->currency->code,
                            'symbol' => $customer->currency->symbol,
                        ],
                        'address' => $address,
                        'wallet_balance' => $customer->wallet_balance
                    ]
                ]);
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

    /**
     * Resend code to phone
     */
    public function actionResendCode($language = "en-US")
    {
        Yii::$app->language = $language;
        try {
            if (Yii::$app->request->isPost) {
                $data = Yii::$app->request->getRawBody();
                $decodedData = Json::decode($data);
                if (!isset($decodedData['phone_number'])) {
                    throw new Exception("Missing Phone number");
                }

                //@todo Clean the input data / Format 
                $phoneNumber = $decodedData['phone_number'];

                $model = Customers::findOne(['phone_number' => $phoneNumber]);
                if (!$model) {
                    $this->setHeader(404);
                    return $this->asJson([
                        'status' => false,
                        'message' => 'The Requested Account doesn\'t exist',
                        'data' => ''
                    ]);
                } else {
                    $code = rand(10000, 99999);
                    $template = Helpers::getSMSTemplate('ACTIVATION_SMS');
                    $template = str_replace("%name%", $model->alias, $template);
                    $template = str_replace("%code%", $code, $template);
                    $sms = new OutboundSms();
                    $sms->msisdn = $model->phone_number;
                    $sms->message = $template;
                    $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                    $sms->status = 'pending';
                    $sms->save();

                    $model->code = $code;
                    $model->save();

                    $this->setHeader(200);
                    //Format if has primary address
                    $locationDescription = '';
                    $longitude = '';
                    $latitude = '';
                    $address = [];
                    if (!is_null($model->primary_address)) {
                        $coordinates = explode(',', $model->primaryAddress->location_coordinates);
                        $longitude = $coordinates[1];
                        $latitude = $coordinates[0];
                        $locationDescription = $model->primaryAddress->address;
                        $address = [
                            'id' => $model->primary_address,
                            'address' => $model->primaryAddress->address,
                            'latitude' => $coordinates[0],
                            'longitude' => $coordinates[1],
                        ];
                    }
                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Customer logged in successfully',
                        'data' => [
                            'alias' => $model->alias,
                            'phoneNumber' => $model->phone_number,
                            'api_token' => $model->api_token,
                            'isActive' => $model->status == StatusCodes::DELETE_STATUS ? false : true,
                            'verified' => StatusCodes::ACTIVE_STATUS == $model->status ? true : false,
                            'otp' => $code,
                            'locationDescription' => $locationDescription,
                            'longitude' => $longitude,
                            'latitude' => $latitude,
                            'currency' => [
                                'name' => $model->currency->name,
                                'code' => $model->currency->code,
                                'symbol' => $model->currency->symbol,
                            ],
                            'address' => $address,
                            'wallet_balance' => $model->wallet_balance
                        ]
                    ]);
                }
            }
            $this->setHeader(405);
            return $this->asJson([
                'status' => false,
                'message' => '405 Method Not Allowed',
                'data' => ''
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

    public function actionDashboard()
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
            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            //Find the list of addresses for this user
            $addresses = CustomerAddress::find()->where(['customer_id' => $customer->id, 'status' => StatusCodes::ACTIVE_STATUS])->all();

            $addressList = [];
            foreach ($addresses as $address) {
                $coordinates = explode(',', $address->location_coordinates);
                $addressList[] = [
                    'id' => $address->id,
                    'address' => $address->address,
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'isPrimary' => $address->id == $customer->primary_address ? true : false,
                ];
            }

            //Fetch list of nearby vendors
            $vendors = Vendors::find()
                ->where('status = :status', [':status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere('is_online = :is_online', [':is_online' => StatusCodes::ACTIVE_STATUS])
                ->all();

            $vendorList = [];
            foreach ($vendors as $vendor) {
                $coordinates = explode(',', $vendor->location_coordinates);
                // Get customer coordinates from primary address
                $customerLat = 0;
                $customerLon = 0;
                if ($customer->primary_address && $customer->primaryAddress) {
                    $customerCoords = explode(',', $customer->primaryAddress->location_coordinates);
                    $customerLat = $customerCoords[0] ?? 0;
                    $customerLon = $customerCoords[1] ?? 0;
                }

                $certification = VendorCertifications::find()
                    ->where([
                        'vendor_id' => $vendor->id,
                        'status' => StatusCodes::ACTIVE_STATUS
                    ])
                    ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                    ->orderBy(['expiry_date' => SORT_ASC])
                    ->one();

                $isFavorite = false;
                if (CustomerFavorites::findOne(['customer_id' => $customer->id, 'vendor_id' => $vendor->id])) {
                    $isFavorite = true;
                }
                $data = [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'other_names' => $vendor->other_names,
                    'mobile_number' => $vendor->mobile_number,
                    'residential_location' => $vendor->residential_location,
                    'location_coordinates' => $vendor->location_coordinates,
                    'tank_volume' => $vendor->tank_volume,
                    'isFavorite' => $isFavorite,
                    'date_created' => $vendor->date_created,
                    'rating' => $vendor->rating,
                    'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                    'isOnline' => $vendor->is_online == 1 ? true : false,
                    'moq' => $vendor->moq,
                    'restrictToSpecificLocations' => $vendor->restrict_to_specific_locations,
                    'specificLocations' => $vendor->specific_locations,
                    'vendor_group' => [
                        'id' => $vendor->vendor_group,
                        'name' => $vendor->vendorGroup->name,
                        'description' => $vendor->vendorGroup->description
                    ],
                    'price_of_water' => $vendor->price_of_water > 0 ? $vendor->price_of_water : AppSettings::find()->one()->price_per_barrel,
                ];
                if ($certification) {
                    $data['isCertified'] = true;
                    $data['certificationDetails'] = $certification->certification_details;
                    $data['certificationExpiryDate'] = $certification->expiry_date;
                    $data['certificationDate'] = $certification->date_modified;
                } else {
                    $data['isCertified'] = false;
                }

                $data['distance'] = number_format((float) Helpers::haversineDistance($customerLat, $customerLon, $coordinates[0], $coordinates[1]), 2, '.', '');
                $vendorList[] = $data;
            }

            //Sort the vendor list by distance
            usort($vendorList, function ($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            $vendorList = array_slice($vendorList, 0, 5);

            //Get list of favorite vendors
            $favoriteVendors = CustomerFavorites::find()
                ->where(['customer_id' => $customer->id])
                ->all();
            $favoriteVendorList = [];
            foreach ($favoriteVendors as $favoriteVendor) {
                $data = [
                    'id' => $favoriteVendor->vendor->id,
                    'first_name' => $favoriteVendor->vendor->first_name,
                    'other_names' => $favoriteVendor->vendor->other_names,
                    'mobile_number' => $favoriteVendor->vendor->mobile_number,
                    'residential_location' => $favoriteVendor->vendor->residential_location,
                    'location_coordinates' => $favoriteVendor->vendor->location_coordinates,
                    'tank_volume' => $favoriteVendor->vendor->tank_volume,
                    'isFavorite' => true,
                    'date_created' => $favoriteVendor->vendor->date_created,
                    'rating' => $favoriteVendor->vendor->rating,
                    'dp' => $favoriteVendor->vendor->display_pic ?? 'images/water_tank.png',
                    'isOnline' => $favoriteVendor->vendor->is_online == 1 ? true : false,
                    'price_of_water' => $favoriteVendor->vendor->price_of_water > 0 ? $favoriteVendor->vendor->price_of_water : AppSettings::find()->one()->price_per_barrel,
                    'moq' => $favoriteVendor->vendor->moq,
                    'restrictToSpecificLocations' => $favoriteVendor->vendor->restrict_to_specific_locations,
                    'specificLocations' => $favoriteVendor->vendor->specific_locations,
                    'vendor_group' => [
                        'id' => $favoriteVendor->vendor->vendorGroup->id,
                        'name' => $favoriteVendor->vendor->vendorGroup->name,
                        'description' => $favoriteVendor->vendor->vendorGroup->description
                    ]
                ];
                $certification = VendorCertifications::find()
                    ->where([
                        'vendor_id' => $favoriteVendor->id,
                        'status' => StatusCodes::ACTIVE_STATUS
                    ])
                    ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                    ->orderBy(['expiry_date' => SORT_ASC])
                    ->one();

                if ($certification) {
                    $data['isCertified'] = true;
                    $data['certificationDetails'] = $favoriteVendor->certification->certification_details;
                    $data['certificationExpiryDate'] = $favoriteVendor->certification->expiry_date;
                    $data['certificationDate'] = $favoriteVendor->certification->date_modified;
                } else {
                    $data['isCertified'] = false;
                }
                $coordinates = explode(',', $favoriteVendor->vendor->location_coordinates);
                $data['distance'] = number_format((float) Helpers::haversineDistance($customerLat, $customerLon, $coordinates[0], $coordinates[1]), 2, '.', '');
                $favoriteVendorList[] = $data;
            }

            //Alerts and guides

            $alerts = Alerts::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                ->andWhere(['type' => Alerts::TYPE_CUSTOMERS])
                ->orderBy(['expiry_date' => SORT_ASC])
                ->all();
            $alertList = [];
            foreach ($alerts as $alert) {
                $alertList[] = [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'expiry_date' => $alert->expiry_date,
                    'date_created' => $alert->date_created,
                    'level' => $alert->level,
                    'attachment' => $alert->attachment,
                    'attachment_url' => $alert->attachment ? Yii::getAlias('@web') . '/' . $alert->attachment : null,
                ];
            }


            $informationGuides = InformationGuides::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->andWhere(['target' => 'customers'])
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

            //Get list of video tutorials
            $videoTutorials = VideoTutorials::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->all();
            $videoTutorialList = [];
            foreach ($videoTutorials as $videoTutorial) {
                $videoTutorialList[] = [
                    'id' => $videoTutorial->id,
                    'title' => $videoTutorial->name,
                    'description' => $videoTutorial->description,
                    'video_url' => 'http://10.0.2.2:10000/' . $videoTutorial->video_name,
                    'date_created' => $videoTutorial->date_created,
                ];
            }

            //Find the ten last recent requests
            //Ignore status 20 (customer cancelled request)
            $recentRequests = CustomerRequests::find()
                ->where(['customer_id' => $customer->id])
                ->andWhere('status != :status', [':status' => StatusCodes::CUSTOMER_CANCELLED_REQUEST])
                ->orderBy(['id' => SORT_DESC])
                ->limit(10)
                ->all();
            $recentRequestList = [];
            foreach ($recentRequests as $req) {
                $favorite = false;
                if ($req->vendor_id != null && CustomerFavorites::findOne(['customer_id' => $customer->id, 'vendor_id' => $req->vendor_id])) {
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
                    'dropoff_address' => $req->customerAddress->address,
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

                $recentRequestList[] = $data;
            }

            //Fetch payment methods
            $paymentMethods = PaymentMethods::find()
                ->where(['enabled' => StatusCodes::ACTIVE_STATUS])
                ->all();
            $paymentMethodList = [];
            foreach ($paymentMethods as $paymentMethod) {
                $paymentMethodList[] = [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'description' => $paymentMethod->description,
                    'image' => $paymentMethod->image,
                    'code' => $paymentMethod->code,
                    'requires_phone' => $paymentMethod->requires_phone == 1 ? true : false,
                    'method_metadata' => $paymentMethod->method_metadata
                ];
            }

            //Find the list of payments: payments belong to requests which belong to this customer
            $payments = Payments::find()
                ->joinWith(['request'])
                ->where(['customer_requests.customer_id' => $customer->id])
                ->orderBy(['payments.id' => SORT_DESC])
                //->limit(10)
                ->all();

            $paymentList = [];
            foreach ($payments as $payment) {
                $paymentList[] = [
                    'id' => $payment->id,
                    'request_id' => $payment->request_id,
                    'amount' => $payment->amount,
                    'description' => $payment->description,
                    'payment_method' => $payment->paymentMethod->name ?? null,
                    'payment_status' => StatusCodes::getPaymentStatusText($payment->status),
                    'image' => $payment->paymentMethod->image ?? null,
                    'date_created' => $payment->date_created,
                ];
            }

            //Notifications
            $notifications = Notifications::find()
                ->where(['key_id' => $customer->id, 'key_type' => 'customer', 'is_read' => 0])
                ->orderBy(['id' => SORT_DESC])
                ->all();
            $notificationList = [];
            foreach ($notifications as $notification) {
                $notificationList[] = [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'dateCreated' => $notification->date_created,
                    'data' => $notification->data_values,
                    'isRead' => $notification->is_read == 1 ? true : false,
                    'notificationType' => $notification->not_type
                ];
            }

            



            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Dashboard data retrieved successfully'),
                'data' => [
                    'wallet_balance' => $customer->wallet_balance,
                    'addresses' => $addressList,
                    'vendors' => $vendorList,
                    'favorite_vendors' => $favoriteVendorList,
                    'alerts' => $alertList,
                    'information_guides' => $informationGuideList,
                    'requests' => $recentRequestList,
                    'payment_methods' => $paymentMethodList,
                    'video_tutorials' => $videoTutorialList,
                    'payments' => $paymentList,
                    'notifications' => $notificationList
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

    //Find the list fo favorite vendors
    public function actionFavoriteVendors($all = 1)
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $all = Yii::$app->request->get('all', 0); // Default to 0 if 'all' is not set

            // Start building the query
            $customerFavoritesQuery = CustomerFavorites::find()->where(['customer_id' => $customer->id]);

            // Apply limit if 'all' parameter is not 1
            if ($all != 1) {
                $customerFavoritesQuery->limit(4);
            }

            // Order by 'id' in descending order
            $customerFavoritesQuery->orderBy(['id' => SORT_DESC]);

            // Execute the query and fetch results
            $customerFavorites = $customerFavoritesQuery->all();

            $vendors = [];
            if (count($customerFavorites) >= 1) {
                foreach ($customerFavorites as $vendor) {
                    // Fetch active, non-expired certification
                    $certification = VendorCertifications::find()
                        ->where([
                            'vendor_id' => $vendor->id,
                            'status' => StatusCodes::ACTIVE_STATUS
                        ])
                        ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                        ->orderBy(['expiry_date' => SORT_ASC])
                        ->one();

                    $vendorArr = [
                        'id' => $vendor->vendor_id,
                        'first_name' => $vendor->vendor->first_name,
                        'other_names' => $vendor->vendor->other_names,
                        'mobile_number' => $vendor->vendor->mobile_number,
                        'residential_location' => $vendor->vendor->residential_location,
                        'location_coordinates' => $vendor->vendor->location_coordinates,
                        'tank_volume' => $vendor->vendor->tank_volume,
                        'isFavorite' => true,
                        'rating' => $vendor->vendor->rating,
                        'dp' => $vendor->vendor->display_pic ?? 'images/water_tank.png',
                        'isOnline' => $vendor->vendor->is_online == StatusCodes::ACTIVE_STATUS ? true : false,
                        'moq' => $vendor->vendor->moq,
                        'vehicleRegistration' => $vendor->vendor->vehicle_registration,
                        'vendor_group' => [
                            'id' => $vendor->vendor->vendor_group,
                            'name' => $vendor->vendor->vendorGroup->name,
                            'description' => $vendor->vendor->vendorGroup->description
                        ]
                    ];

                    if ($certification) {
                        $vendorArr['isCertified'] = true;
                        $vendorArr['certificationDetails'] = $certification->certification_details;
                        $vendorArr['certificationExpiryDate'] = $certification->expiry_date;
                        $vendorArr['certificationDate'] = $certification->date_modified;
                    } else {
                        $vendorArr['isCertified'] = false;
                    }

                    $vendors[] = $vendorArr;
                }
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Favorite Vendors retrieved successfully'),
                'data' => array_values($vendors)
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

    public function actionAddToFavorites()
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['vendor_id'])) {
                throw new Exception("Missing vendor ID");
            }

            $vendor = Vendors::find()->where(['id' => $decodedData['vendor_id']])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor details not found'));
            }

            $vendororite = CustomerFavorites::find()->where('customer_id = :cid AND vendor_id = :vid', [':vid' => $vendor->id, ':cid' => $customer->id])->one();
            if (!$vendororite) {
                $vendororite = new CustomerFavorites();
                $vendororite->customer_id = $customer->id;
                $vendororite->vendor_id = $vendor->id;
                $vendororite->status = StatusCodes::ACTIVE_STATUS;
                $vendororite->date_created = $vendororite->date_modified = Date('Y-m-d H:i:s');
                $vendororite->save();
            }

            $certification = VendorCertifications::find()
                ->where([
                    'vendor_id' => $vendor->id,
                    'status' => StatusCodes::ACTIVE_STATUS
                ])
                ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                ->orderBy(['expiry_date' => SORT_ASC])
                ->one();
            $data = [
                'id' => $vendor->id,
                'first_name' => $vendor->first_name,
                'other_names' => $vendor->other_names,
                'mobile_number' => $vendor->mobile_number,
                'residential_location' => $vendor->residential_location,
                'location_coordinates' => $vendor->location_coordinates,
                'tank_volume' => $vendor->tank_volume,
                'isFavorite' => true,
                'rating' => $vendor->rating,
                'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                'moq' => $vendor->moq,
                'isOnline' => $vendor->is_online == StatusCodes::ACTIVE_STATUS ? true : false,
                'vehicleRegistration' => $vendor->vehicle_registration,
                'vendor_group' => [
                    'id' => $vendor->vendor_group,
                    'name' => $vendor->vendorGroup->name,
                    'description' => $vendor->vendorGroup->description
                ]
            ];
            if ($certification) {
                $data['isCertified'] = true;
                $data['certificationDetails'] = $certification->certification_details;
                $data['certificationExpiryDate'] = $certification->expiry_date;
                $data['certificationDate'] = $certification->date_modified;
            } else {
                $data['isCertified'] = false;
            }
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor added to favorites successfully'),
                'data' => $data
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

    public function actionRemoveFromFavorites()
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['vendor_id'])) {
                throw new Exception("Missing vendor ID");
            }

            $vendor = Vendors::find()->where(['id' => $decodedData['vendor_id']])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor details not found'));
            }

            $vendororite = CustomerFavorites::find()->where('customer_id = :cid AND vendor_id = :vid', [':vid' => $vendor->id, ':cid' => $customer->id])->one();
            if ($vendororite) {
                $vendororite->delete();
            }

            $this->setHeader(200);
            $certification = VendorCertifications::find()
                ->where([
                    'vendor_id' => $vendor->id,
                    'status' => StatusCodes::ACTIVE_STATUS
                ])
                ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                ->orderBy(['expiry_date' => SORT_ASC])
                ->one();
            $data = [
                'id' => $vendor->id,
                'first_name' => $vendor->first_name,
                'other_names' => $vendor->other_names,
                'mobile_number' => $vendor->mobile_number,
                'residential_location' => $vendor->residential_location,
                'location_coordinates' => $vendor->location_coordinates,
                'tank_volume' => $vendor->tank_volume,
                'isFavorite' => false,
                'rating' => $vendor->rating,
                'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                'moq' => $vendor->moq,
                'isOnline' => $vendor->is_online == StatusCodes::ACTIVE_STATUS ? true : false,
                'vehicleRegistration' => $vendor->vehicle_registration,
                'vendor_group' => [
                    'id' => $vendor->vendor_group,
                    'name' => $vendor->vendorGroup->name,
                    'description' => $vendor->vendorGroup->description
                ]
            ];
            if ($certification) {
                $data['isCertified'] = true;
                $data['certificationDetails'] = $certification->certification_details;
                $data['certificationExpiryDate'] = $certification->expiry_date;
                $data['certificationDate'] = $certification->date_modified;
            } else {
                $data['isCertified'] = false;
            }
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor removed from favorites successfully'),
                'data' => $data
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

    public function actionRecentRequests($api_token, $language = 'en-US')
    {
        Yii::$app->language = $language;
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $customerRequests = CustomerRequests::find()->where(['customer_id' => $customer->id])->limit(4)->orderBy('id DESC')->all();
            $requests = [];

            foreach ($customerRequests as $req) {
                //Put Status in a case we can filter on APP
                switch ($req->status) {
                    case StatusCodes::NEW_CUSTOMER_REQUEST:
                        $req->status = Yii::t('app', 'New');
                        break;
                    case StatusCodes::NEW_CUSTOMER_REQUEST_OPEN:
                        $req->status = Yii::t('app', 'On the Way');
                        break;
                    case StatusCodes::PAYMENT_AWAITING_CONFIRMATION:
                        $req->status = Yii::t('app', 'Payment Awaiting Confirmation');
                        break;
                    case StatusCodes::ON_THE_WAY_CUSTOMER_REQUEST:
                        $req->status = Yii::t('app', 'On the Way');
                        break;
                    case StatusCodes::DELIVERED_CUSTOMER_REQUEST:
                        $req->status = Yii::t('app', 'Delivered');
                        break;
                    case StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST:
                        $req->status = Yii::t('app', 'Payment Initiated');
                        break;
                    case StatusCodes::COMPLETED_CUSTOMER_REQUEST:
                        $req->status = Yii::t('app', 'Paid');
                        break;
                    case 20:
                        $req->status = Yii::t('app', 'Cancelled');
                }
                $address_coordinates = explode(',', $req->customerAddress->location_coordinates);
                $data = [
                    'id' => $req->id,
                    'total_amount' => $req->total_amount,
                    'volume_requested' => $req->volume_requested,
                    'status' => $req->status,
                    'date_created' => $req->date_created,
                    'vendor' => [
                        'id' => $req->vendor_id,
                        'first_name' => $req->vendor->first_name,
                        'other_names' => $req->vendor->other_names,
                        'mobile_number' => $req->vendor->mobile_number,
                        'residential_location' => $req->vendor->residential_location,
                        'location_coordinates' => $req->vendor->location_coordinates,
                        'tank_volume' => $req->vendor->tank_volume,
                        'isFavorite' => true,
                        'rating' => $req->vendor->rating,
                        'dp' => $req->vendor->display_pic ?? 'images/water_tank.png',
                        'moq' => $req->vendor->moq,
                        'isOnline' => $req->vendor->is_online == StatusCodes::ACTIVE_STATUS ? true : false,
                        'vehicleRegistration' => $req->vendor->vehicle_registration,
                        'vendor_group' => [
                            'id' => $req->vendor->vendor_group,
                            'name' => $req->vendor->vendorGroup->name,
                            'description' => $req->vendor->vendorGroup->description
                        ]
                    ],
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
                'message' => Yii::t('app', 'Recent requests retrieved successfully'),
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

    /**
     * Sign out action
     */
    public function actionOut($api_token, $language = 'en_US')
    {
        Yii::$app->language = $language;
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }
            $customer->api_token = null;
            if ($customer->save()) {
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Sign out successfull'),
                    'data' => []
                ]);
            } else {
                throw new Exception(Yii::t('app', 'Error Logging out user'));
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

    /**
     * View payment history
     */
    public function actionPaymentsHistory($api_token, $language = 'en-US')
    {
        Yii::$app->language = $language;
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $payments = Payments::find()
                ->joinWith('customerRequest')
                ->where(['customer_requests.customer_id' => $customer->id])
                ->orderBy(['payments.id' => SORT_DESC])
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
                    'date_modified' => $payment->date_modified,
                    'customer_request' => [
                        'id' => $payment->request->id,
                        'total_amount' => $payment->request->total_amount,
                        'volume_requested' => $payment->request->volume_requested,
                        'status' => $payment->request->status,
                        'date_created' => $payment->request->date_created,
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
                            'moq' => $payment->request->vendor->moq,
                            'isOnline' => $payment->request->vendor->is_online == StatusCodes::ACTIVE_STATUS ? true : false,
                            'vehicleRegistration' => $payment->request->vendor->vehicle_registration,
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

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Payment history retrieved successfully'),
                'data' => array_values($data)
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

    public function actionUpdate($language = 'en-US')
    {
        Yii::$app->language = $language;
        try {
            $authHeader = Yii::$app->request->headers->get('Authorization');

            if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
                throw new Exception("Missing or invalid Authorization header");
            }

            $apiToken = substr($authHeader, 7);
            if (!$apiToken) {
                throw new Exception("Missing API token");
            }
            // Verify customer exists and matches api_token
            $customer = Customers::find()
                ->where(['api_token' => $apiToken])
                ->one();

            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            $alias = $decodedData['alias'] ?? null;
            $currency = $decodedData['currency'] ?? null;
            $currency = $currency != '' ? Currencies::find()->where(['code' => $currency])->one() : $customer->currency;

            $customer->alias = $alias != '' ? $alias : $customer->alias;
            $customer->currency_id = $currency->id;
            if($customer->status == StatusCodes::CREATE_STATUS) {
                $customer->status = StatusCodes::ACTIVE_STATUS;
            }

            if ($customer->save()) {
                $locationDescription = '';
                $longitude = '';
                $latitude = '';

                ///Address
                $address = [];
                if (!is_null($customer->primary_address)) {
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $longitude = $coordinates[1];
                    $latitude = $coordinates[0];
                    $locationDescription = $customer->primaryAddress->address;
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $address = [
                        'id' => $customer->primary_address,
                        'address' => $customer->primaryAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                        'default' => true,
                    ];
                }
                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Customer details updated successfully'),
                    'data' => [
                        'alias' => $customer->alias,
                        'phoneNumber' => $customer->phone_number,
                        'api_token' => $customer->api_token,
                        'isActive' => $customer->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $customer->status ? true : false,
                        'otp' => '',
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $customer->currency->name,
                            'code' => $customer->currency->code,
                            'symbol' => $customer->currency->symbol,
                        ],
                        'address' => $address
                    ]
                ]);
            } else {
                throw new Exception(Yii::t('app', 'Failed to update customer details'));
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

    public function actionUpdateDefaultAddress()
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['address_id'])) {
                throw new Exception("Missing address");
            }

            $address_id = $decodedData['address_id'];
            $address = CustomerAddress::find()->where(['id' => $address_id, 'customer_id' => $customer->id])->one();
            if (!$address) {
                throw new Exception("Address not found");
            }

            $customer->primary_address = $address_id;
            if ($customer->save()) {
                $locationDescription = '';
                $longitude = '';
                $latitude = '';

                ///Address
                $address = [];
                if (!is_null($customer->primary_address)) {
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $longitude = $coordinates[1];
                    $latitude = $coordinates[0];
                    $locationDescription = $customer->primaryAddress->address;
                    $coordinates = explode(',', $customer->primaryAddress->location_coordinates);
                    $address = [
                        'id' => $customer->primary_address,
                        'address' => $customer->primaryAddress->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                        'isPrimary' => true,
                    ];
                }

                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Default address updated successfully'),
                    'data' => [
                        'alias' => $customer->alias,
                        'phoneNumber' => $customer->phone_number,
                        'api_token' => $customer->api_token,
                        'isActive' => $customer->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $customer->status ? true : false,
                        'otp' => '',
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $customer->currency->name,
                            'code' => $customer->currency->code,
                            'symbol' => $customer->currency->symbol,
                        ],
                        'address' => $address
                    ]
                ]);
            } else {
                throw new Exception(Yii::t('app', 'Failed to update default address'));
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


    public function actionSaveAddress()
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

            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['latitude']) || !isset($decodedData['longitude'])) {
                throw new Exception("Missing address");
            }

            if (!isset($decodedData['description'])) {
                throw new Exception("Missing address description");
            }

            $latitude = $decodedData['latitude'];
            $longitude = $decodedData['longitude'];
            $description = $decodedData['description'];

            $address = new CustomerAddress();
            $address->customer_id = $customer->id;
            $address->location_id = 1;
            $address->address = $description;
            $address->location_coordinates = "$latitude,$longitude";
            $address->date_created = date('Y-m-d H:i:s');
            $address->date_modified = date('Y-m-d H:i:s');
            $address->status = StatusCodes::ACTIVE_STATUS;

            if (!$address->save()) {
                throw new Exception("Failed to save address");
            }

            $customer->primary_address = $address->id;
            if ($customer->save()) {
                $coordinates = explode(',', $address->location_coordinates);
                $longitude = $coordinates[1];
                $latitude = $coordinates[0];
                $locationDescription = $address->address;

                ///Address
                $address = [
                    'id' => $address->id,
                    'address' => $address->address,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'isPrimary' => true,
                ];

                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Address saved successfully'),
                    'data' => [
                        'id' => $customer->id,
                        'alias' => $customer->alias,
                        'phoneNumber' => $customer->phone_number,
                        'api_token' => $customer->api_token,
                        'isActive' => $customer->status == StatusCodes::DELETE_STATUS ? false : true,
                        'verified' => StatusCodes::ACTIVE_STATUS == $customer->status ? true : false,
                        'otp' => '',
                        'locationDescription' => $locationDescription,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'currency' => [
                            'name' => $customer->currency->name,
                            'code' => $customer->currency->code,
                            'symbol' => $customer->currency->symbol,
                        ],
                        'address' => $address
                    ]
                ]);
            } else {
                throw new Exception(Yii::t('app', 'Failed to update default address'));
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

            $user = Customers::findOne(['api_token' => $apiToken]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $complaints = Complaints::find()
            ->where(['customer_id' => $user->id])
            ->andWhere(['type' => Complaints::TYPE_CUSTOMERS])
            ->orderBy(['id' => SORT_DESC])
            ->all();
            if (!$complaints) {
                throw new Exception(Yii::t('app', 'No complaints found'));
            }

            $complaints = array_map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'category' => $complaint->category,
                    'title' => $complaint->title,
                    'description' => $complaint->description,
                    'resolution_notes' => $complaint->resolution_notes,
                    'status' => $complaint->status,
                    'created_at' => $complaint->date_created,
                ];
            }, $complaints);

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Complaints retrieved successfully'),
                'data' => $complaints
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

    public function actionInitiateTopup()
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

            $customer = Customers::findOne(['api_token' => $apiToken]);
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            if (!isset($decodedData['amount'])) {
                throw new Exception("Missing amount");
            }

            $amount = $decodedData['amount'];

            $topup = new TopupRequests();
            $topup->customer_id = $customer->id;
            $topup->phone_number = $customer->phone_number;
            $topup->amount = $amount;
            $topup->status = TopupRequests::STATUS_PENDING;
            $topup->date_created = date('Y-m-d H:i:s');
            $topup->date_modified = date('Y-m-d H:i:s');

            if (!$topup->save()) {
                throw new Exception("Failed to initiate topup");
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Topup request initiated successfully'),
                'data' => $topup
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

    public function actionGetWalletBalance()
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

            $customer = Customers::findOne(['api_token' => $apiToken]);
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Wallet balance retrieved successfully'),
                'data' => [
                    'balance' => number_format($customer->wallet_balance, 2)
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

    public function actionGetNotifications()
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

            $customer = Customers::findOne(['api_token' => $apiToken]);
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $notifications = Notifications::find()->where(['key_id' => $customer->id, 'key_type' => 'customer', 'is_read' => 0])->orderBy(['id' => SORT_DESC])->all();
            if (!$notifications) {
                throw new Exception(Yii::t('app', 'No notifications found'));
            }

            $notifications = array_map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'dateCreated' => $notification->date_created,
                    'data' => $notification->data_values,
                    'isRead' => $notification->is_read == 1 ? true : false,
                    'notificationType' => $notification->not_type
                ];
            }, $notifications);

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Notifications retrieved successfully'),
                'data' => $notifications
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

            $customer = Customers::findOne(['api_token' => $apiToken]);
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $notifications = Notifications::find()->where(['key_id' => $customer->id, 'key_type' => 'customer', 'is_read' => 0])->all();
            if (!$notifications) {
                throw new Exception(Yii::t('app', 'No notifications found'));
            }

            foreach ($notifications as $notification) {
                $notification->is_read = 1;
                $notification->save();
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'All notifications marked as read'),
                'data' => []
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

    public function actionGetVendorRequests($vendorId, $page = 1, $pageSize = 10)
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

            $customer = Customers::findOne(['api_token' => $apiToken]);
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            //Ignore status 20 requests
            $requests = CustomerRequests::find()
            ->where(['vendor_id' => $vendorId, 'customer_id' => $customer->id])
            ->andWhere('status != :status', [':status' => StatusCodes::CUSTOMER_CANCELLED_REQUEST])
            ->orderBy(['id' => SORT_DESC])->limit($pageSize)->offset(($page - 1) * $pageSize)->all();
            if (!$requests) {
                throw new Exception(Yii::t('app', 'No requests found'));
            }

            $requestsList = [];
            foreach ($requests as $request) {
                //Is this a favorite vendor?
                $favorite = false;
                if ($request->vendor_id != null && CustomerFavorites::findOne(['customer_id' => $customer->id, 'vendor_id' => $request->vendor_id])) {
                    $favorite = true;
                }
                $address_coordinates = explode(',', $request->customerAddress->location_coordinates);
                $data = [
                    'id' => $request->id,
                    'total_amount' => $request->total_amount,
                    'volume_requested' => $request->volume_requested,
                    'status' => $request->status,
                    'date_created' => $request->date_created,
                    'delivery_date' => $request->delivery_date,
                    'delivery_notes' => $request->delivery_notes,
                    'is_shared_order' => $request->is_shared_request,
                    'vendor' => $request->vendor_id != null ? [
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
                    ] : null,
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
                $requestsList[] = $data;
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Requests retrieved successfully'),
                'data' => $requestsList
            ]);
        }
        catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }
}
