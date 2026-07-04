<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\billing\BillingLedger;
use common\models\billing\MeterReadingRaw;

class ReportsController extends BaseController
{
    /**
     * General Reading Report: all meters and their readings, default today.
     */
    public function actionGeneral()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));

        $dataProvider = new ActiveDataProvider([
            'query' => MeterReadingRaw::find()
                ->with('meter')
                ->where(['between', 'reading_time', $date . ' 00:00:00', $date . ' 23:59:59'])
                ->orderBy(['reading_time' => SORT_DESC]),
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('general', [
            'dataProvider' => $dataProvider,
            'date' => $date,
        ]);
    }

    /**
     * Import Status Report: reconciliation of synced TB data vs ledger.
     */
    public function actionImportStatus()
    {
        $from = Yii::$app->request->get('date_from', date('Y-m-d'));
        $to = Yii::$app->request->get('date_to', date('Y-m-d'));

        $base = MeterReadingRaw::find()
            ->with(['meter', 'ledgerEntry.importedByUser'])
            ->where(['between', 'reading_time', $from . ' 00:00:00', $to . ' 23:59:59']);

        $imported = new ActiveDataProvider([
            'query' => (clone $base)->andWhere(['status' => MeterReadingRaw::STATUS_IMPORTED]),
            'pagination' => ['pageSize' => 25],
        ]);
        $pending = new ActiveDataProvider([
            'query' => (clone $base)->andWhere(['status' => MeterReadingRaw::STATUS_PENDING]),
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('import-status', [
            'imported' => $imported,
            'pending' => $pending,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Comparative Analysis: month-over-month consumption comparison.
     */
    public function actionComparative()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));
        $prevDate = date('Y-m-d', strtotime($date . ' -1 month'));

        $rows = Yii::$app->cache->getOrSet(['billing-comparative', $date], function () use ($date, $prevDate) {
            $current = BillingLedger::find()
                ->where(['reading_date' => $date])
                ->indexBy('supply_no')
                ->all();
            $previous = BillingLedger::find()
                ->where(['reading_date' => $prevDate])
                ->indexBy('supply_no')
                ->all();

            $rows = [];
            foreach ($current as $supplyNo => $entry) {
                $prev = $previous[$supplyNo] ?? null;
                $rows[] = [
                    'supply_no' => $supplyNo,
                    'current_reading' => $entry->current_reading,
                    'previous_reading' => $prev ? $prev->current_reading : null,
                    'difference' => $prev !== null
                        ? (float)$entry->current_reading - (float)$prev->current_reading
                        : null,
                ];
            }
            return $rows;
        }, 300);

        return $this->render('comparative', [
            'rows' => $rows,
            'date' => $date,
            'prevDate' => $prevDate,
        ]);
    }
}
