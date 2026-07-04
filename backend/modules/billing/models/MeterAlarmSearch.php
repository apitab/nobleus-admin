<?php

namespace backend\modules\billing\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\billing\MeterAlarm;
use common\models\billing\Meter;

class MeterAlarmSearch extends MeterAlarm
{
    public $meter_type;
    public $supply_no;
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['id', 'meter_id', 'acknowledged_by'], 'integer'],
            [['dev_eui', 'serial_number', 'alarm_type', 'severity', 'status', 'message', 'tb_alarm_id'], 'safe'],
            [['meter_type', 'supply_no', 'date_from', 'date_to'], 'safe'],
            [['originated_at', 'cleared_at', 'acknowledged_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = MeterAlarm::find()
            ->joinWith(['meter', 'meter.assignment'])
            ->orderBy(['originated_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'meter_alarms.id' => $this->id,
            'meter_alarms.meter_id' => $this->meter_id,
            'meter_alarms.alarm_type' => $this->alarm_type,
            'meter_alarms.severity' => $this->severity,
            'meter_alarms.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'meter_alarms.dev_eui', $this->dev_eui])
            ->andFilterWhere(['like', 'meter_alarms.serial_number', $this->serial_number])
            ->andFilterWhere(['like', 'meter_alarms.message', $this->message]);

        // Filter by meter type
        if (!empty($this->meter_type)) {
            $query->andFilterWhere(['meters.meter_type' => $this->meter_type]);
        }

        // Filter by supply number
        if (!empty($this->supply_no)) {
            $query->andFilterWhere(['like', 'meter_assignments.supply_no', $this->supply_no]);
        }

        // Date range filter
        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'meter_alarms.originated_at', $this->date_from . ' 00:00:00']);
        }
        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'meter_alarms.originated_at', $this->date_to . ' 23:59:59']);
        }

        return $dataProvider;
    }

    /**
     * Search alarms for a specific meter
     */
    public function searchByMeter($meterId, $params)
    {
        $query = MeterAlarm::find()
            ->where(['meter_id' => $meterId])
            ->orderBy(['originated_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'alarm_type' => $this->alarm_type,
            'severity' => $this->severity,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }

    /**
     * Get meter types for filter dropdown
     */
    public static function getMeterTypes()
    {
        return Meter::find()
            ->select('meter_type')
            ->distinct()
            ->orderBy('meter_type')
            ->column();
    }
}
