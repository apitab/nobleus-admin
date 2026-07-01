<?php

use yii\db\Migration;

class m260621_000002_create_meter_action_logs_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%meter_action_logs}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'action' => $this->string(100)->notNull(),
            'details' => $this->text()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->dateTime()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%meter_action_logs}}');
    }
}
