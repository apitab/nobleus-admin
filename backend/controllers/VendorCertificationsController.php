<?php

namespace backend\controllers;

use yii;
use backend\models\VendorCertifications;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * VendorCertificationsController implements the CRUD actions for VendorCertifications model.
 */
class VendorCertificationsController extends Controller
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
     * Creates a new VendorCertifications model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($cid)
    {
        $model = new VendorCertifications();
        $model->vendor_id = $cid;

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->expiry_date = date('Y-m-d', strtotime($model->expiry_date));
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            $model->vendor_id = $cid;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Vendor Certification had been added successfully'));
                return $this->redirect(['vendors/view-certifications', 'id' => $model->vendor_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing VendorCertifications model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $cid)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->expiry_date = date('Y-m-d', strtotime($model->expiry_date));
            if($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Vendor Certification had been updated successfully'));
                return $this->redirect(['vendors/view-certifications', 'id' => $model->vendor_id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VendorCertifications model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $cid)
    {
        $model = $this->findModel($id);
        if($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Vendor Certification had been deleted successfully'));
        }
        return $this->redirect(['vendors/view-certifications', 'id' => $model->vendor_id]);
    }

    /**
     * Finds the VendorCertifications model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return VendorCertifications the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorCertifications::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
