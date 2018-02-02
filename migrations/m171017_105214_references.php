<?php

use yii\db\Migration;

class m171017_105214_references extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `references` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `content` text NOT NULL COMMENT 'Reference',
  `book_id` int(11) NULL COMMENT 'Book',
  `app_url` varchar(255) NULL COMMENT 'Google play / App Store',
  `web_url` varchar(255) NULL COMMENT 'Web',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) NULL COMMENT 'Created by',
  `created_time` timestamp NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL COMMENT 'Modified time',
  FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) COMMENT='References' ENGINE='InnoDB' COLLATE 'utf8_general_ci';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171017_105214_references cannot be reverted.\n";

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
