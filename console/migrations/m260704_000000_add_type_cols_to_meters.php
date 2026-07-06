<?php

use yii\db\Migration;

class m260704_000000_add_type_cols_to_meters extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%meters}}', 'frame_type', $this->string(50)->null()->after('meter_type'));
        $this->addColumn('{{%meters}}', 'diameter', $this->string(50)->null()->after('frame_type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%meters}}', 'frame_type');
        $this->dropColumn('{{%meters}}', 'diameter');
    }
}
