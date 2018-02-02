<?php

use yii\db\Migration;

/**
 * Class m171128_061129_update_books
 */
class m171128_061129_update_books extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = <<<SQL
ALTER TABLE `book_chapter`
CHANGE `english_name` `english_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'English name' AFTER `book_id`,
CHANGE `chinese_name` `chinese_name` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese name' AFTER `english_name`,
ADD `english` text COLLATE 'utf8_general_ci' NULL COMMENT 'English content' AFTER `chinese_name`,
ADD `chinese` text COLLATE 'utf8_general_ci' NULL COMMENT 'Chinese content' AFTER `english`,
ADD `pinyin` text COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin content' AFTER `chinese`,
DROP `page_begin`,
DROP `page_end`,
CHANGE `created_time` `created_time` datetime NULL COMMENT 'Created time' AFTER `created_by`,
COMMENT='Book chapters';
SQL;
        $this->execute($sql);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171128_061129_update_books cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171128_061129_update_books cannot be reverted.\n";

        return false;
    }
    */
}
