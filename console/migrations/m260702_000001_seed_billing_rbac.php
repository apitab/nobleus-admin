<?php

use yii\db\Migration;

/**
 * Seeds RBAC data for the billing module:
 * - "billing" module row + its actions
 * - "Billing Clerks" group with read/import permissions only
 * Administrators (group_id = 1) bypass permission checks entirely.
 */
class m260702_000001_seed_billing_rbac extends Migration
{
    private $clerkActions = [
        'dashboard/index',
        'readings/index',
        'readings/view',
        'readings/import',
        'readings/reject',
        'customers/index',
        'flowmeters/index',
        'flowmeters/view',
        'reports/general',
        'reports/import-status',
        'reports/comparative',
    ];

    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $this->insert('{{%modules}}', [
            'module_name' => 'billing',
            'description' => 'Billing portal (ThingsBoard meter readings)',
            'status' => 1,
            'date_created' => $now,
        ]);
        $moduleId = $this->db->getLastInsertID();

        $this->insert('{{%groups}}', [
            'name' => 'Billing Clerks',
            'description' => 'Read and import meter readings only',
            'status' => 1,
            'date_created' => $now,
        ]);
        $groupId = $this->db->getLastInsertID();

        foreach ($this->clerkActions as $action) {
            $this->insert('{{%module_actions}}', [
                'module_id' => $moduleId,
                'name' => $action,
                'description' => 'Billing: ' . $action,
                'status' => 1,
                'date_created' => $now,
            ]);
            $actionId = $this->db->getLastInsertID();

            $this->insert('{{%permissions}}', [
                'module_id' => $moduleId,
                'action_id' => $actionId,
                'group_id' => $groupId,
                'status' => 1,
                'date_created' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $moduleId = $this->db->createCommand("SELECT id FROM {{%modules}} WHERE module_name = 'billing'")->queryScalar();
        if ($moduleId) {
            $this->delete('{{%permissions}}', ['module_id' => $moduleId]);
            $this->delete('{{%module_actions}}', ['module_id' => $moduleId]);
            $this->delete('{{%modules}}', ['id' => $moduleId]);
        }
        $this->delete('{{%groups}}', ['name' => 'Billing Clerks']);
    }
}
