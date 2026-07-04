<?php

use yii\db\Migration;

/**
 * Tracks pushing of clerk-approved readings to the core billing API
 * (reading.hargeisawatertech.com).
 */
class m260702_000003_add_push_tracking_to_ledger extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%billing_ledger}}', 'push_status', $this->string(30)->notNull()->defaultValue('pending'));
        $this->addColumn('{{%billing_ledger}}', 'pushed_at', $this->dateTime()->null());
        $this->addColumn('{{%billing_ledger}}', 'push_response', $this->text()->null());
        $this->createIndex('idx_bl_push_status', '{{%billing_ledger}}', 'push_status');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_bl_push_status', '{{%billing_ledger}}');
        $this->dropColumn('{{%billing_ledger}}', 'push_status');
        $this->dropColumn('{{%billing_ledger}}', 'pushed_at');
        $this->dropColumn('{{%billing_ledger}}', 'push_response');
    }
}
