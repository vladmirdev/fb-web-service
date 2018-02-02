<?php

use yii\db\Migration;

/**
 * Class m171205_105036_herb_pinyin_ton
 */
class m171205_105036_herb_pinyin_ton extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = <<<SQL
ALTER TABLE `herbs`
ADD `pinyin_ton` varchar(255) COLLATE 'utf8_general_ci' NULL COMMENT 'Pinyin tone' AFTER `pinyin`,
COMMENT='Herbs';
SQL;
        $this->execute($sql);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171205_105036_herb_pinyin_ton cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171205_105036_herb_pinyin_ton cannot be reverted.\n";

        return false;
    }
    */
}
