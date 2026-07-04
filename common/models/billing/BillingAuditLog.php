<?php

namespace common\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string|null $supply_no
 * @property int|null $raw_reading_id
 * @property int|null $ledger_id
 * @property string|null $details
 * @property string|null $ip_address
 * @property string $created_at
 */
class BillingAuditLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%billing_audit_logs}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'action'], 'required'],
            [['user_id', 'raw_reading_id', 'ledger_id'], 'integer'],
            [['details'], 'string'],
            [['action'], 'string', 'max' => 50],
            [['supply_no'], 'string', 'max' => 100],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }

    public static function record($action, array $attrs = [])
    {
        $log = new static();
        $log->user_id = Yii::$app->user->id ?? 0;
        $log->action = $action;
        $log->ip_address = Yii::$app->request instanceof \yii\web\Request
            ? Yii::$app->request->userIP : null;
        $log->created_at = date('Y-m-d H:i:s');
        foreach ($attrs as $k => $v) {
            $log->$k = $v;
        }
        return $log->save(false);
    }

    public function getUser()
    {
        return $this->hasOne(\backend\models\Users::class, ['user_id' => 'user_id']);
    }
}
