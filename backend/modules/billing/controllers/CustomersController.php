<?php

namespace backend\modules\billing\controllers;

use Yii;
use backend\modules\billing\models\CustomerAnalysisForm;

class CustomersController extends BaseController
{
    /**
     * Customer Analysis panel: search by supply number with date filters.
     */
    public function actionIndex()
    {
        $model = new CustomerAnalysisForm();
        $model->load(Yii::$app->request->get());

        $assignment = null;
        $history = [];
        $latest = null;

        if ($model->supply_no && $model->validate()) {
            $assignment = $model->getAssignment();
            $history = $model->getHistory();
            $latest = $model->getLatestEntry();
            if (!$assignment) {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'No meter assignment found for this supply number.'));
            }
        }

        return $this->render('index', [
            'model' => $model,
            'assignment' => $assignment,
            'history' => $history,
            'latest' => $latest,
        ]);
    }
}
