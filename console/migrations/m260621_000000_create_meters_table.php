<?php

use yii\db\Migration;

class m260621_000000_create_meters_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%meters}}', [
            'id' => $this->primaryKey(),
            'serial_number' => $this->string(100)->notNull()->unique(),
            'meter_type' => $this->string(50)->notNull(),
            'dev_eui' => $this->string(100)->notNull()->unique(),
            'app_eui' => $this->string(100)->notNull(),
            'app_key' => $this->string(100)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'installed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_meters_serial_number', '{{%meters}}', 'serial_number', true);
        $this->createIndex('idx_meters_dev_eui', '{{%meters}}', 'dev_eui', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%meters}}');
    }
}
