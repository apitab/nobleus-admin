<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\billing\BillingAuditLog;
use common\models\billing\BillingLedger;

/**
 * Retries pushing clerk-imported ledger entries to the core billing API.
 * Run from cron to catch entries whose inline push failed:
 *   php yii billing-push
 */
class BillingPushController extends Controller
{
    public function actionIndex()
    {
        /** @var \common\components\CoreBillingClient $client */
        $client = Yii::$app->coreBilling;
        if (!$client->isConfigured()) {
            $this->stderr("CORE_READING_API_URL and CORE_API_TOKEN must be set in .env\n");
            return ExitCode::CONFIG;
        }

        $pending = BillingLedger::find()
            ->where(['push_status' => ['pending', 'failed']])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $ok = 0;
        foreach ($pending as $ledger) {
            $result = $client->postMeterReading($ledger->supply_no, (float)$ledger->current_reading, $ledger->reading_date);

            $ledger->push_status = $result['reason'] ?: ($result['success'] ? 'pushed' : 'failed');
            $ledger->pushed_at = $result['success'] ? date('Y-m-d H:i:s') : null;
            $ledger->push_response = is_string($result['body']) ? $result['body'] : json_encode($result['body']);
            $ledger->save(false);

            BillingAuditLog::record($result['success'] ? 'push' : 'push_failed', [
                'supply_no' => $ledger->supply_no,
                'ledger_id' => $ledger->id,
                'details' => '[retry] ' . $result['message'],
            ]);

            $this->stdout("#{$ledger->id} {$ledger->supply_no}: {$ledger->push_status}\n");
            if ($result['success'] || $result['reason']) {
                $ok++;
            }
        }

        $this->stdout("Done. " . count($pending) . " processed, {$ok} resolved.\n");
        return ExitCode::OK;
    }
}
