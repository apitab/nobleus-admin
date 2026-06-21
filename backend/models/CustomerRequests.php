<?php

namespace backend\models;
use backend\helpers\StatusCodes;
use backend\helpers\Helpers;
use backend\models\OutboundSms;
use backend\models\Notifications;

use api\helpers\Helpers as ApiHelpers;

use Yii;

/**
 * This is the model class for table "customer_requests".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $customer_address
 * @property float $volume_requested
 * @property float $total_amount
 * @property string|null $delivery_notes 
 * @property string $delivery_date 
 * @property int|null $payment_id
 * @property string|null $cancel_reason
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property int|null $vendor_id
 * @property int|null $is_shared_request 
 * @property int|null $cancel_track
 *
 * @property Customers $customer
 * @property CustomerAddress $customerAddress
 * @property Payments $payment
 * @property Payments[] $payments
 * @property Vendors $vendor
 * @property Ratings[] $ratings
 * @property Complaints[] $complaints 
 */
class CustomerRequests extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'customer_address', 'volume_requested', 'status', 'date_created', 'delivery_date'], 'required'],
            [['customer_id', 'customer_address', 'payment_id', 'status', 'vendor_id'], 'integer'],
            [['volume_requested', 'total_amount'], 'number'],
            [['cancel_reason'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['customer_address'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerAddress::class, 'targetAttribute' => ['customer_address' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customers::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::class, 'targetAttribute' => ['payment_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['vendor_id' => 'id']],
            [['is_shared_request'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'customer_address' => Yii::t('app', 'Customer Address'),
            'volume_requested' => Yii::t('app', 'Volume Requested'),
            'total_amount' => Yii::t('app', 'Total Amount'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'cancel_reason' => Yii::t('app', 'Cancel Reason'),
            'status' => Yii::t('app', 'Status'),
            'date_created' => Yii::t('app', 'Date Created'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'delivery_notes' => Yii::t('app', 'Delivery Notes'),
            'delivery_date' => Yii::t('app', 'Delivery Date'),
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customers::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[CustomerAddress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAddress()
    {
        return $this->hasOne(CustomerAddress::class, ['id' => 'customer_address']);
    }

    /**
     * Gets query for [[Payment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::class, ['id' => 'payment_id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payments::class, ['request_id' => 'id']);
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendors::class, ['id' => 'vendor_id']);
    }

    // Create a getter for formatted date
    public function getFormattedDate()
    {
        return Yii::$app->formatter->asDate($this->delivery_date, 'php:d-m-Y');
    }

    /**
     * Format date in human readable format (today, tomorrow, or date)
     */
    private function getHumanReadableDate($date)
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $deliveryDate = date('Y-m-d', strtotime($date));
        
        if ($deliveryDate === $today) {
            return 'today';
        } elseif ($deliveryDate === $tomorrow) {
            return 'tomorrow';
        } else {
            return date('j,F', strtotime($date)); // e.g., "24,July"
        }
    }

    /** 
     * Log a new notification for a vendor request 
     */
    public function sendCustomerNotifications($customer, $request, $vendor = true)
    {
        $message = ApiHelpers::getSMSTemplate('CUSTOMER_ORDER_CREATED', $request->language);
        $message = str_replace("%name%", $customer->alias, $message);
        $message = str_replace("%volume%", $request->volume_requested, $message);
        $message = str_replace("%vendor_name%", $vendor ? $request->vendor->other_names : Yii::t('app', 'Dhaamiye'), $message);
        $message = str_replace("%date%", $this->getHumanReadableDate($request->delivery_date), $message);
        $message = str_replace("%order_details%", "dhaamiye_user://order/" . $request->id, $message);
        $message = str_replace("%single_shared%", $request->is_shared_request == 1 ? Yii::t('app', 'Shared') : Yii::t('app', 'Single'), $message);

        //Send SMS to the customer
        $sms = new OutboundSms();
        $sms->msisdn = $customer->phone_number;
        $sms->message = $message;
        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->status = 'pending';
        $sms->save();

        //Notification
        $notification = new Notifications();
        $notification->device_id = $customer->device_token;
        $notification->data_values = json_encode([
            'request_id' => $request->id,
            'deep_link' => "dhaamiye_user://order/" . $request->id
        ]);
        $notification->key_type = 'customer';
        $notification->key_id = $customer->id;
        $notification->title = 'New Order';
        $notification->message = $message;
        $notification->type = 'order';
        $notification->not_type = 'success';
        $notification->status = StatusCodes::CREATE_STATUS;
        $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
        $notification->save();
    }

    public function sendVendorNotifications($vendor, $request)
    {
        //Send SMS to the vendor
        $template = Helpers::getSMSTemplate('VENDOR_ORDER_CREATED');
        $template = str_replace("%name%", $vendor->first_name, $template);
        $template = str_replace("%ref%", $request->id, $template);
        $template = str_replace("%volume%", $request->volume_requested, $template);
        $template = str_replace("%address%", $request->customerAddress->address, $template);
        $template = str_replace("%date%", $this->getHumanReadableDate($request->delivery_date), $template);
        $template = str_replace("%order_details%", "dhaamiye_vendor://order/" . $request->id, $template);
        $template = str_replace("%single_shared%", $request->is_shared_request == 1 ? Yii::t('app', 'Shared') : Yii::t('app', 'Single'), $template);

        //SMS
        $sms = new OutboundSms();
        $sms->msisdn = $request->vendor->mobile_number;
        $sms->message = $template;
        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->status = 'pending';
        $sms->save();

        //Notification
        if ($vendor->device_token != '') {
            $notification = new Notifications();
            $notification->device_id = $vendor->device_token;
            $notification->data_values = json_encode([
                'request_id' => $request->id,
                'deep_link' => "dhaamiye_vendor://order/" . $request->id
            ]);
            $notification->key_type = 'vendor';
            $notification->key_id = $vendor->id;
            $notification->title = 'New Order';
            $notification->message = $template;
            $notification->type = 'order';
            $notification->not_type = 'success';
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
            $notification->save();
        }
    }

    /** 
    * Gets query for [[Complaints]]. 
    * 
    * @return \yii\db\ActiveQuery 
    */ 
   public function getComplaints() 
   { 
       return $this->hasMany(Complaints::class, ['order_id' => 'id']); 
   }
   /**
 
   }
   /**
    * Gets query for [[Invoices]]. 
    * 
    * @return \yii\db\ActiveQuery 
    */ 
   public function getInvoices() 
   { 
       return $this->hasMany(Invoices::class, ['customer_request_id' => 'id']); 
   } 
 
   /** 
    * Gets query for [[Payment]].
    *
    * @return \yii\db\ActiveQuery
 
   }
   /**
    * Gets query for [[Ratings]]. 
    * 
    * @return \yii\db\ActiveQuery 
    */ 
   public function getRatings() 
   { 
       return $this->hasMany(Ratings::class, ['order_id' => 'id']); 
   } 
}
