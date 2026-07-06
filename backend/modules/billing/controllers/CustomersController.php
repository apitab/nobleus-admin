<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\billing\MeterAssignment;

class CustomersController extends BaseController
{
    /**
     * Customer Analysis panel: list customers with name, phone, meter ID, supply no.
     */
    public function actionIndex()
    {
        $supplyNo = trim((string) Yii::$app->request->get('supply_no', ''));
        $phone    = trim((string) Yii::$app->request->get('phone', ''));

        $query = MeterAssignment::find()->with(['meter', 'customer']);
        if ($supplyNo !== '') {
            $query->andFilterWhere(['like', 'supply_no', $supplyNo]);
        }
        if ($phone !== '') {
            $query->andFilterWhere(['like', 'customer_phone', $phone]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'supplyNo' => $supplyNo,
            'phone' => $phone,
        ]);
    }
}
