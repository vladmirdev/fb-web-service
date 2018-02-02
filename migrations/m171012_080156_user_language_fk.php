<?php

use yii\db\Migration;

class m171012_080156_user_language_fk extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `user`
ADD `language_id` int(10) unsigned NULL COMMENT 'Language' AFTER `country_id`;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `user`
ADD FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL;
SQL;
        $this->execute($sql);

    }

    public function safeDown()
    {
        echo "m171012_080156_user_language_fk cannot be reverted.\n";

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
