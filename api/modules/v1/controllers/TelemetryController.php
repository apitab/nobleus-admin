<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\models\billing\Meter;
use common\models\billing\MeterAssignment;
use common\models\billing\MeterReadingRaw;

/**
 * Receives real-time telemetry pushed from the ThingsBoard Rule Engine
 * (REST API Call node). Protected by a shared secret token.
 *
 * POST /v1/telemetry/receive
 * Header: X-TB-Token: <TB_WEBHOOK_TOKEN from .env>
 *
 * In this ThingsBoard setup (hwa-smart-reading-system) devices are named by
 * their 8-digit meter serial (e.g. "03284475") and the telemetry key is
 * "absoluteMeterReading_liters". The rule chain node should send:
 *   {"deviceName":"$[metadata.deviceName]",
 *    "reading":"$[absoluteMeterReading_liters]",
 *    "supplyCode":"$[metadata.ss_supplyCode]","ts":"$[metadata.ts]"}
 */
class TelemetryController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionReceive()
    {
        $request = Yii::$app->request;
        if (!$request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['success' => false, 'message' => 'Method not allowed'];
        }

        $expected = $_ENV['TB_WEBHOOK_TOKEN'] ?? null;
        $provided = $request->headers->get('X-TB-Token') ?: $request->get('token');
        if (empty($expected) || !hash_equals($expected, (string)$provided)) {
            Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Invalid token'];
        }

        $body = json_decode($request->rawBody, true);
        if (!is_array($body)) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'Invalid JSON payload'];
        }

        $deviceName = $body['deviceName'] ?? $body['devEui'] ?? $body['dev_eui'] ?? null;
        $reading = $body['reading'] ?? $body['absoluteMeterReading_liters'] ?? $body['meterReading'] ?? $body['value'] ?? null;
        $ts = $body['ts'] ?? null;

        if ($deviceName === null || $reading === null || !is_numeric($reading)) {
            Yii::$app->response->statusCode = 422;
            return ['success' => false, 'message' => 'deviceName/devEui and numeric reading are required'];
        }

        $model = new MeterReadingRaw();
        $model->dev_eui = (string)$deviceName;
        $model->reading_value = (float)$reading;
        $model->reading_time = $ts !== null
            ? date('Y-m-d H:i:s', (int)($ts / ($ts > 9999999999 ? 1000 : 1)))
            : date('Y-m-d H:i:s');
        $model->payload = $request->rawBody;
        $model->source = 'webhook';
        $model->status = MeterReadingRaw::STATUS_PENDING;

        // Resolve meter + supply number locally. TB device names are the
        // 8-digit meter serials, so try serial_number first, then dev_eui.
        $meter = Meter::findOne(['serial_number' => $model->dev_eui])
            ?: Meter::findOne(['dev_eui' => $model->dev_eui]);
        if ($meter) {
            $model->meter_id = $meter->id;
            $assignment = MeterAssignment::findOne(['meter_id' => $meter->id]);
            if ($assignment) {
                $model->supply_no = $assignment->supply_no;
            }
        }
        if (empty($model->supply_no) && !empty($body['supplyCode'])) {
            $model->supply_no = (string)$body['supplyCode'];
        }

        // Idempotency: TB rule engine retries and REST-sync overlap may send
        // the same reading twice. Acknowledge duplicates with 200 so TB does
        // not keep retrying.
        $existing = MeterReadingRaw::find()
            ->where(['dev_eui' => $model->dev_eui, 'reading_time' => $model->reading_time])
            ->one();
        if ($existing) {
            return ['success' => true, 'id' => (int)$existing->id, 'duplicate' => true];
        }

        if (!$model->save()) {
            Yii::error('Telemetry save failed: ' . json_encode($model->errors), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Failed to store reading'];
        }

        return ['success' => true, 'id' => $model->id];
    }
}
