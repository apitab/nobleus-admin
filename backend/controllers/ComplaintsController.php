<?php

namespace backend\controllers;

use yii;
use backend\models\Complaints;
use backend\models\ComplaintsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\OutboundSms;
use backend\models\Notifications;
use backend\helpers\StatusCodes;
/**
 * ComplaintsController implements the CRUD actions for Complaints model.
 */
class ComplaintsController extends Controller
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
     * Lists all Complaints models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ComplaintsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Complaints model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Handle form submission
        if ($model->load(Yii::$app->request->post())) {
            // If "Mark as Resolved" button was clicked
            if (Yii::$app->request->post('resolve')) {
                $model->status = 3; // Set status to Resolved

                //Send message to customer or vendor based on type
                //@todo Add language translation. How do we get the language?
                $message = "Your complaint " . $model->title . " has been resolved REF " . $model->id . ". Please contact us if you have any further questions.";
                
                if ($model->type === Complaints::TYPE_CUSTOMERS) {
                    $customer = $model->customer;
                    if ($customer) {
                        // Send SMS to customer
                $sms = new OutboundSms();
                        $sms->msisdn = $customer->phone_number;
                $sms->message = $message;
                $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                $sms->status = 'pending';
                $sms->save();

                        // Send notification to customer
                        if ($customer->device_token) {
                $notification = new Notifications();
                            $notification->device_id = $customer->device_token;
                $notification->data_values = json_encode(['complaint_id' => $model->id]);
                $notification->key_type = 'customer';
                            $notification->key_id = $customer->id;
                            $notification->title = 'Issue Status Update';
                            $notification->message = $message;
                            $notification->type = 'issue';
                            $notification->not_type = 'success';
                            $notification->status = StatusCodes::CREATE_STATUS;
                            $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
                            $notification->save();
                        }
                    }
                } elseif ($model->type === Complaints::TYPE_VENDORS) {
                    $vendor = $model->vendor;
                    if ($vendor) {
                        // Send SMS to vendor
                        $sms = new OutboundSms();
                        $sms->msisdn = $vendor->mobile_number;
                        $sms->message = $message;
                        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                        $sms->status = 'pending';
                        $sms->save();

                        // Send notification to vendor
                        if ($vendor->device_token) {
                            $notification = new Notifications();
                            $notification->device_id = $vendor->device_token;
                            $notification->data_values = json_encode(['complaint_id' => $model->id]);
                            $notification->key_type = 'vendor';
                            $notification->key_id = $vendor->id;
                $notification->title = 'Issue Status Update';
                $notification->message = $message;
                $notification->type = 'issue';
                $notification->not_type = 'success';
                $notification->status = StatusCodes::CREATE_STATUS;
                $notification->date_created = $notification->date_modified = date('Y-m-d H:i:s');
                $notification->save();
                        }
                    }
                }
            }

            // Get resolution notes from POST data
            $model->resolution_notes = Yii::$app->request->post('Complaints')['resolution_notes'];

            // Update the modification date
            $model->date_modified = date('Y-m-d H:i:s');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Complaint has been updated successfully.'));
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t(
                    'app',
                    'There was an error updating the complaint: ' . json_encode($model->errors)
                ));
            }
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function sendMessage($phone_number, $message)
    {
        $sms = new OutboundSms();
        $sms->msisdn = $phone_number;
        $sms->message = $message;
        $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
        $sms->status = 'pending';
        $sms->save();
    }

    /**
     * Creates a new Complaints model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Complaints();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Complaints model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Complaints model.
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
     * Finds the Complaints model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Complaints the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Complaints::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
