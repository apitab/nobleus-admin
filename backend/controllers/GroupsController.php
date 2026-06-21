<?php

namespace backend\controllers;

use backend\helpers\StatusCodes;
use yii;
use backend\models\Groups;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GroupsController implements the CRUD actions for Groups model.
 */
class GroupsController extends CustomController
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
     * Lists all Groups models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Groups::find(),
            'pagination' => [
                'pageSize' => 10
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
     * Displays a single Groups model.
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
     * Creates a new Groups model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Groups();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = $model->date_modified  = Date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success',Yii::t('app','Group has been created successfully'));
                return $this->redirect(['groups/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Groups model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->id == 1) {
            Yii::$app->session->setFlash('error',Yii::t('app','Administrator Group details cannot be updated'));
            return $this->redirect(['index']);
        }
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success',Yii::t('app','Group has been updated successfully'));
            return $this->redirect(['groups/index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Groups model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->id == 1) {
            Yii::$app->session->setFlash('error',Yii::t('app','Administrator Group cannot be disabled'));
            return $this->redirect(['index']);
        }
        $model->status = StatusCodes::DELETE_STATUS;
        $model->save();
        Yii::$app->session->setFlash('success',Yii::t('app','Group has been disabled successfully'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the Groups model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Groups the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Groups::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
