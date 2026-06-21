<?php

namespace backend\controllers;

use Yii;
use backend\models\Alerts;
use backend\models\Customers;
use backend\models\Notifications;
use backend\models\OutboundSms;
use backend\helpers\StatusCodes;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AlertsController implements the CRUD actions for Alerts model.
 */
class AlertsController extends CustomController
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
                        //'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Alerts models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Alerts::find(),

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
        ]);
    }

    /**
     * Creates a new Alerts model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Alerts();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->attachmentFile = UploadedFile::getInstance($model, 'attachmentFile');
            $model->date_created = Date('Y-m-d H:i:s');

            if ($model->attachmentFile) {
                $uploadResult = $model->upload();
                if ($uploadResult['status']) {
                    // upload() already validated and set attachment, so skip re-validation to avoid temp file errors
                    $model->attachmentFile = null;
                    if ($model->save(false)) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Alert created successfully'));
                        return $this->redirect(['alerts/index']);
                    }
                } else {
                    $model->addError('attachmentFile', $uploadResult['message'] ?? Yii::t('app', 'Failed to upload PDF file'));
                }
            } else {
                // No file uploaded, save normally
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Alert created successfully'));
                    return $this->redirect(['alerts/index']);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Alerts model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->attachmentFile = UploadedFile::getInstance($model, 'attachmentFile');

            if ($model->attachmentFile) {
                // Delete old attachment file if exists
                if ($model->attachment) {
                    $oldFilePath = Yii::getAlias('@webroot') . '/' . $model->attachment;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $uploadResult = $model->upload();
                if ($uploadResult['status']) {
                    // upload() already validated and moved the file; avoid re-validating the temp file
                    $model->attachmentFile = null;
                    if ($model->save(false)) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Alert updated successfully'));
                        return $this->redirect(['alerts/index']);
                    }
                } else {
                    $model->addError('attachmentFile', $uploadResult['message'] ?? Yii::t('app', 'Failed to upload PDF file'));
                }
            } else {
                // No new file uploaded, save normally
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Alert updated successfully'));
                    return $this->redirect(['alerts/index']);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Alerts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Alert deleted successfully'));
        return $this->redirect(['index']);
    }

    /**
     * Resends alerts to all active customers (status = 1)
     * @param int $id Alert ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionResendAlerts($id)
    {
        $alert = $this->findModel($id);
        
        // Find all active customers (status = 1)
        $customers = Customers::find()->where(['status' => StatusCodes::ACTIVE_STATUS])->all();
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($customers as $customer) {
            try {
                // Send Notification
                if ($customer->device_token) {
                    $notification = new Notifications();
                    $notification->device_id = $customer->device_token;
                    $notification->data_values = json_encode(['alert_id' => $alert->id]);
                    $notification->key_type = 'customer';
                    $notification->key_id = $customer->id;
                    $notification->title = $alert->title;
                    $notification->message = $alert->description;
                    $notification->type = 'alert';
                    $notification->not_type = Notifications::NOT_TYPE_ALERT;
                    $notification->status = StatusCodes::CREATE_STATUS;
                    $notification->is_read = 0;
                    $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');
                    
                    if ($notification->save()) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                }
                
                // Send SMS
                if ($customer->phone_number) {
                    $sms = new OutboundSms();
                    $sms->msisdn = $customer->phone_number;
                    $sms->message = $alert->title . ': ' . $alert->description;
                    $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                    $sms->status = 'pending';
                    $sms->save();
                }
            } catch (\Exception $e) {
                $failedCount++;
                Yii::error('Failed to send alert to customer ' . $customer->id . ': ' . $e->getMessage());
            }
        }
        
        $message = Yii::t('app', 'Alert resent successfully. Sent to {sent} customers', ['sent' => $sentCount]);
        if ($failedCount > 0) {
            $message .= '. Failed: ' . $failedCount;
        }
        
        Yii::$app->session->setFlash('success', $message);
        return $this->redirect(['index']);
    }

    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Alerts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
