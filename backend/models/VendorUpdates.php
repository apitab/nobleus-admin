<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "vendor_updates".
 *
 * @property int $id
 * @property int $vendor_id
 * @property string|null $first_name
 * @property string|null $other_names
 * @property string|null $mobile_number
 * @property string|null $operating_hours
 * @property string|null $tank_volume
 * @property string|null $vehicle_registration_number
 * @property string|null $minimum_order_qty
 * @property string|null $dp
 * @property float|null $price_of_water
 * @property string $status
 * @property string $date_created
 * @property string|null $date_modified
 *
 * @property Vendors $vendor
 */
class VendorUpdates extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_updates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'other_names', 'mobile_number', 'operating_hours', 'tank_volume', 'vehicle_registration_number', 'minimum_order_qty', 'dp', 'price_of_water'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['vendor_id', 'date_created'], 'required'],
            [['vendor_id'], 'integer'],
            [['price_of_water'], 'number'],
            [['status'], 'string'],
            [['date_created', 'date_modified'], 'safe'],
            [['first_name', 'other_names'], 'string', 'max' => 100],
            [['mobile_number', 'vehicle_registration_number', 'minimum_order_qty'], 'string', 'max' => 20],
            [['operating_hours'], 'string', 'max' => 250],
            [['tank_volume'], 'string', 'max' => 10],
            [['dp'], 'string', 'max' => 200],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendors::class, 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'first_name' => 'First Name',
            'other_names' => 'Other Names',
            'mobile_number' => 'Mobile Number',
            'operating_hours' => 'Operating Hours',
            'tank_volume' => 'Tank Volume',
            'vehicle_registration_number' => 'Vehicle Registration Number',
            'minimum_order_qty' => 'Minimum Order Qty',
            'dp' => 'Dp',
            'price_of_water' => 'Price Of Water',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
        ];
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


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PENDING => 'pending',
            self::STATUS_APPROVED => 'approved',
            self::STATUS_CANCELLED => 'cancelled',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function setStatusToPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function setStatusToApproved()
    {
        $this->status = self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }
}
