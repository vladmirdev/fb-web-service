<?php

use yii\db\Migration;

/**
 * Class m171128_110553_sync_token
 */
class m171128_110553_sync_token extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = <<<SQL
ALTER TABLE `sync_history`
ADD `token` char(32) NOT NULL COMMENT 'Synchronization token',
ADD `is_confirmed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is confirmed' AFTER `token`,
COMMENT='Sync history';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE `sync_history`
CHANGE `last_sync_time` `last_sync_time` datetime NOT NULL COMMENT 'Last sync time' AFTER `device_id`,
COMMENT='Sync history';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE `sync_history`
ADD `confirm_time` datetime NULL COMMENT 'Confirm time' AFTER `last_sync_time`,
COMMENT='Sync history';
SQL;
        $this->execute($sql);

        \app\modules\v1\models\SyncHistory::updateAll(['is_confirmed' => 1]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171128_110553_sync_token cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171128_110553_sync_token cannot be reverted.\n";

        return false;
    }
    */
}
