<?php

namespace backend\controllers;

use backend\helpers\StatusCodes;
use backend\models\CustomerRequests;
use backend\models\PaymentMethods;
use backend\models\Payments;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\PaymentsSearchForm;
/**
 * PaymentsController implements the CRUD actions for Payments model.
 */
class PaymentsController extends Controller
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
     * Lists all Payments models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model=new PaymentsSearchForm();
        $query = Payments::find();
        $search = false;
        $searchText = "";
        if ($this->request->get('PaymentsSearchForm')) {
            $search = true;
            $searchForm = $this->request->get('PaymentsSearchForm');
            $model->customerPhoneNumber = $searchForm['customerPhoneNumber'];
            $model->vendorPhoneNumber = $searchForm['vendorPhoneNumber'];
            $model->dateFrom = $searchForm['dateFrom'];
            $model->dateTo = $searchForm['dateTo'];
            $model->paymentMethod = $searchForm['paymentMethod'];

            if ($model->customerPhoneNumber != "") {
                $model->customerPhoneNumber = preg_replace('/[^0-9]/', '', $model->customerPhoneNumber);
                $query->joinWith(['request.customer'])
                      ->andWhere(['like', 'customers.phone_number', "%" . $model->customerPhoneNumber . "%", false]);
                $searchText .= "/ Customer Phone: {$model->customerPhoneNumber}";
            }   

            if ($model->vendorPhoneNumber != "") {
                $model->vendorPhoneNumber = preg_replace('/[^0-9]/', '', $model->vendorPhoneNumber);
                $query->joinWith(['request.vendor'])
                      ->andWhere(['like', 'vendors.mobile_number', "%" . $model->vendorPhoneNumber . "%", false]);
                $searchText .= "/ Vendor Phone: {$model->vendorPhoneNumber}";
            }

            if ($model->dateFrom != "") {
                $query->andWhere(['>=', 'date_created', $model->dateFrom]);
                $searchText .= "/ Date From: {$model->dateFrom}";
            }

            if ($model->dateTo != "") {
                $query->andWhere(['<=', 'date_created', $model->dateTo]);
                $searchText .= "/ Date From: {$model->dateTo}";
            }

            if ($model->paymentMethod != "") {
                $query->andWhere(['payment_method_id' => $model->paymentMethod]);
                //@todo Add payment method name
                $searchText .= "/ Payment Method: {$model->paymentMethod}";
            }

            // if ($model->status != "") {
            //     $query->andWhere(['status' => $model->status]);
            // }
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
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search,
            'searchText' => $searchText 
        ]);
    }

    /**
     * Displays a single Payments model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Updates an existing Payments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($rid, $id = null)
    {
        $model = new Payments();//If we are creating a new payment 
        
        if(!is_null($id)) {
            $model = $this->findModel($id);
        }

        if ($this->request->isPost)  {
            $model->load(Yii::$app->request->post());
            if(is_null($model->id)) {
                $model->date_created = $model->date_modified = date('Y-m-d H:i:s');
            }
            $model->request_id = $rid;
            if ($model->save()) {
                if(is_null($id)) {
                    $request = CustomerRequests::findOne($rid);
                    if($request) {
                        $request->payment_id = $model->id;
                        $request->save();
                    }
                }
                Yii::$app->session->setFlash('success','Payment details updated successfully');
                return $this->redirect(['customer-requests/view','id'=> $model->request_id]);
            }
        }
        $paymentMethods = PaymentMethods::findAll([['enabled' => StatusCodes::ACTIVE_STATUS]]);
        return $this->render('update', [
            'model' => $model,
            'paymentMethods' => $paymentMethods
        ]);
    }


    /**
     * Finds the Payments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Payments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payments::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionCreate($rid)
    {
        $model = new Payments();
        $model->request_id = $rid;
        //Check that we dont have another payment for this request
        $existingPayment = Payments::findOne(['request_id' => $rid]);
        if ($existingPayment) {
            Yii::$app->session->setFlash('error', 'Payment already exists for this request');
            return $this->redirect(['customer-requests/view', 'id' => $rid]);
        }
        
        if ($this->request->isPost) {
            $model->load(Yii::$app->request->post());
            // Convert datetime-local format to database format
            if ($model->date_created) {
                $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i', $model->date_created);
                if ($dateTime) {
                    $model->date_created = $dateTime->format('Y-m-d H:i:s');
                } else {
                    // Fallback to current date if parsing fails
                    $model->date_created = date('Y-m-d H:i:s');
                }
            } else {
                $model->date_created = date('Y-m-d H:i:s');
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Payment created successfully'));
                return $this->redirect(['customer-requests/view', 'id' => $rid]);
            } else {
                print_r($model->errors);
                exit;
            }
        } else {
            // Set default date_created for display in datetime-local format
            $model->date_created = date('Y-m-d\TH:i');
        }
        $model->amount = $model->request->total_amount;
        return $this->render('create', ['model' => $model]);
    }
}
