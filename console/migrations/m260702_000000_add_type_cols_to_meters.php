<?php

use yii\db\Migration;

/**
 * Adds frame_type and diameter columns to meters table.
 * These store the meter-specific type details from ThingsBoard telemetry:
 * - frame_type: for ultrasonic meters
 * - diameter: for b-meters (DN15, DN20, etc.)
 */
class m260702_000000_add_type_cols_to_meters extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%meters}}', 'frame_type', $this->string(50)->null()->after('meter_type'));
        $this->addColumn('{{%meters}}', 'diameter', $this->string(50)->null()->after('frame_type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%meters}}', 'diameter');
        $this->dropColumn('{{%meters}}', 'frame_type');
    }
}
