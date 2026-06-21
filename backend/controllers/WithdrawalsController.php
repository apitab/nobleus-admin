<?php

namespace backend\controllers;

use backend\models\Withdrawals;
use backend\models\WithdrawalsSearchForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * WithdrawalsController implements the CRUD actions for Withdrawals model.
 */
class WithdrawalsController extends Controller
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
     * Lists all Withdrawals models.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $search = Yii::$app->request->get('WithdrawalsSearchForm');
        $model = new WithdrawalsSearchForm();
        $searchText = '';
        
        if ($search) {
            $model->load($search, '');
        }

        // Build search text from filters
        $searchFilters = [];
        if ($model->phoneNumber) {
            $searchFilters[] = 'Phone: ' . $model->phoneNumber;
        }
        if ($model->firstName) {
            $searchFilters[] = 'First Name: ' . $model->firstName;
        }
        if ($model->lastName) {
            $searchFilters[] = 'Last Name: ' . $model->lastName;
        }
        if ($model->status) {
            $searchFilters[] = 'Status: ' . $model->status;
        }
        
        if (!empty($searchFilters)) {
            $searchText = 'Filters: ' . implode(', ', $searchFilters);
        }

        $query = Withdrawals::find()
            ->leftJoin('vendors', 'withdrawals.from_id = vendors.id AND withdrawals.from_type = "vendor"')
            ->leftJoin('customers', 'withdrawals.from_id = customers.id AND withdrawals.from_type = "customer"');

        // Apply search filters
        if ($model->phoneNumber) {
            $query->andWhere(['or',
                ['and', ['=', 'withdrawals.from_type', 'vendor'], ['like', 'vendors.mobile_number', $model->phoneNumber]],
                ['and', ['=', 'withdrawals.from_type', 'customer'], ['like', 'customers.phone_number', $model->phoneNumber]]
            ]);
        }
        if ($model->firstName) {
            $query->andWhere(['or',
                ['and', ['=', 'withdrawals.from_type', 'vendor'], ['like', 'vendors.first_name', $model->firstName]],
                ['and', ['=', 'withdrawals.from_type', 'customer'], ['like', 'customers.alias', $model->firstName]]
            ]);
        }
        if ($model->lastName) {
            $query->andWhere(['or',
                ['and', ['=', 'withdrawals.from_type', 'vendor'], ['like', 'vendors.other_names', $model->lastName]],
                ['and', ['=', 'withdrawals.from_type', 'customer'], ['like', 'customers.alias', $model->lastName]]
            ]);
        }
        if ($model->status) {
            $query->andWhere(['withdrawals.status' => $model->status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'amount',
                    'status',
                    'created_at',
                    'from_type',
                    'vendor.first_name',
                    'vendor.other_names',
                    'vendor.mobile_number',
                    'customer.alias',
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search,
            'searchText' => $searchText
        ]);
    }

    /**
     * Displays a single Withdrawals model.
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
     * Updates an existing Withdrawals model.
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
     * Finds the Withdrawals model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Withdrawals the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Withdrawals::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
