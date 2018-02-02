<?php

use yii\db\Migration;

class m171110_064707_category_colors extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `category`
ADD `color` char(7) COLLATE 'utf8_general_ci' NULL COMMENT 'Color' AFTER `type`,
COMMENT='Categories';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171110_064707_category_colors cannot be reverted.\n";

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
