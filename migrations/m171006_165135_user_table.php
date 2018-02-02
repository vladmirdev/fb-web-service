<?php

use yii\db\Migration;

class m171006_165135_user_table extends Migration
{
    public function safeUp()
    {

        $sql = <<<SQL
UPDATE `user` SET paid = 0;
UPDATE `user` SET status = 0;
UPDATE `user` SET deleted = 0 WHERE deleted IS NULL;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `user`
CHANGE `role` `role` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Role (unused)' AFTER `email`,
CHANGE `verify_phone` `verify_phone` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Verify phone' AFTER `password`,
CHANGE `verify_method` `verify_method` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Verify method' AFTER `verify_phone`,
CHANGE `status` `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Status' AFTER `verify_method`,
CHANGE `paid` `is_paid` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Paid' AFTER `status`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `is_paid`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
COMMENT='Users';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `user`
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `user`
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $this->execute($sql);

    }

    public function safeDown()
    {
        echo "m171006_165135_user_table cannot be reverted.\n";

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
