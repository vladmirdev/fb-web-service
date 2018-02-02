<?php

use yii\db\Migration;

class m171010_043928_queue extends Migration
{
    public $tableName = '{{%queue}}';
    public $tableOptions;

    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'channel' => $this->string()->notNull(),
            'job' => $this->binary()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'started_at' => $this->integer(),
            'finished_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('channel', $this->tableName, 'channel');
        $this->createIndex('started_at', $this->tableName, 'started_at');

        $this->addColumn($this->tableName, 'timeout', $this->integer()->defaultValue(0)->notNull()->after('created_at'));

        $this->renameColumn($this->tableName, 'created_at', 'pushed_at');
        $this->addColumn($this->tableName, 'ttr', $this->integer()->notNull()->after('pushed_at'));
        $this->renameColumn($this->tableName, 'timeout', 'delay');
        $this->dropIndex('started_at', $this->tableName);
        $this->renameColumn($this->tableName, 'started_at', 'reserved_at');
        $this->createIndex('reserved_at', $this->tableName, 'reserved_at');
        $this->addColumn($this->tableName, 'attempt', $this->integer()->after('reserved_at'));
        $this->renameColumn($this->tableName, 'finished_at', 'done_at');

        $this->addColumn($this->tableName, 'priority', $this->integer()->unsigned()->notNull()->defaultValue(1024)->after('delay'));
        $this->createIndex('priority', $this->tableName, 'priority');
    }

    public function down()
    {
        $this->dropTable($this->tableName);
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
