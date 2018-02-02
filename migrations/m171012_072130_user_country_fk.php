<?php

use yii\db\Migration;

class m171012_072130_user_country_fk extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET FOREIGN_KEY_CHECKS=0;        
ALTER TABLE `user`
ADD `country_id` int(11) NULL COMMENT 'Country' AFTER `status`;

ALTER TABLE `user`
ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171012_072130_user_country_fk cannot be reverted.\n";

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
