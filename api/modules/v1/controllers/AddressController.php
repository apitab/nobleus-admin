<?php

namespace api\modules\v1\controllers;

use backend\helpers\StatusCodes;
use Exception;
use yii;
use yii\helpers\Json;
use yii\db\IntegrityException;
use backend\models\Customers;
use backend\models\CustomerAddress;

class AddressController extends ApiController
{
    public function actionSave() {
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

            if (!isset($decodedData['address']) || !isset($decodedData['latitude']) || !isset($decodedData['longitude'])) {
                throw new Exception("Missing Address details or coordinates");
            }

            $address = new CustomerAddress();
            $address->status = StatusCodes::ACTIVE_STATUS;
            $address->date_created = $address->date_modified = Date('Y-m-d H:i:s');
            $address->location_coordinates = $decodedData['latitude'] . ',' . $decodedData['longitude'];
            $address->address = $decodedData['address'];
            $address->location_id = 1;
            $address->customer_id = $customer->id;

            if ($address->save()) {
                $coordinates = explode(',', $address->location_coordinates);
                $customer->primary_address = $address->id;
                $customer->save();

                $this->setHeader(200);
                return $this->asJson([
                    'status' => true,
                    'message' => 'Address added successfully',
                    'data' => [
                        'id' => $address->id,
                        'address' => $address->address,
                        'latitude' => $coordinates[0],
                        'longitude' => $coordinates[1],
                    ]
                ]);
            }

            throw new Exception(json_encode($address->errors));
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage() //Yii::t('app', 'Error processing Request')
            ]);
        }
    }

    // Find List of addresses
    public function actionAll()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            throw new Exception("Missing or invalid Authorization header");
        }

        $apiToken = substr($authHeader, 7); 

        try {
            $customer = Customers::find()->where(['api_token' => $apiToken])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }


            $addresses = CustomerAddress::find()->where(['customer_id' => $customer->id])->orderBy(['id' => SORT_DESC])->all();
            $listOfAddresses = [];

            foreach ($addresses as $address) {
                $coordinates = explode(',', $address->location_coordinates);
                $listOfAddresses[] = [
                    'address' => $address->address,
                    'latitude' => $coordinates[0],
                    'longitude' =>  $coordinates[1],
                    'default' => $address->id == $customer->primary_address ? true : false,
                    'user_id' => $address->customer_id,
                    'id' => $address->id
                ];
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Addresses retrieved successfully',
                'data' => array_values($listOfAddresses)
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

    // Delete an address
    public function actionDeleteAddress()
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
                throw new Exception("Address ID is required");
            }

            $address = CustomerAddress::find()
                ->where(['id' => $decodedData['address_id'], 'customer_id' => $customer->id])
                ->one();

            if (!$address) {
                throw new Exception("Address not found or you don't have permission to delete it");
            }

            // If this is the primary address, we need to update the customer's primary_address
            if ($customer->primary_address == $address->id) {
                // Find another address to set as primary
                $newPrimaryAddress = CustomerAddress::find()
                    ->where(['customer_id' => $customer->id])
                    ->andWhere(['!=', 'id', $address->id])
                    ->one();

                if ($newPrimaryAddress) {
                    $customer->primary_address = $newPrimaryAddress->id;
                    $customer->save();
                } else {
                    $customer->primary_address = null;
                    $customer->save();
                }
            }

            try {
                if ($address->delete()) {
                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Address deleted successfully',
                        'data' => null
                    ]);
                }
                throw new Exception("Failed to delete address");
            } catch (IntegrityException $e) {
                // If there's an integrity constraint violation, update status to inactive instead
                $address->status = StatusCodes::DELETE_STATUS;
                $address->date_modified = Date('Y-m-d H:i:s');
                if ($address->save()) {
                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Address marked as inactive successfully',
                        'data' => null
                    ]);
                }
                throw new Exception("Failed to update address status");
            }
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'data' => '',
                'message' => $e->getMessage()
            ]);
        }
    }
}
