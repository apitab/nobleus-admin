<?php

use yii\db\Migration;

/**
 * Creates the meter_alarms table to store alarm history from ThingsBoard.
 * Each alarm has a type, severity, and timestamps for when it was raised/cleared.
 */
class m260702_100000_create_meter_alarms_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%meter_alarms}}', [
            'id' => $this->primaryKey(),
            'meter_id' => $this->integer()->null(),
            'dev_eui' => $this->string(100)->notNull(),
            'serial_number' => $this->string(100)->null(),
            'alarm_type' => $this->string(100)->notNull(), // e.g., 'inactivity', 'low_battery', 'tamper', 'leak'
            'severity' => $this->string(20)->notNull()->defaultValue('WARNING'), // CRITICAL, WARNING, INFO
            'status' => $this->string(20)->notNull()->defaultValue('ACTIVE'), // ACTIVE, CLEARED, ACKNOWLEDGED
            'message' => $this->text()->null(),
            'details' => $this->text()->null(), // JSON details from ThingsBoard
            'tb_alarm_id' => $this->string(100)->null(), // ThingsBoard alarm UUID
            'originated_at' => $this->dateTime()->notNull(), // When alarm was raised
            'cleared_at' => $this->dateTime()->null(), // When alarm was cleared
            'acknowledged_at' => $this->dateTime()->null(),
            'acknowledged_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_ma_meter_id', '{{%meter_alarms}}', 'meter_id');
        $this->createIndex('idx_ma_dev_eui', '{{%meter_alarms}}', 'dev_eui');
        $this->createIndex('idx_ma_serial_number', '{{%meter_alarms}}', 'serial_number');
        $this->createIndex('idx_ma_alarm_type', '{{%meter_alarms}}', 'alarm_type');
        $this->createIndex('idx_ma_severity', '{{%meter_alarms}}', 'severity');
        $this->createIndex('idx_ma_status', '{{%meter_alarms}}', 'status');
        $this->createIndex('idx_ma_originated_at', '{{%meter_alarms}}', 'originated_at');
        $this->createIndex('idx_ma_tb_alarm_id', '{{%meter_alarms}}', 'tb_alarm_id');
        
        $this->addForeignKey(
            'fk_ma_meter_id',
            '{{%meter_alarms}}',
            'meter_id',
            '{{%meters}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%meter_alarms}}');
    }
}
