<?php

namespace api\modules\v1\controllers;

use backend\helpers\StatusCodes;
use Exception;
use yii;
use yii\helpers\Json;
use backend\models\Vendors;
use backend\models\Customers;
use backend\models\CustomerFavorites;
use backend\models\VendorCertifications;
use backend\models\VendorGroups;
use backend\models\AppSettings;
use api\helpers\Helpers;
class VendorsController extends ApiController
{
    /**
     * Register a new Customer
     */
    public function actionIndex($api_token, $language = "en-US", $vendor_groups = '')
    {
        Yii::$app->language = $language;
        try {
            $user = Customers::findOne(['api_token' => $api_token, 'status' => StatusCodes::ACTIVE_STATUS]);
            if (!$user) {
                throw new Exception(Yii::t('app', 'Customer Not Found'));
            }

            $vendors = Vendors::find();

            if($vendor_groups != '') {
                $vendors->where("vendor_group IN ($vendor_groups)");
            }

            $vendors->andWhere('status = :status', [':status' => StatusCodes::ACTIVE_STATUS]);
            $result = $vendors->all();

            $listOfVendors = [];

            foreach($result as $vendor) {
                $isFavorite = false;
                if(CustomerFavorites::findOne(['customer_id' => $user->id, 'vendor_id' => $vendor->id])) {
                    $isFavorite = true;
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
                    'isFavorite' => $isFavorite,
                    'date_created' => $vendor->date_created,
                    'rating' => $vendor->rating,
                    'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                    'isOnline' => $vendor->is_online == 1 ? true : false,
                    'price_of_water' => Yii::$app->params['price_of_water'],
                    'moq' => $vendor->moq,
                    'restrictToSpecificLocations' => $vendor->restrict_to_specific_locations,
                    'specificLocations' => $vendor->specific_locations,
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
                $listOfVendors[] = $data;
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Vendors retrieved successfully',
                'data' => array_values($listOfVendors)
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

    public function actionFind($id, $api_token, $language = 'en-US', $vendor_groups = '')
    {
        //Find the Vendor
        Yii::$app->language = $language;
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $vendor = Vendors::find()->where(['id' => $id])->one();
            if (!$vendor) {
                throw new Exception(Yii::t('app', 'Vendor details not found'));
            }

            $favorite = false;
            if(CustomerFavorites::findOne(['customer_id' => $customer->id, 'vendor_id' => $vendor->id])) {
                $favorite = true;
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
                'isFavorite' => $favorite,
                'date_created' => $vendor->date_created,
                'rating' => $vendor->rating,
                'dp' => $vendor->display_pic ?? 'images/water_tank.png',
                'price_of_water' => Yii::$app->params['price_of_water'],
                'isOnline' => $vendor->is_online == 1 ? true : false,
                'moq' => $vendor->moq,
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
                'message' => 'Vendor retrieved successfully',
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

    public function actionFilter() {
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
            
            // Build query
            $query = Vendors::find();

            // Active vendors only
            $query->andWhere(['vendors.status' => StatusCodes::ACTIVE_STATUS]);

            // Filter by vendor_type_ids if provided
            if (isset($decodedData['vendor_type_ids']) && !empty($decodedData['vendor_type_ids'])) {
                // Parse comma-separated vendor group IDs
                $vendorGroupIds = array_map('trim', explode(',', $decodedData['vendor_type_ids']));
                $vendorGroupIds = array_filter($vendorGroupIds, function($id) {
                    return is_numeric($id) && $id > 0;
                });
                
                if (!empty($vendorGroupIds)) {
                    $query->andWhere(['vendors.vendor_group' => $vendorGroupIds]);
                }
            }
            // If vendor_type_ids is not set, don't filter by vendor groups (return all vendors)

            // Filter by is_online status based on include_offline
            $includeOffline = isset($decodedData['include_offline']) ? (int)$decodedData['include_offline'] : "0";
            if ($includeOffline == "0") {
                // Only show online vendors (is_online = 1)
                $query->andWhere(['vendors.is_online' => 1]);
            } else {
                // Show both online and offline vendors
                $query->andWhere(['or', ['vendors.is_online' => 0], ['vendors.is_online' => 1]]);
            }
            // If include_offline = 1, show both online and offline vendors (no filter)

            // Filter by favorites_only if requested
            $favoritesOnly = isset($decodedData['favorites_only']) ? (int)$decodedData['favorites_only'] : 0;
            if ($favoritesOnly == 1) {
                $favoriteVendorIds = CustomerFavorites::find()
                    ->select('vendor_id')
                    ->where(['customer_id' => $customer->id, 'status' => StatusCodes::ACTIVE_STATUS])
                    ->column();
                if (empty($favoriteVendorIds)) {
                    // No favorites found, return empty array
                    $this->setHeader(200);
                    return $this->asJson([
                        'status' => true,
                        'message' => 'Vendors retrieved successfully',
                        'data' => [],
                        'pagination' => [
                            'page' => isset($decodedData['page']) ? (int)$decodedData['page'] : 1,
                            'pageSize' => isset($decodedData['pageSize']) ? (int)$decodedData['pageSize'] : 20,
                            'totalCount' => 0,
                            'totalPages' => 0
                        ]
                    ]);
                } else {
                    $query->andWhere(['vendors.id' => $favoriteVendorIds]);
                }
            }

            // Get total count before pagination for metadata
            $totalCount = (clone $query)->count();

            // Pagination
            $page = isset($decodedData['page']) ? (int)$decodedData['page'] : 1;
            $pageSize = isset($decodedData['pageSize']) ? (int)$decodedData['pageSize'] : 20;
            
            // Apply pagination
            $offset = ($page - 1) * $pageSize;
            $query->limit($pageSize)->offset($offset);
            
            $vendors = $query->all();
            
            // Format vendor data similar to actionIndex
            $listOfVendors = [];
            foreach($vendors as $vendor) {
                $coordinates = explode(',', $vendor->location_coordinates);
                // Get customer coordinates from primary address
                $customerLat = 0;
                $customerLon = 0;
                if ($customer->primary_address && $customer->primaryAddress) {
                    $customerCoords = explode(',', $customer->primaryAddress->location_coordinates);
                    $customerLat = $customerCoords[0] ?? 0;
                    $customerLon = $customerCoords[1] ?? 0;
                }

                //Corodinates not configured for this vendor, so we skip it
                if(($coordinates[0] == '9.5596741142129' || $coordinates[1] == '44.049097529431') && $vendor->status == 1) {
                    continue; 
                } 

                $isFavorite = false;
                if(CustomerFavorites::findOne(['customer_id' => $customer->id, 'vendor_id' => $vendor->id])) {
                    $isFavorite = true;
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
                $listOfVendors[] = $data;
            }

            //A
            usort($listOfVendors, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            
            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Vendors retrieved successfully',
                'data' => array_values($listOfVendors),
                'pagination' => [
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'totalCount' => $totalCount,
                    'totalPages' => ceil($totalCount / $pageSize)
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



