<?php

namespace backend\controllers;

use yii;
use backend\models\Kiosks;
use backend\models\KioskSearchForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * KiosksController implements the CRUD actions for Kiosks model.
 */
class KiosksController extends Controller
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
     * Lists all Kiosks models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new KioskSearchForm();
        $query = Kiosks::find();
        $search = false;
        if ($this->request->get('KioskSearchForm')) {
            $search = true;
            $searchForm = $this->request->get('KioskSearchForm');
            $model->name = $searchForm['name'];
            $model->status = $searchForm['status'];
            $model->location = $searchForm['location'];

            if ($model->name != "") {
                $query->andWhere(['like', 'name', "%" . $model->name . "%", false]);
            }

            if ($model->status != "") {
                $query->andWhere("status = :status", [":status" => $model->status]);
            }

            if ($model->location != "") {
                $query->andWhere("location_id = :id", [":id" => $model->location]);
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

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search
        ]);
    }


    /**
     * Creates a new Kiosks model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Kiosks();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Kiosk created sucessfully');
                return $this->redirect(['kiosks/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Kiosks model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
        //     print_r($this->request->post());
        // die;
            Yii::$app->session->setFlash('success', 'Kiosk updated sucessfully');
                return $this->redirect(['kiosks/index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Kiosks model.
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
     * Finds the Kiosks model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Kiosks the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Kiosks::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
