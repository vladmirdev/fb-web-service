<?php

use yii\db\Migration;

class m171104_123644_device_deletion extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `device`
CHANGE `is_deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `platform_id`,
COMMENT='Devices';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171104_123644_device_deletion cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
