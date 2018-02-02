<?php

use yii\db\Migration;

class m171002_182321_foreign_keys extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `user`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `firstname` `firstname` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'First name' AFTER `id`,
CHANGE `lastname` `lastname` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Last name' AFTER `firstname`,
CHANGE `email` `email` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Email' AFTER `lastname`,
ADD `salt` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Salt' AFTER `role`,
CHANGE `password` `password` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Password' AFTER `salt`,
CHANGE `deleted` `deleted` tinyint(1) NOT NULL DEFAULT '0' AFTER `paid`,
COMMENT='Users';

TRUNCATE TABLE verify_code;

ALTER TABLE `verify_code`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `verify_code` `verify_code` varchar(10) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Code' AFTER `id`,
CHANGE `user_id` `user_id` int(11) NOT NULL COMMENT 'User' AFTER `verify_code`,
CHANGE `used` `used` tinyint(1) NULL DEFAULT '0' COMMENT 'Is used flag' AFTER `user_id`,
CHANGE `expired_time` `expired_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Expire time' AFTER `used`,
COMMENT='Verify tokens';

TRUNCATE TABLE token;

ALTER TABLE `token`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'Id' AUTO_INCREMENT FIRST,
CHANGE `platform` `platform` varchar(20) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Platform' AFTER `id`,
CHANGE `access_token` `access_token` varchar(200) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Token' AFTER `platform`,
CHANGE `user_id` `user_id` int(11) NOT NULL COMMENT 'User' AFTER `access_token`,
CHANGE `deleted` `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted flag' AFTER `user_id`,
CHANGE `timeout` `timeout` int(11) NOT NULL COMMENT 'Timeout' AFTER `deleted`,
CHANGE `created_by` `created_by` int(11) NOT NULL COMMENT 'Created by' AFTER `timeout`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
COMMENT='Tokens';

ALTER TABLE `token`
DROP FOREIGN KEY `token_ibfk_1`,
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `verify_code`
CHANGE `verify_code` `verify_code` char(10) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Code' AFTER `id`,
CHANGE `used` `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is used flag' AFTER `user_id`,
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
COMMENT='Verify tokens';

TRUNCATE TABLE sync_history;

ALTER TABLE `sync_history`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `user_id` `user_id` int(11) NOT NULL COMMENT 'User' AFTER `id`,
CHANGE `device_id` `device_id` varchar(50) COLLATE 'utf8_general_ci' NULL COMMENT 'Device' AFTER `user_id`,
CHANGE `last_sync_time` `last_sync_time` timestamp NOT NULL COMMENT 'Last sync time' AFTER `device_id`,
ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
COMMENT='Sync history';

ALTER TABLE `formulas`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Name' AFTER `id`,
CHANGE `favorite` `favorite` tinyint(1) NULL DEFAULT '0' COMMENT 'Favorite (flag)' AFTER `name`,
CHANGE `read_only` `read_only` tinyint(1) NULL DEFAULT '0' COMMENT 'Readonly (flag)' AFTER `favorite`,
CHANGE `pinyin` `pinyin` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin' AFTER `read_only`,
CHANGE `pinyin_ton` `pinyin_ton` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin Ton' AFTER `pinyin`,
CHANGE `pinyin_code` `pinyin_code` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin Code' AFTER `pinyin_ton`,
CHANGE `english_name` `english_name` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `pinyin_code`,
CHANGE `simplified_chinese` `simplified_chinese` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Simplified chinese' AFTER `english_name`,
CHANGE `traditional_chinese` `traditional_chinese` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Traditional Chinese' AFTER `simplified_chinese`,
CHANGE `deleted` `deleted` tinyint(1) NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `traditional_chinese`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `deleted`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
COMMENT='Formulas';

ALTER TABLE `verify_code`
CHANGE `used` `is_used` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is used flag' AFTER `user_id`,
COMMENT='Verify tokens';

ALTER TABLE `token`
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted flag' AFTER `user_id`,
COMMENT='Tokens';

ALTER TABLE `formula_category`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `category_id` `category_id` int(11) NOT NULL COMMENT 'Category' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `category_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
COMMENT='Formula categories';

ALTER TABLE `formula_herb`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `herb_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
COMMENT='Formula herbs';

ALTER TABLE `formula_note`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `note_id` `note_id` int(11) NOT NULL COMMENT 'Note' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `note_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE,
COMMENT='Formula notes';

ALTER TABLE `formula_preparation`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `prep_id` `prep_id` int(11) NOT NULL COMMENT 'Preparation' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `prep_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`prep_id`) REFERENCES `preparation` (`id`) ON DELETE CASCADE,
COMMENT='Formula preparations';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171002_182321_foreign_keys cannot be reverted.\n";

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
