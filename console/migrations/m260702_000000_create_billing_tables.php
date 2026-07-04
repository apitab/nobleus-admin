<?php

use yii\db\Migration;

class m260702_000000_create_billing_tables extends Migration
{
    public function safeUp()
    {
        // Raw telemetry pushed from ThingsBoard webhook
        $this->createTable('{{%meter_readings_raw}}', [
            'id' => $this->primaryKey(),
            'dev_eui' => $this->string(100)->notNull(),
            'meter_id' => $this->integer()->null(),
            'supply_no' => $this->string(100)->null(),
            'reading_value' => $this->decimal(14, 3)->notNull(),
            'reading_time' => $this->dateTime()->notNull(),
            'payload' => $this->text()->null(),
            'source' => $this->string(30)->notNull()->defaultValue('webhook'),
            'status' => $this->smallInteger()->notNull()->defaultValue(0), // 0=pending,1=imported,2=rejected
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('idx_mrr_dev_eui', '{{%meter_readings_raw}}', 'dev_eui');
        $this->createIndex('idx_mrr_supply_no', '{{%meter_readings_raw}}', 'supply_no');
        $this->createIndex('idx_mrr_status', '{{%meter_readings_raw}}', 'status');
        $this->createIndex('idx_mrr_reading_time', '{{%meter_readings_raw}}', 'reading_time');
        $this->addForeignKey('fk_mrr_meter_id', '{{%meter_readings_raw}}', 'meter_id', '{{%meters}}', 'id', 'SET NULL', 'CASCADE');

        // Finalized billing ledger
        $this->createTable('{{%billing_ledger}}', [
            'id' => $this->primaryKey(),
            'raw_reading_id' => $this->integer()->notNull()->unique(),
            'meter_id' => $this->integer()->null(),
            'supply_no' => $this->string(100)->notNull(),
            'previous_reading' => $this->decimal(14, 3)->null(),
            'current_reading' => $this->decimal(14, 3)->notNull(),
            'consumption' => $this->decimal(14, 3)->null(),
            'reading_date' => $this->date()->notNull(),
            'imported_by' => $this->integer()->notNull(),
            'imported_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('idx_bl_supply_no', '{{%billing_ledger}}', 'supply_no');
        $this->createIndex('idx_bl_reading_date', '{{%billing_ledger}}', 'reading_date');
        $this->addForeignKey('fk_bl_raw_reading_id', '{{%billing_ledger}}', 'raw_reading_id', '{{%meter_readings_raw}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_bl_meter_id', '{{%billing_ledger}}', 'meter_id', '{{%meters}}', 'id', 'SET NULL', 'CASCADE');

        // Audit trail for clerk import actions
        $this->createTable('{{%billing_audit_logs}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'action' => $this->string(50)->notNull(),
            'supply_no' => $this->string(100)->null(),
            'raw_reading_id' => $this->integer()->null(),
            'ledger_id' => $this->integer()->null(),
            'details' => $this->text()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('idx_bal_user_id', '{{%billing_audit_logs}}', 'user_id');
        $this->createIndex('idx_bal_supply_no', '{{%billing_audit_logs}}', 'supply_no');
        $this->createIndex('idx_bal_created_at', '{{%billing_audit_logs}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%billing_audit_logs}}');
        $this->dropTable('{{%billing_ledger}}');
        $this->dropTable('{{%meter_readings_raw}}');
    }
}
