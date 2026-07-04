<?php

namespace backend\modules\billing\models;

use Yii;
use yii\base\Model;
use common\models\billing\BillingLedger;
use common\models\billing\MeterAssignment;

class CustomerAnalysisForm extends Model
{
    public $supply_no;
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['supply_no'], 'required'],
            [['supply_no'], 'string', 'max' => 100],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function getAssignment()
    {
        return MeterAssignment::find()
            ->with(['meter', 'customer'])
            ->where(['supply_no' => $this->supply_no])
            ->one();
    }

    /**
     * Historical ledger entries, cached for fast repeat loads.
     */
    public function getHistory()
    {
        $from = $this->date_from ?: date('Y-m-d', strtotime('-3 months'));
        $to = $this->date_to ?: date('Y-m-d');
        $cacheKey = ['billing-history', $this->supply_no, $from, $to];

        return Yii::$app->cache->getOrSet($cacheKey, function () use ($from, $to) {
            return BillingLedger::find()
                ->where(['supply_no' => $this->supply_no])
                ->andWhere(['between', 'reading_date', $from, $to])
                ->orderBy(['reading_date' => SORT_DESC])
                ->all();
        }, 300);
    }

    public function getLatestEntry()
    {
        return BillingLedger::find()
            ->where(['supply_no' => $this->supply_no])
            ->orderBy(['reading_date' => SORT_DESC, 'id' => SORT_DESC])
            ->one();
    }
}
