<?php

namespace backend\modules\billing\models;

use yii\data\ActiveDataProvider;
use common\models\billing\MeterReadingRaw;

class ReadingSearch extends MeterReadingRaw
{
    public $date_from;
    public $date_to;
    public $supply_part1;
    public $supply_part2;
    public $supply_part3;

    public function rules()
    {
        return [
            [['dev_eui', 'supply_no', 'date_from', 'date_to'], 'safe'],
            [['supply_part1', 'supply_part2', 'supply_part3'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'supply_no' => 'Meter No',
        ]);
    }

    public function search($params)
    {
        $query = MeterReadingRaw::find()->with(['meter.assignment']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['reading_time' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Reconstruct supply_no from its three parts (e.g. "J-1-23") when provided
        if ($this->supply_part1 !== null || $this->supply_part2 !== null || $this->supply_part3 !== null) {
            $parts = [
                trim((string) $this->supply_part1),
                trim((string) $this->supply_part2),
                trim((string) $this->supply_part3),
            ];
            if (implode('', $parts) !== '') {
                $this->supply_no = trim(implode(' - ', $parts), ' -');
            }
        } elseif ($this->supply_no !== null && $this->supply_no !== '') {
            // Populate the individual parts for redisplay in the filter form
            $parts = explode(' - ', $this->supply_no);
            $this->supply_part1 = $parts[0] ?? '';
            $this->supply_part2 = $parts[1] ?? '';
            $this->supply_part3 = $parts[2] ?? '';
        }

        $query->andFilterWhere(['like', 'dev_eui', $this->dev_eui])
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
