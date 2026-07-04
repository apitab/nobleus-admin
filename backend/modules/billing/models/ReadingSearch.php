<?php

namespace backend\modules\billing\models;

use yii\data\ActiveDataProvider;
use common\models\billing\MeterReadingRaw;

class ReadingSearch extends MeterReadingRaw
{
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['dev_eui', 'supply_no', 'date_from', 'date_to'], 'safe'],
            [['status'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = MeterReadingRaw::find()->with('meter');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['reading_time' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'dev_eui', $this->dev_eui])
            ->andFilterWhere(['like', 'supply_no', $this->supply_no]);

        if ($this->date_from) {
            $query->andWhere(['>=', 'reading_time', $this->date_from . ' 00:00:00']);
        }
        if ($this->date_to) {
            $query->andWhere(['<=', 'reading_time', $this->date_to . ' 23:59:59']);
        }

        return $dataProvider;
    }
}
