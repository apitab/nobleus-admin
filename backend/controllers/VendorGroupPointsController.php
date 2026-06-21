<?php

namespace backend\controllers;

use yii;
use backend\models\VendorGroupPoints;
use backend\models\VendorGroups;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * VendorGroupPointsController implements the CRUD actions for VendorGroupPoints model.
 */
class VendorGroupPointsController extends Controller
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
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all VendorGroupPoints models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => VendorGroupPoints::find(),
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
     * Displays a single VendorGroupPoints model.
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
     * Creates a new VendorGroupPoints model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new VendorGroupPoints();
        $vendorGroups = VendorGroups::find()->all();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->date_created = Date('Y-m-d H:i:s');
                if($model->save()) {
                    Yii::$app->session->setFlash('success', 'Vendor reward points created sucessfully');
                } else {
                    Yii::$app->session->setFlash('error', 'Error creating reward points. Contact Admin');
                }
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'groups' => $vendorGroups
        ]);
    }

    /**
     * Updates an existing VendorGroupPoints model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $vendorGroups = VendorGroups::find()->all();
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if($model->save()) {
                Yii::$app->session->setFlash('success', 'Vendor reward points updated sucessfully');
            } else {
                Yii::$app->session->setFlash('error', 'Error updating reward points. Contact Admin');
            }
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'groups' => $vendorGroups
        ]);
    }

    /**
     * Deletes an existing VendorGroupPoints model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->delete()) {
            Yii::$app->session->setFlash('success', 'Vendor reward points deleted sucessfully');
        } else {
            Yii::$app->session->setFlash('error', 'Error deleting reward points. Contact Admin');
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the VendorGroupPoints model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return VendorGroupPoints the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorGroupPoints::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
