<?php

namespace api\modules\vendor\controllers;

use api\modules\v1\controllers\ApiController;
use Exception;
use yii;
use yii\helpers\Json;

use backend\models\Vendors;
use backend\models\OutboundSms;
use api\helpers\Helpers;
use api\helpers\StatusCodes;
use backend\models\CustomerRequests;
use backend\helpers\StatusCodes as BackendStatusCodes;
use backend\models\VendorGroups;
use yii\web\UploadedFile;
use backend\models\AppSettings;
use backend\models\VendorCertifications;

class AuthController extends ApiController
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

    public function actionLogin()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['phone_number']) || !isset($decodedData['password'])) {
                throw new Exception(Yii::t('app', 'Missing Phone number or password'));
            }

            $phoneNumber = Helpers::formatMsisdn($decodedData['phone_number']);
            $vendor = Vendors::find()->where(['mobile_number' => $phoneNumber])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'));
            }

            if ($vendor->status == 10) {
                throw new Exception(Yii::t('app', 'Account pending activation. Contact support'));
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor is inactive'));
            }

            $vendorPassword = $decodedData['password'];
            if (!Yii::$app->security->validatePassword($vendorPassword, $vendor->password_hash)) {
                throw new Exception(Yii::t('app', 'Invalid password'));
            }

            $code = rand(10000, 99999);
            $vendor->code = $code;
            if (!$vendor->save()) {
                throw new Exception(Yii::t('app', 'System error. Please try again later or contact support'));
            }

            //Send OTP SMS with language support
            $lang = Yii::$app->request->get('lang', 'so');
            $template = Helpers::getSMSTemplate('ACTIVATION_SMS', $lang);
            $template = str_replace("%name%", $vendor->other_names, $template);
            $template = str_replace("%code%", $code, $template);
            $sms = new OutboundSms();
            $sms->msisdn = $vendor->mobile_number;
            $sms->message = $template;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();

            $coordinates = explode(',', $vendor->location_coordinates);
            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor logged in successfully'),
                'data' => [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'other_names' => $vendor->other_names,
                    'phone_number' => $vendor->mobile_number,
                    'dp' => $vendor->display_pic,
                    'api_token' => $vendor->api_token,
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'location_description' => $vendor->location_description != '' ? $vendor->location_description : Yii::t('app', 'No location description'),
                    'rating' => $vendor->rating,
                    'is_online' => $vendor->is_online,
                    'available_volume' => $vendor->available_volume,
                    'currency' => [
                        'name' => $vendor->currency->name,
                        'code' => $vendor->currency->code,
                        'symbol' => $vendor->currency->symbol,
                    ],
                    'vendor_group' => [
                        'name' => $vendor->vendorGroup->name,
                        'description' => $vendor->vendorGroup->description,
                    ],
                    'operating_hours' => $vendor->operating_hours,
                    'wallet_balance' => number_format($vendor->wallet_balance, 2),
                    'otp' => $code,

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

    public function actionSendCodeToPhone()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['phone_number'])) {
                throw new Exception(Yii::t('app', 'Missing Phone number'));
            }

            $vendor = Vendors::find()->where(['mobile_number' => $decodedData['phone_number']])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'));
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor is inactive'));
            }

            if ($vendor->status == StatusCodes::CREATE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor is not active'));
            }

            $code = rand(10000, 99999);
            $vendor->code = $code;
            $vendor->save();

            //Send OTP SMS with language support
            $lang = Yii::$app->request->get('lang', 'so');
            $template = Helpers::getSMSTemplate('ACTIVATION_SMS', $lang);
            $template = str_replace("%name%", $vendor->other_names, $template);
            $template = str_replace("%code%", $code, $template);
            $sms = new OutboundSms();
            $sms->msisdn = $vendor->mobile_number;
            $sms->message = $template;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'OTP sent to vendor successfully'),
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

    public function actionVerifyPhone()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['phone_number']) || !isset($decodedData['code']) || !isset($decodedData['device_token'])) {
                throw new Exception(Yii::t('app', 'Missing Phone number or code'));
            }

            $vendor = Vendors::find()->where(['mobile_number' => $decodedData['phone_number']])->one();
            //$vendor = Vendors::find()->where(['id'=>2])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'));
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor account is inactive, contact support'));
            }

            if ($vendor->status == 10) {
                throw new Exception(Yii::t('app', 'Account pending activation. Contact support'));
            }

            if ($vendor->code != $decodedData['code']) {
                throw new Exception(Yii::t('app', 'Invalid code'));
            }

            $vendor->api_token = Yii::$app->security->generateRandomString(32);
            $vendor->device_token = $decodedData['device_token'];
            $vendor->save();

            $coordinates = explode(',', $vendor->location_coordinates);
            $certification = VendorCertifications::find()
                    ->where([
                        'vendor_id' => $vendor->id,
                        'status' => StatusCodes::ACTIVE_STATUS
                    ])
                    ->andWhere(['>', 'expiry_date', date('Y-m-d H:i:s')])
                    ->orderBy(['expiry_date' => SORT_ASC])
                    ->one();
            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor verified successfully'),
                'data' => [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'other_names' => $vendor->other_names,
                    'phone_number' => $vendor->mobile_number,
                    'api_token' => $vendor->api_token,
                    'device_token' => $vendor->device_token,
                    'vendor' => [
                        'id' => $vendor->id,
                        'firstName' => $vendor->first_name,
                        'otherNames' => $vendor->other_names,
                        'phoneNumber' => $vendor->mobile_number,
                        'residentialLocation' => $vendor->residential_location,
                        'locationCoordinates' => $vendor->location_coordinates,
                        'tankVolume' => $vendor->tank_volume,
                        'isFavorite' => true,
                        'dateCreated' => $vendor->date_created,
                        'rating' => $vendor->rating,
                        'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                        'isOnline' => $vendor->is_online == 1 ? true : false,
                        'moq' => $vendor->moq,
                        'restrictToSpecificLocations' => $vendor->restrict_to_specific_locations,
                        'specificLocations' => $vendor->specific_locations,
                        'vendorGroup' => $vendor->vendorGroup->name,
                        'operatingHours' => $vendor->operating_hours,
                        'walletBalance' => number_format($vendor->wallet_balance, 2),
                        'priceOfWater' => $vendor->price_of_water > 0 ? $vendor->price_of_water : AppSettings::find()->one()->price_per_barrel,
                        'lat' => $coordinates[0],
                        'lon' => $coordinates[1],
                        'location' => $vendor->location_description != '' ? $vendor->location_description : Yii::t('app', 'No location description'),
                        'isCertified' => $certification ? true : false,
                        'certificationDetails' => $certification ? $certification->certification_details : '',
                        'certificationExpiryDate' => $certification ? $certification->expiry_date : '',
                        'certificationDate' => $certification ? $certification->date_modified : '',
                        'vehicleRegistration' => $vendor->vehicle_registration,
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

    public function actionForgotPassword()
    {
        try {
            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);

            if (!isset($decodedData['phone_number'])) {
                throw new Exception(Yii::t('app', 'Missing Phone number'));
            }

            $vendor = Vendors::find()->where(['mobile_number' => $decodedData['phone_number']])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'));
            }

            if ($vendor->status == StatusCodes::DELETE_STATUS) {
                throw new Exception(Yii::t('app', 'Vendor account is inactive, contact support'));
            }

            if ($vendor->status == 10) {
                throw new Exception(Yii::t('app', 'Account pending activation. Contact support'));
            }

            $code = rand(100000, 999999);
            $vendor->password_hash = Yii::$app->security->generatePasswordHash($code);

            if (!$vendor->save()) {
                throw new Exception(Yii::t('app', 'Failed to save vendor details.') . " " . json_encode($vendor->errors));
            }

            //Send OTP SMS with language support
            $lang = Yii::$app->request->get('lang', 'so');
            $template = Helpers::getSMSTemplate('FORGOT_PASSWORD_SMS', $lang);
            $template = str_replace("%name%", $vendor->other_names, $template);
            $template = str_replace("%code%", $code, $template);
            $sms = new OutboundSms();
            $sms->msisdn = $vendor->mobile_number;
            $sms->message = $template;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'OTP sent to vendor successfully'),
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


    public function actionSignup()
    {
        try {
            // Handle multipart/form-data from Flutter app
            $postData = Yii::$app->request->post();

            // Validate required fields
            if (!isset($postData['phoneNumber']) || empty($postData['phoneNumber'])) {
                throw new Exception(Yii::t('app', 'Missing Phone number'));
            }

            if (!isset($postData['firstName']) || empty($postData['firstName'])) {
                throw new Exception(Yii::t('app', 'Missing First name'));
            }

            if (!isset($postData['otherNames']) || empty($postData['otherNames'])) {
                throw new Exception(Yii::t('app', 'Missing Other names'));
            }

            // Check if vendor already exists
            $existingVendor = Vendors::findOne(['mobile_number' => Helpers::formatMsisdn($postData['phoneNumber'])]);
            if ($existingVendor) {
                throw new Exception(Yii::t('app', 'Vendor with this phone number already exists'));
            }

            // Create new vendor
            $vendor = new Vendors();
            $vendor->first_name = $postData['firstName'];
            $vendor->other_names = $postData['otherNames'];
            $vendor->mobile_number = Helpers::formatMsisdn($postData['phoneNumber']);

            // Optional fields
            if (isset($postData['vendorGroup']) && !empty($postData['vendorGroup'])) {
                $vendorGroup = VendorGroups::findOne(['id' => (int) $postData['vendorGroup']]);
                if ($vendorGroup) {
                    $vendor->vendor_group = $vendorGroup->id;
                } else {
                    throw new Exception(Yii::t('app', 'Invalid vendor group'));
                }
            } else {
                // Set default vendor group if not provided
                $defaultVendorGroup = VendorGroups::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->one();
                if ($defaultVendorGroup) {
                    $vendor->vendor_group = $defaultVendorGroup->id;
                } else {
                    throw new Exception(Yii::t('app', 'No vendor group available'));
                }
            }

            if (isset($postData['tankVolume']) && !empty($postData['tankVolume'])) {
                $vendor->tank_volume = (int) $postData['tankVolume'];
            } else {
                $vendor->tank_volume = 0; // Default value
            }

            if (isset($postData['vehicleRegistration']) && !empty($postData['vehicleRegistration'])) {
                $vendor->vehicle_registration = $postData['vehicleRegistration'];
            }

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

                // Save relative path to database (relative to backend web root)
                $vendor->display_pic = $uploadDir . $fileName;
            }

            // Set required fields with defaults
            $vendor->owner_rental = 'owner'; // Default value
            $vendor->owners_name = $vendor->first_name . ' ' . $vendor->other_names;
            $vendor->residential_location = 1; // Default location, should be set properly
            $vendor->water_source = 1; // Default water source, should be set properly
            $vendor->kiosk_id = 'VENDOR-' . time(); // Generate unique kiosk ID
            $vendor->status = StatusCodes::CREATE_STATUS; // Pending activation
            $vendor->date_created = $vendor->date_modified = date('Y-m-d H:i:s');
            $vendor->location_coordinates = '0,0'; // Default coordinates, should be updated
            $vendor->currency_id = 1; // Default currency
            $vendor->vendor_type = Vendors::VENDOR_TYPE_VENDOR;
            $vendor->wallet_balance = 0;
            $vendor->is_online = 0;

            // Generate password and API token
            $password = rand(100000, 999999);
            $vendor->password_hash = Yii::$app->security->generatePasswordHash($password);
            $vendor->api_token = Yii::$app->security->generateRandomString(32);
            $vendor->code = rand(10000, 99999);
            $vendor->status = 10; // Pending activation

            if (!$vendor->save()) {
                throw new Exception(Yii::t('app', 'Failed to create vendor account: ') . json_encode($vendor->errors));
            }

            // Send activation SMS
            $lang = Yii::$app->request->get('lang', 'so');
            $template = Helpers::getSMSTemplate('VENDOR_ACCOUNT_CREATED', $lang);
            $template = str_replace('%name%', $vendor->first_name, $template);
            $template = str_replace('%phone%', $vendor->mobile_number, $template);
            $template = str_replace('%password%', $password, $template);
            $sms = new OutboundSms();
            $sms->msisdn = $vendor->mobile_number;
            $sms->message = $template;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';
            $sms->save();

            $this->setHeader(ApiController::STATUS_OK);
            return $this->asJson([
                'status' => true,
                'message' => Yii::t('app', 'Vendor account created successfully. Contact support to activate your account.'),
                'data' => [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'other_names' => $vendor->other_names,
                    'phone_number' => $vendor->mobile_number,
                    'status' => $vendor->status
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

}