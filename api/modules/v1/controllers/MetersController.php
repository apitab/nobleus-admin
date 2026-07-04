<?php

namespace api\modules\v1\controllers;

use Yii;
use Exception;
use backend\models\Customers;
use common\models\billing\MeterAssignment;
use common\models\billing\MeterAlarm;
use common\models\billing\MeterReadingRaw;

class MetersController extends ApiController
{
    /**
     * Get the customer's meter details.
     * Exposes only customer-safe fields: supply no, meter id, meter type,
     * status, latest alarm and latest reading.
     * Sensitive LoRaWAN fields (dev_eui, app_eui, app_key) are never returned.
     */
    public function actionMyMeter($api_token)
    {
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $assignment = MeterAssignment::find()
                ->with('meter')
                ->where(['customer_phone' => $customer->phone_number])
                ->one();

            if (!$assignment || !$assignment->meter) {
                $this->setHeader(404);
                return $this->asJson([
                    'status' => false,
                    'message' => 'No meter assigned to this customer',
                    'data' => ''
                ]);
            }

            $meter = $assignment->meter;

            $lastAlarm = MeterAlarm::find()
                ->where(['meter_id' => $meter->id])
                ->orderBy(['originated_at' => SORT_DESC])
                ->one();

            $lastReading = MeterReadingRaw::find()
                ->where(['supply_no' => $assignment->supply_no])
                ->orderBy(['reading_time' => SORT_DESC])
                ->one();

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Meter details retrieved successfully',
                'data' => [
                    'meter_id' => $meter->id,
                    'supply_no' => $assignment->supply_no,
                    'meter_type' => $meter->meter_type,
                    'status' => $meter->status,
                    'installed_at' => $meter->installed_at,
                    'assigned_at' => $assignment->assigned_at,
                    'last_alarm' => $lastAlarm ? [
                        'alarm_type' => $lastAlarm->alarm_type,
                        'severity' => $lastAlarm->severity,
                        'status' => $lastAlarm->status,
                        'originated_at' => $lastAlarm->originated_at,
                    ] : null,
                    'last_reading' => $lastReading ? [
                        'reading_time' => $lastReading->reading_time,
                    ] : null,
                ]
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error fetching meter details',
                'data' => ''
            ]);
        }
    }

    /**
     * Get the customer's meter alarm history.
     * Only alarm information relevant to the customer is returned.
     */
    public function actionMyAlarms($api_token)
    {
        try {
            $customer = Customers::find()->where(['api_token' => $api_token])->one();
            if (!$customer) {
                throw new Exception(Yii::t('app', 'Customer details not found'));
            }

            $assignment = MeterAssignment::find()
                ->where(['customer_phone' => $customer->phone_number])
                ->one();

            if (!$assignment) {
                $this->setHeader(404);
                return $this->asJson([
                    'status' => false,
                    'message' => 'No meter assigned to this customer',
                    'data' => ''
                ]);
            }

            $alarms = MeterAlarm::find()
                ->where(['meter_id' => $assignment->meter_id])
                ->orderBy(['originated_at' => SORT_DESC])
                ->limit(20)
                ->all();

            $data = [];
            foreach ($alarms as $alarm) {
                $data[] = [
                    'alarm_type' => $alarm->alarm_type,
                    'severity' => $alarm->severity,
                    'status' => $alarm->status,
                    'originated_at' => $alarm->originated_at,
                    'cleared_at' => $alarm->cleared_at,
                ];
            }

            $this->setHeader(200);
            return $this->asJson([
                'status' => true,
                'message' => 'Meter alarms retrieved successfully',
                'data' => array_values($data)
            ]);
        } catch (Exception $e) {
            $this->setHeader(500);
            return $this->asJson([
                'status' => false,
                'message' => 'Error fetching meter alarms',
                'data' => ''
            ]);
        }
    }
}
