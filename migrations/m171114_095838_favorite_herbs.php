<?php

use yii\db\Migration;

class m171114_095838_favorite_herbs extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `herb_favorites`;
CREATE TABLE `herb_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `herb_id` int(11) NOT NULL COMMENT 'Herb',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` datetime DEFAULT NULL COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  KEY `herb_id` (`herb_id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `herb_favorites_ibfk_1` FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_favorites_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_favorites_ibfk_3` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Herb favorites';
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        echo "m171114_095838_favorite_herbs cannot be reverted.\n";

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
