<?php

use yii\db\Migration;

class m171110_050220_preparation_categories extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `preparation_category`;
CREATE TABLE `preparation_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `prep_id` int(11) NOT NULL COMMENT 'Preparation',
  `category_id` int(11) NOT NULL COMMENT 'Category',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  KEY `prep_id` (`prep_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `preparation_category_ibfk_1` FOREIGN KEY (`prep_id`) REFERENCES `preparation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `preparation_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `preparation_category_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `preparation_category_ibfk_4` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Preparation categories';


-- 2017-11-10 05:00:31
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171110_050220_preparation_categories cannot be reverted.\n";

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
