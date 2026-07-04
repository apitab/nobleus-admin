<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use common\models\billing\Meter;
use backend\modules\billing\models\FlowmeterSearch;

class FlowmetersController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new FlowmeterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = Meter::find()->with(['assignment.customer'])->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested flowmeter does not exist.'));
        }
        return $this->render('view', ['model' => $model]);
    }
}
