<?php

namespace backend\controllers;

use yii;
use backend\models\VendorGroups;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * VendorGroupsController implements the CRUD actions for VendorGroups model.
 */
class VendorGroupsController extends CustomController
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
     * Lists all VendorGroups models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => VendorGroups::find(),
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
        ]);
    }

    

    /**
     * Creates a new VendorGroups model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new VendorGroups();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success','Vendor group created sucessfully');
                return $this->redirect(['vendor-groups/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing VendorGroups model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($this->request->isPost) {
            $model->load($this->request->post());
            if ($model->save()) {
                Yii::$app->session->setFlash('success','Vendor group updated sucessfully');
                return $this->redirect(['vendor-groups/index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VendorGroups model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if($this->request->isPost) {
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('success','Vendor group deleted sucessfully');
        }
        

        return $this->redirect(['index']);
    }

    /**
     * Finds the VendorGroups model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return VendorGroups the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorGroups::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
