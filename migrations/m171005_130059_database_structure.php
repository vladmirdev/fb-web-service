<?php

use yii\db\Migration;

class m171005_130059_database_structure extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
        
ALTER TABLE `symptom`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Symptoms';

ALTER TABLE `species`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Species';

ALTER TABLE `source`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `date` `date` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Date' AFTER `id`,
CHANGE `author` `author` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Author' AFTER `date`,
CHANGE `english_name` `english_name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `author`,
CHANGE `chinese_name` `chinese_name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english_name`,
CHANGE `type` `type` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Type' AFTER `chinese_name`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `type`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified tiime' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Sources';

ALTER TABLE `preparation`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Name' AFTER `id`,
CHANGE `alternate_name` `alternate_name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Alternate name' AFTER `name`,
CHANGE `type` `type` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Type' AFTER `alternate_name`,
CHANGE `method` `method` text COLLATE 'utf8_general_ci' NULL COMMENT 'Method' AFTER `type`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `method`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Preparations';

ALTER TABLE `note`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `title` `title` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Title' AFTER `id`,
CHANGE `content` `content` text COLLATE 'utf8_general_ci' NULL COMMENT 'Content' AFTER `title`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `content`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Notes';

ALTER TABLE `nature`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(50) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Natures';

ALTER TABLE `book`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `english_name` `english_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `id`,
CHANGE `chinese_name` `chinese_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english_name`,
CHANGE `author` `author` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Author' AFTER `chinese_name`,
CHANGE `year` `year` char(4) COLLATE 'utf8_general_ci' NULL COMMENT 'Year' AFTER `author`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `year`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Books';

ALTER TABLE `book_chapter`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `book_id` `book_id` int(11) NOT NULL COMMENT 'Book' AFTER `id`,
CHANGE `english_name` `english_name` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `book_id`,
CHANGE `chinese_name` `chinese_name` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english_name`,
CHANGE `page_begin` `page_begin` int(11) NULL COMMENT 'Page (begin)' AFTER `chinese_name`,
CHANGE `page_end` `page_end` int(11) NULL COMMENT 'Page (end)' AFTER `page_begin`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `page_end`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified tiime' AFTER `modified_by`,
ADD FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Book chapters';

ALTER TABLE `book_page`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
ADD `book_id` int(11) NOT NULL COMMENT 'Book' AFTER `id`,
CHANGE `chapter_id` `chapter_id` int(11) NOT NULL COMMENT 'Chapter' AFTER `book_id`,
CHANGE `page` `page` int(11) NULL COMMENT 'Page' AFTER `chapter_id`,
CHANGE `english` `english` text COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `page`,
CHANGE `chinese` `chinese` text COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english`,
CHANGE `pinyin` `pinyin` text COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin name' AFTER `chinese`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `pinyin`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`chapter_id`) REFERENCES `book_chapter` (`id`) ON DELETE CASCADE,
COMMENT='Book pages';

ALTER TABLE `book_page`
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Book pages';

UPDATE category SET `read_only` = 0;

ALTER TABLE `category`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
DROP `timestamp`,
CHANGE `type` `type` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Type' AFTER `name`,
CHANGE `read_only` `is_readonly` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is readonly' AFTER `type`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `is_readonly`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Categories';

ALTER TABLE `token`
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `timeout`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Tokens';

ALTER TABLE `caution`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Cautions';

UPDATE channel SET `read_only` = 1;

ALTER TABLE `channel`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `icon_name` `icon_name` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Icon' AFTER `id`,
CHANGE `english_name` `english_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `icon_name`,
CHANGE `chinese_name` `chinese_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english_name`,
CHANGE `read_only` `is_readonly` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is readonly' AFTER `chinese_name`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `is_readonly`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Channels';

ALTER TABLE `cultivation`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Cultivations';

ALTER TABLE `element`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `syndrome` `syndrome` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Syndrome' AFTER `id`,
CHANGE `chinese_simplified` `chinese_simplified` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese simplified' AFTER `syndrome`,
CHANGE `chinese_traditional` `chinese_traditional` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese traditional' AFTER `chinese_simplified`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `chinese_traditional`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Elements';

ALTER TABLE `feedback`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `type` `type` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Type' AFTER `id`,
CHANGE `from` `from` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'From' AFTER `type`,
CHANGE `subject` `subject` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Subject' AFTER `from`,
CHANGE `content` `content` text COLLATE 'utf8_general_ci' NULL COMMENT 'Content' AFTER `subject`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `content`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Feedback messages';

ALTER TABLE `flavour`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `name`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Flavours';

ALTER TABLE `activity`
CHANGE `type` `type` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Object type' AFTER `id`,
CHANGE `obj_id` `obj_id` int(11) NOT NULL COMMENT 'Object ID' AFTER `type`,
CHANGE `action` `action` varchar(200) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Action' AFTER `obj_id`,
CHANGE `user_id` `created_by` int(11) NULL COMMENT 'User ID' AFTER `action`,
CHANGE `created_at` `created_time` datetime NULL COMMENT 'Create date' AFTER `created_by`,
COMMENT='Activity events';

ALTER TABLE `formula_activity`
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Formula activity';
SQL;

        $this->execute($sql);

    }

    public function down()
    {
        echo "m171005_130059_database_structure cannot be reverted.\n";

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
