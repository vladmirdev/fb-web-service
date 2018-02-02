<?php

use yii\db\Migration;

class m171006_063455_database_structure extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

UPDAtE `herbs` set `favorite` = 0;
UPDAtE `herbs` set `read_only` = 0;

ALTER TABLE `herbs`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `pinyin` `pinyin` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin' AFTER `name`,
CHANGE `pinyin_code` `pinyin_code` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin code' AFTER `pinyin`,
CHANGE `english_name` `english_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `pinyin_code`,
CHANGE `simplified_chinese` `simplified_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Simplified chinese' AFTER `english_name`,
CHANGE `traditional_chinese` `traditional_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Traditional chinese' AFTER `simplified_chinese`,
CHANGE `latin_name` `latin_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Latin name' AFTER `traditional_chinese`,
CHANGE `english_common` `english_common` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English common' AFTER `latin_name`,
CHANGE `photo` `photo` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Photo' AFTER `english_common`,
CHANGE `favorite` `is_favorite` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is favorite' AFTER `photo`,
CHANGE `read_only` `is_readonly` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is readonly' AFTER `is_favorite`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `is_readonly`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herbs';

ALTER TABLE `herb_activity`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `activity_id` `activity_id` int(11) NOT NULL COMMENT 'Activity' AFTER `herb_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `activity_id`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb activity' COLLATE 'utf8_general_ci';

UPDATE herb_alternate SET `deleted` = 0;

ALTER TABLE `herb_alternate`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Original herb' AFTER `id`,
CHANGE `alternate_herb_id` `alternate_herb_id` int(11) NOT NULL COMMENT 'Alternate herb' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `alternate_herb_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`alternate_herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb alternates';

ALTER TABLE `herb_category`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `category_id` `category_id` int(11) NOT NULL COMMENT 'Category' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `category_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
COMMENT='Herb categories';

ALTER TABLE `herb_category`
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb categories';

ALTER TABLE `herb_caution`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `caution_id` `caution_id` int(11) NOT NULL COMMENT 'Caution' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `caution_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`caution_id`) REFERENCES `caution` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb cautions';

ALTER TABLE `herb_channel`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `channel_id` `channel_id` int(11) NOT NULL COMMENT 'Channel' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `channel_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb channels';

ALTER TABLE `herb_cultivation`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `cultivation_id` `cultivation_id` int(11) NOT NULL COMMENT 'Cultivation' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `cultivation_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`cultivation_id`) REFERENCES `cultivation` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb cultivations';

ALTER TABLE `herb_flavour`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `flavour_id` `flavour_id` int(11) NOT NULL COMMENT 'Flavour' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `flavour_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`flavour_id`) REFERENCES `flavour` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb flavours';

ALTER TABLE `herb_nature`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `nature_id` `nature_id` int(11) NOT NULL COMMENT 'Nature' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `nature_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`nature_id`) REFERENCES `nature` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb natures';

ALTER TABLE `herb_preparation`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `prep_id` `prep_id` int(11) NOT NULL COMMENT 'Preparation' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `prep_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`prep_id`) REFERENCES `preparation` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb preparations';

ALTER TABLE `herb_source`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `source_id` `source_id` int(11) NOT NULL COMMENT 'Source' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `source_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`source_id`) REFERENCES `source` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb sources';

ALTER TABLE `herb_species`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `species_id` `species_id` int(11) NOT NULL COMMENT 'Specie' AFTER `herb_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `species_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Herb species';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171006_063455_database_structure cannot be reverted.\n";

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
