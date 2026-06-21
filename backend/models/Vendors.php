<?php

namespace backend\models;

use backend\helpers\Helpers;
use backend\helpers\StatusCodes;
use Yii;

/**
 * This is the model class for table "vendors".
 *
 * @property int $id
 * @property string $first_name
 * @property string $other_names
 * @property string $mobile_number
 * @property string $owner_rental
 * @property string $owners_name
 * @property string|null $owner_phone_number
 * @property string|null $govt_reg
 * @property int $residential_location
 * @property int $vendor_group
 * @property int $water_source
 * @property int $tank_volume
 * @property float|null $available_volume 
 * @property string|null $borehole_id
 * @property string $kiosk_id
 * @property int|null $rating
 * @property string $location_coordinates
 * @property string|null $location_description
 * @property string|null $specific_locations
* @property int|null $restrict_to_specific_locations
 * @property int $status
 * @property string $date_created
 * @property string|null $date_modified
 * @property string|null $display_pic
 * @property string|null $auth_key
 * @property string $password_hash
 * @property string $api_token
 * @property string $device_token
 * @property int $currency_id
 * @property string $code
 * @property int $is_online
 * @property float|null $wallet_balance
 * @property int $moq
 * @property string $vendor_type
 * @property string|null $operating_hours
 * @property string|null $vehicle_registration
 * @property float|null $price_of_water
 *  
 * @property VendorGroups $vendorGroup
 * @property WaterSources $waterSource
 */
class Vendors extends \yii\db\ActiveRecord
{

    public $imageFile;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendors';
    }

    const VENDOR_TYPE_KIOSK = 'kiosk';
    const VENDOR_TYPE_VENDOR = 'vendor';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'other_names', 'mobile_number', 'owner_rental', 'owners_name', 'residential_location', 'vendor_group', 'water_source', 'tank_volume', 'kiosk_id', 'status', 'date_created'], 'required'],
            [['owner_rental'], 'string'],
            [['vendor_group', 'water_source', 'tank_volume', 'status'], 'integer'],
            [['date_created', 'date_modified'], 'safe'],
            [['first_name', 'govt_reg', 'kiosk_id', 'location_coordinates'], 'string', 'max' => 100],
            [['other_names', 'owners_name'], 'string', 'max' => 250],
            [['mobile_number', 'borehole_id', 'vehicle_registration'], 'string', 'max' => 20],
            [['owner_phone_number'], 'string', 'max' => 50],
            [['vendor_group'], 'exist', 'skipOnError' => true, 'targetClass' => VendorGroups::class, 'targetAttribute' => ['vendor_group' => 'id']],
            [['water_source'], 'exist', 'skipOnError' => true, 'targetClass' => WaterSources::class, 'targetAttribute' => ['water_source' => 'id']],
            ['mobile_number', 'validatePhoneNumber'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'maxSize' => 2 * 1024 * 1024],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currencies::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['available_volume'], 'number'],
            [['wallet_balance'], 'number'],
            [['wallet_balance'], 'default', 'value' => 0],
            [['vendor_type'], 'default', 'value' => 'vendor'],
            [['price_of_water'], 'number'],
        ];
    }

    /**
     * validates the input phone number
     */
    public function validatePhoneNumber()
    {
        if (!Helpers::formatMsisdn($this->mobile_number)) {
            $this->addError('mobile_number', 'Enter valid phone number in the format 634070906 or +252634070906');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'other_names' => 'Other Names',
            'mobile_number' => 'Mobile Number',
            'owner_rental' => 'Owner Rental',
            'owners_name' => 'Owners Name',
            'owner_phone_number' => 'Owner Phone Number',
            'govt_reg' => 'Govt Reg',
            'residential_location' => 'Residential Location',
            'vendor_group' => 'Vendor Group',
            'water_source' => 'Water Source',
            'tank_volume' => 'Tank Volume (barrels)',
            'available_volume' => Yii::t('app', 'Available Volume'),
            'borehole_id' => 'Borehole ID',
            'kiosk_id' => 'Kiosk ID',
            'location_coordinates' => 'Location Coordinates',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_modified' => 'Date Modified',
            'display_pic' => 'Profile Picture',
            'moq' => 'Minimum Order Quantity (barrels)',
            'vendor_type' => 'Vendor Type',
            'operating_hours' => 'Operating Hours', 
            'vehicle_registration' => 'Vehicle Registration',
            'price_of_water' => 'Price Of Water',
        ];
    }

    /**
     * Handle Image Upload
     */
    public function upload()
    {
        if ($this->validate()) {
            $filePath = 'uploads/vendors/' . uniqid() . '.' . $this->imageFile->extension;
            if ($this->imageFile->saveAs($filePath)) {
                $this->display_pic = $filePath; // Save file path to DB
                return true;
            }
        }
        return false;
    }


    /**
     * Gets query for [[VendorGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendorGroup()
    {
        return $this->hasOne(VendorGroups::class, ['id' => 'vendor_group']);
    }

    /**
     * Gets query for [[WaterSource]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWaterSource()
    {
        return $this->hasOne(WaterSources::class, ['id' => 'water_source']);
    }

    /** 
     * Gets query for [[VendorCertifications]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */
    public function getVendorCertifications()
    {
        return $this->hasMany(VendorCertifications::class, ['vendor_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRequests()
    {
        return $this->hasMany(CustomerRequests::class, ['vendor_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerFavorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerFavorites()
    {
        return $this->hasMany(CustomerFavorites::class, ['vendor_id' => 'id']);
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currencies::class, ['id' => 'currency_id']);
    }

    /**
     * Gets the average rating for the vendor
     * @return float
     */
    public function getAverageRating()
    {
        return (float) Ratings::find()
            ->where(['vendor_id' => $this->id])
            ->average('rating') ?? 0;
    }

    /**
     * Gets the total number of ratings
     * @return int
     */
    public function getTotalRatings()
    {
        return (int) Ratings::find()
            ->where(['vendor_id' => $this->id])
            ->count();
    }

    /**
     * Gets the breakdown of ratings (how many 1s, 2s, 3s, etc.)
     * @return array
     */
    public function getRatingBreakdown()
    {
        $breakdown = Ratings::find()
            ->select(['rating', 'COUNT(*) as count'])
            ->where(['vendor_id' => $this->id])
            ->groupBy('rating')
            ->indexBy('rating')
            ->asArray()
            ->all();

        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = isset($breakdown[$i]) ? (int)$breakdown[$i]['count'] : 0;
        }
        return $result;
    }

    /**
     * Gets the ratings with customer details
     * @param int $limit Optional limit
     * @return Ratings[]
     */
    public function getRatings($limit = null)
    {
        $query = Ratings::find()
            ->where(['vendor_id' => $this->id])
            ->orderBy(['date_created' => SORT_DESC]);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->all();
    }

    /**
     * Gets the total amount transacted
     * @return float
     */
    public function getTotalAmountTransacted()
    {
        return (float) CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->andWhere('status in (' . StatusCodes::DELIVERED_CUSTOMER_REQUEST . ', ' . StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST .', ' . StatusCodes::COMPLETED_CUSTOMER_REQUEST . ')')
            ->sum('total_amount') ?? 0;
    }

    /**
     * Gets the total number of requests handled
     * @return int
     */
    public function getTotalRequests()
    {
        return (int) CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->count();
    }

    /**
     * Gets the total volume of water delivered
     * @return float
     */
    public function getTotalVolumeDelivered()
    {
        return (float) CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->sum('volume_requested') ?? 0;
    }

    /**
     * Gets recent deliveries
     * @param int $limit Number of deliveries to return
     * @return CustomerRequests[]
     */
    public function getRecentDeliveries($limit = 5)
    {
        return CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->orderBy(['delivery_date' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    public function getTotalEarnings()
    {
        // Implement the logic to calculate total earnings
        return CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->andWhere('status in (' . StatusCodes::DELIVERED_CUSTOMER_REQUEST . ', ' . StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST .', ' . StatusCodes::COMPLETED_CUSTOMER_REQUEST . ')')
            ->sum('total_amount') ?? 0;
    }

    public function getTotalDeliveries()
    {
        // Implement the logic to count total deliveries
        return CustomerRequests::find()
            ->where(['vendor_id' => $this->id])
            ->andWhere('status in (' . StatusCodes::DELIVERED_CUSTOMER_REQUEST . ', ' . StatusCodes::PAYMENT_INITIATED_CUSTOMER_REQUEST .', ' . StatusCodes::COMPLETED_CUSTOMER_REQUEST . ')')
            ->count();
    }
}
