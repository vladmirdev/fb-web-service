<?php

use yii\db\Migration;

class m171102_122338_token_activation extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `token`
DROP `platform`,
ADD `platform_id` int(11) NULL COMMENT 'Device platform' AFTER `id`,
CHANGE `is_deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `user_id`,
ADD `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is active' AFTER `is_deleted`,
ADD FOREIGN KEY (`platform_id`) REFERENCES `device_platform` (`id`) ON DELETE SET NULL,
COMMENT='Tokens';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171102_122338_token_activation cannot be reverted.\n";

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
