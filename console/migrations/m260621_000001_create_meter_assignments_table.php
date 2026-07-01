<?php

use yii\db\Migration;

class m260621_000001_create_meter_assignments_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%meter_assignments}}', [
            'id' => $this->primaryKey(),
            'meter_id' => $this->integer()->notNull(),
            'supply_no' => $this->string(100)->notNull(),
            'customer_phone' => $this->string(50)->null(),
            'receipt_no' => $this->string(100)->null(),
            'assigned_by' => $this->integer()->notNull(),
            'assigned_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_meter_assignments_meter_id', '{{%meter_assignments}}', 'meter_id', true);
        $this->createIndex('idx_meter_assignments_supply_no', '{{%meter_assignments}}', 'supply_no');

        $this->addForeignKey(
            'fk_meter_assignments_meter_id',
            '{{%meter_assignments}}',
            'meter_id',
            '{{%meters}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%meter_assignments}}');
    }
}
