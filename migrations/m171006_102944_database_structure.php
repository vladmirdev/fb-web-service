<?php

use yii\db\Migration;

class m171006_102944_database_structure extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `english_common`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='English common';

ALTER TABLE `formula_source`
ADD FOREIGN KEY (`source_id`) REFERENCES `source` (`id`) ON DELETE CASCADE;

UPDATE herb_english_common SET deleted = 0;

ALTER TABLE `herb_english_common`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `english_common_id` `english_common_id` int(11) NOT NULL COMMENT 'English common' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `english_common_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`english_common_id`) REFERENCES `english_common` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb english common';

ALTER TABLE `latin_name`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Latin names';

ALTER TABLE `herb_latin_name`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `latin_name_id` `latin_name_id` int(11) NOT NULL COMMENT 'Latin name' AFTER `herb_id`,
CHANGE `deleted` `deleted` tinyint(1) NULL COMMENT 'Is deleted' AFTER `latin_name_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`latin_name_id`) REFERENCES `latin_name` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb latin names';

UPDATE `herb_latin_name` SET `deleted` = 0;

ALTER TABLE `herb_latin_name`
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `latin_name_id`,
COMMENT='Herb latin names';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171006_102944_database_structure cannot be reverted.\n";

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
