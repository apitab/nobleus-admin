<?php

namespace backend\controllers;

use Yii;
use backend\models\VideoTutorials;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * VideoTutorialsController implements the CRUD actions for VideoTutorials model.
 */
class VideoTutorialsController extends CustomController
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
     * Lists all VideoTutorials models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => VideoTutorials::find(),

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
     * Creates a new VideoTutorials model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new VideoTutorials();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->videoFile = UploadedFile::getInstance($model, 'videoFile');
            $model->date_created = Date('Y-m-d H:i:s');

            if ($model->videoFile) {
                $uploadResult = $model->upload();
                if ($uploadResult['status']) {
                    // upload() already validated and set video_name, so skip re-validation to avoid temp file errors
                    $model->videoFile = null;
                    if ($model->save(false)) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Video tutorial created successfully'));
                        return $this->redirect(['video-tutorials/index']);
                    }
                } else {
                    $model->addError('videoFile', Yii::t('app', 'Failed to upload video file'));
                }
            } else {
                $model->addError('videoFile', Yii::t('app', 'Please upload a video file'));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing VideoTutorials model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->videoFile = UploadedFile::getInstance($model, 'videoFile');

            if ($model->videoFile) {
                // Delete old video file if exists
                if ($model->video_name) {
                    $oldFilePath = Yii::getAlias('@webroot') . '/' . $model->video_name;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $model->upload();
            }
            // upload() already validated and moved the file; avoid re-validating the temp file
            $model->videoFile = null;
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Video tutorial updated successfully'));
                return $this->redirect(['video-tutorials/index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VideoTutorials model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Delete video file if exists
        if ($model->video_name) {
            $filePath = Yii::getAlias('@webroot') . '/' . $model->video_name;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Video tutorial deleted successfully'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the VideoTutorials model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return VideoTutorials the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VideoTutorials::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
