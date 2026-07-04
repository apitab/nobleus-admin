<?php

namespace backend\modules\billing\models;

use yii\data\ActiveDataProvider;
use common\models\billing\Meter;

class FlowmeterSearch extends Meter
{
    public $supply_no;

    public function rules()
    {
        return [
            [['serial_number', 'dev_eui', 'meter_type', 'supply_no'], 'safe'],
            [['status'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = Meter::find()->joinWith('assignment');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['meters.status' => $this->status])
            ->andFilterWhere(['like', 'serial_number', $this->serial_number])
            ->andFilterWhere(['like', 'dev_eui', $this->dev_eui])
            ->andFilterWhere(['like', 'meter_type', $this->meter_type])
            ->andFilterWhere(['like', 'meter_assignments.supply_no', $this->supply_no]);

        return $dataProvider;
    }
}
