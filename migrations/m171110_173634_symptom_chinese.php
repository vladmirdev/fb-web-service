<?php

use yii\db\Migration;

class m171110_173634_symptom_chinese extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `symptom`
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'Name' AFTER `id`,
ADD `simplified_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Simplified chinese' AFTER `name`,
ADD `traditional_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Traditional chinese' AFTER `simplified_chinese`,
CHANGE `created_time` `created_time` datetime NULL COMMENT 'Created time' AFTER `created_by`,
CHANGE `modified_time` `modified_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Modified time' AFTER `modified_by`,
COMMENT='Symptoms';
SQL;

        $this->execute($sql);
    }

    public function down()
    {
        echo "m171110_173634_symptom_chinese cannot be reverted.\n";

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
