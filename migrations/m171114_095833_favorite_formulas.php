<?php

use yii\db\Migration;

class m171114_095833_favorite_formulas extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `formula_favorites`;
CREATE TABLE `formula_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `formula_id` int(11) NOT NULL COMMENT 'Formula',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` datetime DEFAULT NULL COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  KEY `formula_id` (`formula_id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `formula_favorites_ibfk_1` FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formula_favorites_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formula_favorites_ibfk_3` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Formula favorites';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171114_095833_favorite_formulas cannot be reverted.\n";

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
