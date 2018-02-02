<?php

use yii\db\Migration;

class m171020_193500_books_authors extends Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `book`
ADD `chinese_author` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese author' AFTER `author`,
COMMENT='Books';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171020_193500_books_authors cannot be reverted.\n";

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
