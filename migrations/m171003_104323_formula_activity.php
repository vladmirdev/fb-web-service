<?php

use yii\db\Migration;

class m171003_104323_formula_activity extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `formula_activity`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' FIRST,
CHANGE `formula_id` `formula_id` int(11) NOT NULL COMMENT 'Formula' AFTER `id`,
CHANGE `activity_id` `activity_id` int(11) NOT NULL COMMENT 'Activity' AFTER `formula_id`,
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `activity_id`,
CHANGE `created_by` `created_by` int(11) NOT NULL COMMENT 'Created by' AFTER `is_deleted`,
CHANGE `created_time` `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`formula_id`) REFERENCES `formulas` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
COMMENT='Formula activity';

ALTER TABLE `formula_activity`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
COMMENT='Formula activity';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171003_104323_formula_activity cannot be reverted.\n";

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
