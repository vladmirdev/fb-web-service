<?php

use yii\db\Migration;

class m171121_113241_symptom_colors extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `symptom`
ADD `color` char(7) COLLATE 'utf8_general_ci' NULL COMMENT 'Color' AFTER `traditional_chinese`,
COMMENT='Symptoms';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        $this->dropColumn(\app\modules\v1\models\Symptom::tableName(), 'color');
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
