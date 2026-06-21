<?php

namespace backend\controllers;

use Yii;
use backend\models\InformationGuides;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InformationGuidesController implements the CRUD actions for InformationGuides model.
 */
class InformationGuidesController extends Controller
{
    public $layout = '/dashboard/main';
    
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
     * Lists all InformationGuides models.
     *
     * @return string
     */
    public function actionIndex($type = null)
    {
        $query = InformationGuides::find();
        
        if ($type) {
            $query->andWhere(['type' => $type]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        // Get all guide types
        $guideTypes = InformationGuides::find()
            ->select('type')
            ->distinct()
            ->column();

        // Get count by type
        $typeCount = InformationGuides::find()
            ->select(['type', 'count(*) as count'])
            ->groupBy('type')
            ->indexBy('type')
            ->column();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'guideTypes' => $guideTypes,
            'typeCount' => $typeCount,
            'type' => $type,
        ]);
    }

    /**
     * Displays a single InformationGuides model.
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
     * Creates a new InformationGuides model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new InformationGuides();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = date('Y-m-d H:i:s');
            $model->date_modified = date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Information guide created successfully'));
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing InformationGuides model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Information guide updated successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing InformationGuides model.
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
     * Finds the InformationGuides model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return InformationGuides the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = InformationGuides::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
