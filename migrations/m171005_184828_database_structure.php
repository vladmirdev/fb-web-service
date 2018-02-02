<?php

use yii\db\Migration;

class m171005_184828_database_structure extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

UPDAtE `formulas` set `favorite` = 0;
UPDAtE `formulas` set `read_only` = 0;

ALTER TABLE `formulas`
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
CHANGE `pinyin` `pinyin` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin' AFTER `name`,
CHANGE `pinyin_ton` `pinyin_ton` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin Ton' AFTER `pinyin`,
CHANGE `pinyin_code` `pinyin_code` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin Code' AFTER `pinyin_ton`,
CHANGE `english_name` `english_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `pinyin_code`,
CHANGE `simplified_chinese` `simplified_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Simplified chinese' AFTER `english_name`,
CHANGE `traditional_chinese` `traditional_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Traditional Chinese' AFTER `simplified_chinese`,
CHANGE `favorite` `is_favorite` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Favorite (flag)' AFTER `traditional_chinese`,
CHANGE `read_only` `is_readonly` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Readonly (flag)' AFTER `is_favorite`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `is_readonly`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Formulas';

ALTER TABLE `formula_source`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `source_id` `source_id` int(11) NOT NULL COMMENT 'Source' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `source_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
COMMENT='Formula sources';

ALTER TABLE `formula_source`
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
COMMENT='Formula sources';


SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171005_184828_database_structure cannot be reverted.\n";

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
