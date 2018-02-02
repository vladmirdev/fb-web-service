<?php

use yii\db\Migration;

class m171110_032851_system_records extends Migration
{
    public function up()
    {
        $sql = <<<SQL
UPDATE formula_herb SET created_by = 0 WHERE created_by IS NULL;
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171110_032851_system_records cannot be reverted.\n";

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
