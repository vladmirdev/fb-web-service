<?php

use yii\db\Migration;

class m171003_063843_notes_fk extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

ALTER TABLE `herb_note`
CHANGE `id` `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT FIRST,
CHANGE `herb_id` `herb_id` int(11) NOT NULL COMMENT 'Herb' AFTER `id`,
CHANGE `note_id` `note_id` int(11) NOT NULL COMMENT 'Note' AFTER `herb_id`,
CHANGE `deleted` `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `note_id`,
CHANGE `created_by` `created_by` int(11) NULL COMMENT 'Created by' AFTER `deleted`,
CHANGE `created_time` `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_by` `modified_by` int(11) NULL COMMENT 'Modified by' AFTER `created_time`,
CHANGE `modified_time` `modified_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
ADD FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE,
COMMENT='Herb notes';

ALTER TABLE `herb_note`
CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted' AFTER `note_id`,
COMMENT='Herb notes';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171003_063843_notes_fk cannot be reverted.\n";

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
