<?php

use yii\db\Migration;

class m171102_110900_subscriptions extends Migration
{
    public function up()
    {
        $sql = <<<SQL
CREATE TABLE `subscription_platform` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL COMMENT 'Name',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) NULL DEFAULT '0' COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Modified time',
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) COMMENT='Subscription platforms' ENGINE='InnoDB';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TABLE `package` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL COMMENT 'Name',
  `description` text NULL COMMENT 'Description',
  `platform_package_id` varchar(255) NOT NULL COMMENT 'Platform package',
  `subscription_platform_id` int(11) NOT NULL COMMENT 'Subscription platform',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is active',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Modified time',
  FOREIGN KEY (`subscription_platform_id`) REFERENCES `subscription_platform` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) COMMENT='Packages' ENGINE='InnoDB';
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171102_110900_subscriptions cannot be reverted.\n";

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
