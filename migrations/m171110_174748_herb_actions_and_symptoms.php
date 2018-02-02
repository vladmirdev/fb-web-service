<?php

use yii\db\Migration;

class m171110_174748_herb_actions_and_symptoms extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `herb_action`;
CREATE TABLE `herb_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `herb_id` int(11) NOT NULL COMMENT 'Herb',
  `action_id` int(11) NOT NULL COMMENT 'Action',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  KEY `herb_id` (`herb_id`),
  KEY `action_id` (`action_id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `herb_action_ibfk_1` FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_action_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_action_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `herb_action_ibfk_4` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Herb actions';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `herb_symptom`;
CREATE TABLE `herb_symptom` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `herb_id` int(11) NOT NULL COMMENT 'Herb',
  `symptom_id` int(11) NOT NULL COMMENT 'Symptom',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  KEY `herb_id` (`herb_id`),
  KEY `symptom_id` (`symptom_id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `herb_symptom_ibfk_1` FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_symptom_ibfk_2` FOREIGN KEY (`symptom_id`) REFERENCES `symptom` (`id`) ON DELETE CASCADE,
  CONSTRAINT `herb_symptom_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `herb_symptom_ibfk_4` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Herb symptoms';
SQL;
        $this->execute($sql);


    }

    public function down()
    {
        echo "m171110_174748_herb_actions_and_symptoms cannot be reverted.\n";

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
