<?php

namespace backend\controllers;

use yii;
use backend\models\AppSettings;

/**
 * Manage App Settings
 */
class AppSettingsController extends CustomController
{
    public $layout = 'dashboard/main';

    /**
     * Manage application settings
     */
    public function actionIndex()
    {
        $model = AppSettings::findOne(['id' => 1]);

        if(Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success',Yii::t('app','Mobile app settings updated successfully'));
            }
        }
        return $this->render('app_settings', ['model' => $model]);
    }
}
