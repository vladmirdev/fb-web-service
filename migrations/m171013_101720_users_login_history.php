<?php

use yii\db\Migration;

class m171013_101720_users_login_history extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `login_history` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NULL COMMENT 'User',
  `location` varchar(255) NOT NULL COMMENT 'Location',
  `action` varchar(255) NOT NULL COMMENT 'Action',
  `os` varchar(255) NOT NULL COMMENT 'OS',
  `useragent` varchar(255) NOT NULL COMMENT 'Browser',
  `ip_address` varchar(255) NOT NULL COMMENT 'IP address',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) NULL COMMENT 'Created by',
  `created_time` timestamp NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL COMMENT 'Modified time',
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) COMMENT='Users login history' ENGINE='InnoDB' COLLATE 'utf8_general_ci';

SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `login_history`
CHANGE `useragent` `useragent` varchar(500) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Useragent' AFTER `os`,
ADD `browser` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Browser' AFTER `useragent`,
COMMENT='Users login history';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `login_history`
CHANGE `location` `location` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Location' AFTER `user_id`,
COMMENT='Users login history';
SQL;
        $this->execute($sql);

    }

    public function safeDown()
    {
        echo "m171013_101720_users_login_history cannot be reverted.\n";

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
