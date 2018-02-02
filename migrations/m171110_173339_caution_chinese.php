<?php

use yii\db\Migration;

class m171110_173339_caution_chinese extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `caution`
ADD `simplified_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Simplified chinese' AFTER `name`,
ADD `traditional_chinese` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Traditional chinese' AFTER `simplified_chinese`,
COMMENT='Cautions';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171110_173339_caution_chinese cannot be reverted.\n";

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
