<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use common\models\billing\MeterAlarm;
use common\models\billing\Meter;
use backend\modules\billing\models\MeterAlarmSearch;

/**
 * AlarmsController - Manages meter alarms panel
 * Lists all meters with their alarm status and provides alarm history
 */
class AlarmsController extends BaseController
{
    /**
     * Lists all meters with alarm summary
     */
    public function actionIndex()
    {
        $searchModel = new MeterAlarmSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Get alarm statistics
        $stats = [
            'total_active' => MeterAlarm::find()->where(['status' => MeterAlarm::STATUS_ACTIVE])->count(),
            'critical' => MeterAlarm::find()->where(['status' => MeterAlarm::STATUS_ACTIVE, 'severity' => MeterAlarm::SEVERITY_CRITICAL])->count(),
            'warning' => MeterAlarm::find()->where(['status' => MeterAlarm::STATUS_ACTIVE, 'severity' => MeterAlarm::SEVERITY_WARNING])->count(),
            'cleared_today' => MeterAlarm::find()
                ->where(['status' => MeterAlarm::STATUS_CLEARED])
                ->andWhere(['>=', 'cleared_at', date('Y-m-d 00:00:00')])
                ->count(),
        ];

        // Get meter types for filter
        $meterTypes = MeterAlarmSearch::getMeterTypes();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'meterTypes' => $meterTypes,
        ]);
    }

    /**
     * Lists all meters with their current alarm status
     * Shows LAST alarm status only (not duplicates)
     */
    public function actionMeters()
    {
        $query = Meter::find()
            ->with(['assignment', 'readings' => function($q) {
                $q->orderBy(['reading_time' => SORT_DESC])->limit(1);
            }]);

        // Apply filters
        $request = Yii::$app->request;
        $meterType = $request->get('meter_type');
        $serialNumber = $request->get('serial_number');
        $supplyNo = $request->get('supply_no');
        $hasAlarms = $request->get('has_alarms');
        $assignmentStatus = $request->get('assignment_status');

        if ($meterType) {
            $query->andWhere(['meter_type' => $meterType]);
        }
        if ($serialNumber) {
            $query->andWhere(['like', 'serial_number', $serialNumber]);
        }
        
        // Filter by supply_no (requires join with meter_assignments)
        if ($supplyNo) {
            $query->joinWith('assignment')
                  ->andWhere(['like', 'meter_assignments.supply_no', $supplyNo]);
        }
        
        // Filter by assignment status
        if ($assignmentStatus === 'assigned') {
            $query->joinWith('assignment')
                  ->andWhere(['IS NOT', 'meter_assignments.supply_no', null]);
        } elseif ($assignmentStatus === 'unassigned') {
            $query->leftJoin('meter_assignments ma2', 'meters.id = ma2.meter_id')
                  ->andWhere(['OR', ['ma2.id' => null], ['ma2.supply_no' => null]]);
        }

        // Filter to show only meters with active alarms
        if ($hasAlarms === '1') {
            $metersWithAlarms = MeterAlarm::find()
                ->select('meter_id')
                ->where(['status' => MeterAlarm::STATUS_ACTIVE])
                ->distinct()
                ->column();
            $query->andWhere(['meters.id' => $metersWithAlarms]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['serial_number' => SORT_ASC],
            ],
        ]);

        // Get meter types for filter
        $meterTypes = MeterAlarmSearch::getMeterTypes();

        return $this->render('meters', [
            'dataProvider' => $dataProvider,
            'meterTypes' => $meterTypes,
            'filters' => [
                'meter_type' => $meterType,
                'serial_number' => $serialNumber,
                'supply_no' => $supplyNo,
                'has_alarms' => $hasAlarms,
                'assignment_status' => $assignmentStatus,
            ],
        ]);
    }

    /**
     * View alarm history for a specific meter
     */
    public function actionHistory($id)
    {
        $meter = Meter::find()
            ->with(['assignment'])
            ->where(['id' => $id])
            ->one();

        if (!$meter) {
            throw new NotFoundHttpException(Yii::t('app', 'Meter not found.'));
        }

        $searchModel = new MeterAlarmSearch();
        $dataProvider = $searchModel->searchByMeter($id, Yii::$app->request->queryParams);

        // Get alarm statistics for this meter
        $stats = [
            'total' => MeterAlarm::find()->where(['meter_id' => $id])->count(),
            'active' => MeterAlarm::find()->where(['meter_id' => $id, 'status' => MeterAlarm::STATUS_ACTIVE])->count(),
            'cleared' => MeterAlarm::find()->where(['meter_id' => $id, 'status' => MeterAlarm::STATUS_CLEARED])->count(),
        ];

        return $this->render('history', [
            'meter' => $meter,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * View single alarm details
     */
    public function actionView($id)
    {
        $alarm = MeterAlarm::find()
            ->with(['meter', 'meter.assignment', 'acknowledgedByUser'])
            ->where(['id' => $id])
            ->one();

        if (!$alarm) {
            throw new NotFoundHttpException(Yii::t('app', 'Alarm not found.'));
        }

        return $this->render('view', [
            'alarm' => $alarm,
        ]);
    }

    /**
     * Acknowledge an alarm
     */
    public function actionAcknowledge($id)
    {
        $alarm = MeterAlarm::findOne($id);
        if (!$alarm) {
            throw new NotFoundHttpException(Yii::t('app', 'Alarm not found.'));
        }

        if ($alarm->status === MeterAlarm::STATUS_ACTIVE) {
            $alarm->status = MeterAlarm::STATUS_ACKNOWLEDGED;
            $alarm->acknowledged_at = date('Y-m-d H:i:s');
            $alarm->acknowledged_by = Yii::$app->user->id;
            
            if ($alarm->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Alarm acknowledged successfully.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to acknowledge alarm.'));
            }
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Get alarm details via AJAX (for modal)
     */
    public function actionAjaxDetails($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $alarm = MeterAlarm::find()
            ->with(['meter', 'meter.assignment'])
            ->where(['id' => $id])
            ->one();

        if (!$alarm) {
            return ['success' => false, 'message' => 'Alarm not found'];
        }

        return [
            'success' => true,
            'alarm' => [
                'id' => $alarm->id,
                'serial_number' => $alarm->serial_number ?? $alarm->meter->serial_number ?? $alarm->dev_eui,
                'alarm_type' => $alarm->getAlarmTypeLabel(),
                'severity' => $alarm->severity,
                'status' => $alarm->status,
                'message' => $alarm->message,
                'details' => $alarm->details,
                'originated_at' => $alarm->originated_at,
                'cleared_at' => $alarm->cleared_at,
                'supply_no' => $alarm->meter->assignment->supply_no ?? '-',
            ],
        ];
    }
}
