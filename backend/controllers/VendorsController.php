<?php

namespace backend\controllers;


use backend\models\Withdrawals;
use yii;
use backend\models\Vendors;
use backend\models\VendorSearchForm;
use backend\models\WaterSources;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\helpers\StatusCodes;
use backend\models\Locations;
use backend\models\VendorGroups;
use backend\helpers\Helpers;
use backend\models\VendorCertifications;
use backend\models\CustomerRequests;
use yii\web\UploadedFile;
use yii\web\MethodNotAllowedHttpException;
use backend\models\Ratings;
use backend\models\VendorUpdates;
use backend\models\Notifications;
use backend\models\Payments;

/**
 * VendorsController implements the CRUD actions for Vendors model.
 */
class VendorsController extends CustomController
{

    public $layout = 'dashboard/main';
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Vendors models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new VendorSearchForm();
        $query = Vendors::find();
        $search = false;
        if ($this->request->get('VendorSearchForm')) {
            $search = true;
            //Set the vendor search form parameters
            //@todo validate this params
            $searchForm = $this->request->get('VendorSearchForm');
            $model->phone_number = $searchForm['phone_number'];
            $model->first_name = $searchForm['first_name'];
            $model->vendor_group = $searchForm['vendor_group'];
            $model->tank_volume = $searchForm['tank_volume'];
            $model->status = $searchForm['status'];
            $model->vendor_type = $searchForm['vendor_type'];

            //Define the query parameters
            if ($model->phone_number != "") {
                $model->phone_number = preg_replace('/[^0-9]/', '', $model->phone_number);
                $query->andWhere(['like', 'mobile_number', "%" . $model->phone_number . "%", false]);
            }

            if ($model->first_name != "") {
                $query->andWhere(['like', 'first_name', "%" . $model->first_name . "%", false]);
                $query->andWhere(['like', 'other_names', "%" . $model->first_name . "%", false]);
            }


            if ($model->vendor_group != "") {
                $query->andWhere("vendor_group = :vendor_group", [":vendor_group" => $model->vendor_group]);
            }

            if ($model->tank_volume != "") {
                $query->andWhere("tank_volume = :tank_volume", [":tank_volume" => $model->tank_volume]);
            }


            if ($model->status != "") {
                $query->andWhere("status = :status", [":status" => $model->status]);
            }

            if ($model->vendor_type != '') {
                $query->andWhere("vendor_type = :type", [":type" => $model->vendor_type]);
            }
        }

        //Ignore status 10 (pending registrations)
        $query->andWhere(['!=', 'status', 10]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search
        ]);
    }

    /**
     * Displays a single Vendors model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'totalAmountTransacted' => $model->getTotalAmountTransacted(),
            'totalRequests' => $model->getTotalRequests(),
            'totalVolume' => $model->getTotalVolumeDelivered(),
            'recent_deliveries' => $model->getRecentDeliveries(5),
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
            'ratings' => $model->getRatings(10), // Get last 10 ratings
        ]);
    }

    public function actionDeliveries($id)
    {
        $model = $this->findModel($id);

        //Find the list of customer deliveries
        $query = CustomerRequests::find()->where('vendor_id  = :vid', [':vid' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('deliveries', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'totalAmountTransacted' => $model->getTotalAmountTransacted(),
            'totalRequests' => $model->getTotalRequests(),
            'totalVolume' => $model->getTotalVolumeDelivered(),
            'recent_deliveries' => $model->getRecentDeliveries(5),
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
            'ratings' => $model->getRatings(10)
        ]);
    }

    public function actionViewPayments($id)
    {
        $model = $this->findModel($id);

        //Find the list of customer deliveries
        $query = Payments::find()
        ->joinWith(['request'])
        ->where(['customer_requests.vendor_id' => $model->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('payments', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'totalAmountTransacted' => $model->getTotalAmountTransacted(),
            'totalRequests' => $model->getTotalRequests(),
            'totalVolume' => $model->getTotalVolumeDelivered(),
            'recent_deliveries' => $model->getRecentDeliveries(5),
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
            'ratings' => $model->getRatings(10)
        ]);
    }

    public function actionWithdrawals($id)
    {
        $model = $this->findModel($id);

        //Find the list of customer deliveries
        $query = Withdrawals::find()
        ->where(['from_type' => 'vendor', 'from_id' => $model->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('withdrawals', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'totalAmountTransacted' => $model->getTotalAmountTransacted(),
            'totalRequests' => $model->getTotalRequests(),
            'totalVolume' => $model->getTotalVolumeDelivered(),
            'recent_deliveries' => $model->getRecentDeliveries(5),
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
            'ratings' => $model->getRatings(10)
        ]);
    }

    public function actionViewCertifications($id)
    {
        $model = $this->findModel($id);
        //Get the cettifications
        $certifications = VendorCertifications::findAll(['vendor_id' => $id]);
        return $this->render('view-certifications', [
            'model' => $model,
            'certifications' => $certifications,
            'totalAmountTransacted' => $model->getTotalAmountTransacted(),
            'totalRequests' => $model->getTotalRequests(),
            'totalVolume' => $model->getTotalVolumeDelivered(),
            'recent_deliveries' => $model->getRecentDeliveries(5),
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
            'ratings' => $model->getRatings(10), //
        ]);
    }

    public function actionViewRatings($id)
    {
        $model = $this->findModel($id);
        $ratings = Ratings::findAll(['vendor_id' => $id]);
        return $this->render('ratings', [
            'model' => $model,
            'ratings' => $ratings,
            'averageRating' => $model->getAverageRating(),
            'totalRatings' => $model->getTotalRatings(),
            'ratingBreakdown' => $model->getRatingBreakdown(),
        ]);
    }

    /**
     * Creates a new Vendors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Vendors();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            $password = rand(100000, 999999);
            $model->password_hash = Yii::$app->security->generatePasswordHash($password);

            if ($model->imageFile && $model->upload() && $model->save()) {
                //Send a message to the vendor
                $message = Helpers::getSMSTemplate('VENDOR_ACCOUNT_CREATED');
                $message = str_replace('%name%', $model->first_name, $message);
                $message = str_replace('%phone%', $model->mobile_number, $message);
                $message = str_replace('%password%', $password, $message);
                $message = Helpers::sendSMS($model->mobile_number, $message);
                Yii::$app->session->setFlash('success', 'Vendor created sucessfully');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        $locations = Locations::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $waterSources = WaterSources::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $vendorGroups = VendorGroups::findAll(['status' => StatusCodes::ACTIVE_STATUS]);

        return $this->render('create', [
            'model' => $model,
            'locations' => $locations,
            'waterSources' => $waterSources,
            'vendorGroups' => $vendorGroups
        ]);
    }

    /**
     * Updates an existing Vendors model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->imageFile) {
                $model->upload();
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Vendor details updated sucessfully');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                print_r($model->upload());
                die;
            }

        }

        $locations = Locations::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $waterSources = WaterSources::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $vendorGroups = VendorGroups::findAll(['status' => StatusCodes::ACTIVE_STATUS]);

        return $this->render('update', [
            'model' => $model,
            'locations' => $locations,
            'waterSources' => $waterSources,
            'vendorGroups' => $vendorGroups
        ]);
    }

    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);
        $password = rand(100000, 999999);
        $model->password_hash = Yii::$app->security->generatePasswordHash($password);
        $model->save();

        //Send a message to the vendor
        $message = Helpers::getSMSTemplate('VENDOR_ACCOUNT_RESET');
        $message = str_replace('%name%', $model->first_name, $message);
        $message = str_replace('%phone%', $model->mobile_number, $message);
        $message = str_replace('%password%', $password, $message);
        $message = Helpers::sendSMS($model->mobile_number, $message);
        Yii::$app->session->setFlash('success', 'Vendor password reset sucessfully');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Deletes an existing Vendors model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Vendors model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vendors the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vendors::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionReplyRating($id)
    {
        $rating = Ratings::findOne($id);
        if ($rating === null) {
            throw new NotFoundHttpException('The requested rating does not exist.');
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_reply_form', [
                'rating' => $rating
            ]);

        }

        throw new MethodNotAllowedHttpException();
    }


    public function actionProfileUpdates()
    {
        $model = new VendorSearchForm();
        $query = VendorUpdates::find()->where(['status' => 'pending']);
        $search = false;
        if ($this->request->get('VendorSearchForm')) {
            $search = true;
            //Set the vendor search form parameters
            //@todo validate this params
            $searchForm = $this->request->get('VendorSearchForm');
            $model->phone_number = $searchForm['phone_number'];
            $model->first_name = $searchForm['first_name'];

            //Define the query parameters
            if ($model->phone_number != "") {
                $model->phone_number = preg_replace('/[^0-9]/', '', $model->phone_number);
                $query->andWhere(['like', 'mobile_number', "%" . $model->phone_number . "%", false]);
            }

            if ($model->first_name != "") {
                $query->andWhere(['like', 'first_name', "%" . $model->first_name . "%", false]);
                $query->andWhere(['like', 'other_names', "%" . $model->first_name . "%", false]);
            }
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('vendor-updates', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search
        ]);
    }

    public function actionViewUpdateRequest($id, $lang = 'en')
    {
        Yii::$app->language = $lang;
        $model = VendorUpdates::findOne(['id' => $id]);
        if (!$model || $model == null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        //Load the vendor
        $vendor = Vendors::findOne(['id' => $model->vendor_id]);

        if (Yii::$app->request->isPost) {
            $decision = Yii::$app->request->post('decision');
            if ($decision === 'approve') {
                // Apply changes from the update request to the vendor
                $vendor->first_name = $model->first_name ?: $vendor->first_name;
                $vendor->other_names = $model->other_names ?: $vendor->other_names;
                $vendor->mobile_number = $model->mobile_number ?: $vendor->mobile_number;
                $vendor->tank_volume = $model->tank_volume ?: $vendor->tank_volume;
                $vendor->moq = $model->minimum_order_qty ?: $vendor->moq;
                $vendor->operating_hours = $model->operating_hours ?: $vendor->operating_hours;
                // $vendor->device_token = '';
                // $vendor->api_token = 'invalid';
                if ($model->vehicle_registration_number) {
                    // Existing schema uses 'vehicle_registration' on Vendors
                    $vendor->vehicle_registration = $model->vehicle_registration_number;
                }
                if ($vendor->save(false)) {
                    $model->setStatusToApproved();
                    $model->date_modified = date('Y-m-d H:i:s');
                    $model->save(false);

                    $notification = new Notifications();
                    $notification->device_id = $vendor->device_token;
                    $notification->key_type = 'vendor';
                    $notification->key_id = $vendor->id;
                    $notification->type = 'profile';
                    $notification->title = Yii::t('app', 'Profile Updated');
                    $notification->message = "Your vendor profile update request has been approved. Sign in out and In";
                    $notification->status = StatusCodes::CREATE_STATUS;
                    $notification->not_type = 'success';
                    $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                    $notification->data_values = json_encode([]);
                    $notification->save();
                    Yii::$app->session->setFlash('success', 'Vendor update verified and applied.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to apply vendor updates.');
                }
            } elseif ($decision === 'deny') {
                $model->setStatusToCancelled();
                $model->date_modified = date('Y-m-d H:i:s');
                $model->save(false);
                Yii::$app->session->setFlash('success', 'Vendor update request denied.');
            } else {
                Yii::$app->session->setFlash('error', 'Invalid action.');
            }
            return $this->redirect(['vendors/profile-updates']);
        }

        return $this->render('view-profile-update', [
            'vendor' => $vendor,
            'model' => $model
        ]);
    }

    public function actionRegistrations() {
        $model = new Vendors();
        $query = Vendors::find()->where(['status' => 10]);
        $search = false;
        
        if ($this->request->get('Vendors')) {
            $search = true;
            $searchForm = $this->request->get('Vendors');
            $phoneNumber = isset($searchForm['phone_number']) ? $searchForm['phone_number'] : '';
            
            if ($phoneNumber != "") {
                $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
                $query->andWhere(['like', 'mobile_number', "%" . $phoneNumber . "%", false]);
                $model->mobile_number = $phoneNumber;
            }
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('registrations', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search
        ]);
    }

    public function actionViewOrders() {

    }
}
