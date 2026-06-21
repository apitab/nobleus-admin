<?php

namespace backend\controllers;

use backend\helpers\StatusCodes;
use backend\models\CustomerAddress;
use yii;
use backend\models\Locations;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use backend\models\Customers;

/**
 * CustomerAddressController implements the CRUD actions for CustomerAddress model.
 */
class CustomerAddressController extends CustomController
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
     * Creates a new CustomerAddress model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($customer_id)
    {
        $model = new CustomerAddress();
        //Load the user model
        $customer = Customers::findOne($customer_id);
        $model->customer_id = $customer_id;

        $locations = Locations::findAll(['status' => StatusCodes::ACTIVE_STATUS]);


        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->status = StatusCodes::ACTIVE_STATUS;
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success',Yii::t('app','Customer address has been added successfully'));
                return $this->redirect(Yii::$app->urlManager->createUrl(['customers/view','id'=>$customer->id]));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'locations' => $locations,
            'customer' => $customer
        ]);
    }

    /**
     * Updates an existing CustomerAddress model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $customer_id)
    {
        $model = $this->findModel($id, $customer_id);
        $customer = Customers::findOne($customer_id);
        $model->customer_id = $customer_id;

        $locations = Locations::findAll(['status' => StatusCodes::ACTIVE_STATUS]);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success',Yii::t('app','Customer address has been updated successfully'));
            return $this->redirect(Yii::$app->urlManager->createUrl(['customers/view','id'=>$customer->id]));
        }

        return $this->render('update', [
            'model' => $model,
            'locations'=>$locations,
            'customer' => $customer
        ]);
    }

    /**
     * Deletes an existing CustomerAddress model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $customer_id)
    {
        $this->findModel($id, $customer_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CustomerAddress model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CustomerAddress the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $customer_id)
    {
        if (($model = CustomerAddress::findOne(['id' => $id, 'customer_id' => $customer_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
