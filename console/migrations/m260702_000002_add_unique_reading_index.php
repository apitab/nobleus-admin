<?php

use yii\db\Migration;

/**
 * Makes telemetry ingestion idempotent: the same device cannot store two
 * readings for the exact same timestamp. Protects against ThingsBoard rule
 * engine retries and webhook + REST-sync overlap creating duplicates.
 */
class m260702_000002_add_unique_reading_index extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('idx_mrr_dev_eui', '{{%meter_readings_raw}}');
        $this->createIndex('idx_mrr_dev_eui_time', '{{%meter_readings_raw}}', ['dev_eui', 'reading_time'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_mrr_dev_eui_time', '{{%meter_readings_raw}}');
        $this->createIndex('idx_mrr_dev_eui', '{{%meter_readings_raw}}', 'dev_eui');
    }
}
