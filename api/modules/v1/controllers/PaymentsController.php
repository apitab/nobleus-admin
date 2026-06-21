<?php

namespace api\modules\v1\controllers;

use backend\helpers\StatusCodes;
use backend\models\OutboundSms;
use Exception;
use yii;
use yii\helpers\Json;

use backend\models\Customers;
use backend\models\CustomerRequests;
use backend\models\Payments;
use backend\models\PaymentStatus;
use backend\models\CustomerFavorites;
use backend\models\PaymentMethods;
use backend\models\Vendors;
use backend\models\Notifications;
use backend\models\Invoices;
class PaymentsController extends ApiController
{

    public function actionProcess($req_id)
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

            
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            if (!$request = CustomerRequests::find()->where('id = :id AND customer_id = :cid', [':cid' => $customer->id, ':id' => $req_id])->one()) {
                throw new Exception(Yii::t('app', 'Customer Request Not Found'));
            }

            $data = Yii::$app->request->getRawBody();
            $decodedData = Json::decode($data);
            $paymentMethodCode = $decodedData['payment_method'] ?? null;
            if(!$paymentMethodCode) {
                throw new Exception(Yii::t('app', 'Payment method is required'));
            }
            $phoneNumber = isset($decodedData['phone_number']) ? $decodedData['phone_number'] : null; 
            //Get the payment method
            if(!$paymentMethod = PaymentMethods::findOne(['code' => $paymentMethodCode])) {
                throw new Exception(Yii::t('app', 'Payment method not found'));
            }
            $paymentMethod = PaymentMethods::findOne(['code' => $paymentMethodCode]);
            if(!$paymentMethod) {
                throw new Exception(Yii::t('app', 'Payment method not found'));
            }

            if($paymentMethod->code == 'my-wallet') {
                if($customer->wallet_balance < $request->total_amount) {
                    throw new Exception(Yii::t('app', 'Insufficient balance'));
                }
            }

            if($paymentMethod->code == 'somtel') {
                if(!$phoneNumber) {
                    throw new Exception(Yii::t('app', 'Phone number is required'));
                }
            }

            //Check if we have an existing payment
            $payment = Payments::findOne(['request_id' => $request->id]);
            if($payment) {
                //Return success
                if($payment->status == StatusCodes::PAYMENT_SUCCESS) {
                    //Update the request status to completed
                    $request->status = StatusCodes::COMPLETED_CUSTOMER_REQUEST;
                    $request->save();
                }

                //Return success
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Payment already processed successfully'),
                    'data' => [
                        'request' => [
                            'id' => $request->id,
                            'status' => $request->status,
                            'total_amount' => $request->total_amount,
                            'volume_requested' => $request->volume_requested,
                        ]
                    ]
                ]);
            }

            //Create a new payment
            $invoice = null;
            if($paymentMethod->code == 'somtel') {
                $invoice = new Invoices();
                $invoice->customer_request_id = $request->id;
                $invoice->edahab_number = $phoneNumber;
                $invoice->amount = $request->total_amount;
                $invoice->currency = 'sls';
                $invoice->status = 'pending';
                $invoice->created_at = $invoice->updated_at = Date('Y-m-d H:i:s');
                $invoice->save();
            }

            $vendor = Vendors::findOne(['id' => $request->vendor_id]);
            if(!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor not found'
            ));
            }

            if($paymentMethod->code == 'my-wallet') {
                $customer->wallet_balance -= $request->total_amount;
                $customer->save();

                $vendor->wallet_balance += $request->total_amount;
                $vendor->save();
            }

            $payment = new Payments();
                $payment->request_id = $request->id;
                $payment->amount = $request->total_amount;
                $payment->payment_method_id = $paymentMethod->id;
                $payment->status = StatusCodes::PAYMENT_NEW;
                $payment->description = 'Payment for Water delivery';
                $payment->date_created = $payment->date_modified = Date('Y-m-d H:i:s');
                $payment->phone_number = $paymentMethod->requires_phone == "1" ? $decodedData['phone_number'] : null;
                
                //Handle status codes for different payment methods
                switch($paymentMethod->id) {
                    case 1:
                        $request->status = StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST;
                        $payment->status = StatusCodes::PAYMENT_NEW;
                        break;
                    case 2:
                    case 3:
                        $request->status = StatusCodes::PAYMENT_AWAITING_CONFIRMATION;
                        $payment->status = StatusCodes::PAYMENT_CUSTOMER_CONFIRMED;
                        break;
                    case 4:
                        $request->status = StatusCodes::COMPLETED_CUSTOMER_REQUEST;
                        $payment->status = StatusCodes::PAYMENT_SUCCESS;
                        break;
                    case 5: 
                        $request->status = StatusCodes::PAYMENT_PAY_LATER_CUSTOMER_REQUEST;
                        $payment->status = StatusCodes::PAYMENT_PAY_LATER;
                        break;
                }
                if(!$payment->save()) {
                    //undo the wallet balance changes
                    if($paymentMethod->code == 'my-wallet') {
                        $customer->wallet_balance += $request->total_amount;
                        $customer->save();
                        $vendor->wallet_balance -= $request->total_amount;
                        $vendor->save();
                    }
                    if($paymentMethod->code == 'somtel') {
                        $invoice->delete();
                    }
                    throw new Exception(Yii::t('app', 'Error processing payment ' . Json::encode($payment->errors)));
                } else {
                    $request->payment_id = $payment->id;
                    $request->save();
                }

                //Send notification to the vendor
                $sms = new OutboundSms();
                $sms->msisdn = $vendor->mobile_number;
                $sms->message = 'You have received a payment of ' . $payment->amount . ' from ' . $customer->alias . ' for request ' . $request->id . ' using ' . $paymentMethod->name;
                $sms->status = 'pending';
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->save();

                $notification = new Notifications();
                $notification->device_id = $vendor->device_token;
                $notification->data_values = json_encode(['request_id' => $request->id, 'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST]);
                $notification->key_type = 'vendor';
                $notification->key_id = $vendor->id;
                $notification->type = 'request';
                $notification->title = Yii::t('app', 'Payment received');
                $notification->message = 'You have received a payment of ' . $payment->amount . ' from ' . $customer->alias . ' for request ' . $request->id . ' using ' . $paymentMethod->name;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();  
                
                //Send notification to the customer 
                $sms = new OutboundSms();
                $sms->msisdn = $customer->phone_number;
                $sms->message = 'You have made a payment of ' . $payment->amount . ' to ' . $vendor->other_names . ' for request ' . $request->id . ' using ' . $paymentMethod->name;
                $sms->status = 'pending';
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->save();

                $notification = new Notifications();
                $notification->device_id = $customer->device_token;

                $notification->data_values = json_encode(['request_id' => $request->id, 'status' => StatusCodes::COMPLETED_CUSTOMER_REQUEST]);
                $notification->key_type = 'customer';
                $notification->key_id = $customer->id;
                $notification->type = 'request';
                $notification->title = Yii::t('app', 'Payment received');
                $notification->message = 'You have made a payment of ' . $payment->amount . ' to ' . $vendor->other_names . ' for request ' . $request->id . ' using ' . $paymentMethod->name;
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->not_type = 'success';
                $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                $notification->save();

                //Return success
                return $this->asJson([
                    'status' => true,
                    'message' => Yii::t('app', 'Payment processed successfully'),
                    'data' => [
                        'request' => [
                            'id' => $request->id,
                            'status' => $request->status,
                            'total_amount' => $request->total_amount,
                            'volume_requested' => $request->volume_requested,
                        ]
                    ]
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
