<?php

namespace backend\controllers;

use yii;
use backend\helpers\StatusCodes;
use backend\models\Groups;
use backend\models\ModuleActions;
use backend\models\Modules;
use backend\models\Permissions;
use backend\models\PermissionSearchForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PermissionsController implements the CRUD actions for Permissions model.
 */
class PermissionsController extends CustomController
{
    public $layout = "dashboard/main";
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
     * Lists all Permissions models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new PermissionSearchForm();
        $query = Permissions::find();
        $search = false;
        if($this->request->get('PermissionSearchForm')) {
            $search = true;
            //@todo validate this params
            $searchForm = $this->request->get('PermissionSearchForm');
            $model->module = $searchForm['module'];
            $model->group = $searchForm['group'];

            //Define the query parameters
            if($model->module != "") {
                $query->andWhere("module_id = :module_id",[":module_id" => $model->module]);
            }

            if($model->group != "") {
                $query->andWhere("group_id = :group_id",[":group_id" => $model->group]);
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
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search
        ]);
    }

    /**
     * Creates a new Permissions model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Permissions();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->status = StatusCodes::ACTIVE_STATUS;
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success','Permission has been added successfully');
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        $groups = Groups::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $modules = Modules::findAll(['status' => StatusCodes::ACTIVE_STATUS]);
        $moduleActions = ModuleActions::findAll(['status' => StatusCodes::ACTIVE_STATUS]);

        return $this->render('create', [
            'model' => $model,
            'groups' => $groups,
            'modules' => $modules,
            'moduleActions' => $moduleActions
        ]);
    }

    
    /**
     * Deletes an existing Permissions model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success','Permission has been deleted successfully');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Permissions model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Permissions the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Permissions::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
