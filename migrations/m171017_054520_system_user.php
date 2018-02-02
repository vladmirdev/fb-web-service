<?php

use yii\db\Migration;

class m171017_054520_system_user extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `role`, `salt`, `password`, `verify_phone`, `verify_method`, `status`, `country_id`, `language_id`, `is_paid`, `is_deleted`, `created_by`, `created_time`, `modified_by`, `modified_time`)
VALUES ('0', 'System', 'User', 'systemuser@plumflowerinternational.com', NULL, 'JO3wjWZYHYulOcClmrTl5r6GDEHEOMuW', 'a0021160a38e75fed79cb8f1ee49d0b6', NULL, NULL, '1', '230', '1', '0', '0', NULL, now(), NULL, now());
SQL;

        $this->execute($sql);

        $sql = <<<SQL
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

UPDATE `formulas` SET `created_by` = 0 WHERE `created_by` IS NULL;
UPDATE `herbs` SET `created_by` = 0 WHERE `created_by` IS NULL;
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171017_054520_system_user cannot be reverted.\n";

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
