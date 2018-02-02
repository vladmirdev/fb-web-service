<?php

use yii\db\Migration;

class m171102_114012_devices extends Migration
{
    public function up()
    {
        $sql = <<<SQL
CREATE TABLE `device` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NULL COMMENT 'Name',
  `uid` varchar(255) NOT NULL COMMENT 'UUID',
  `vendor` varchar(255) NOT NULL COMMENT 'Vendor',
  `model` varchar(255) NOT NULL COMMENT 'Model',
  `os_version` varchar(255) NOT NULL COMMENT 'OS version',
  `app_version` varchar(255) NOT NULL COMMENT 'Application version',
  `is_deleted` tinyint(1) NOT NULL COMMENT 'Is deleted',
  `created_by` int(11) NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Modified time',
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`)
) COMMENT='Devices' ENGINE='InnoDB';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TABLE `device_platform` (
  `id` int NOT NULL COMMENT 'ID' AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL COMMENT 'Name',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) NULL COMMENT 'Created by',
  `created_time` datetime NULL COMMENT 'Created time',
  `modified_by` int(11) NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Modified time',
  FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) COMMENT='Device platforms' ENGINE='InnoDB';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE `device`
ADD `platform_id` int(11) NULL COMMENT 'Device platform' AFTER `app_version`,
ADD FOREIGN KEY (`platform_id`) REFERENCES `device_platform` (`id`) ON DELETE SET NULL,
COMMENT='Devices';
SQL;
        $this->execute($sql);

        $sql = <<<SQL
INSERT INTO `device_platform` (`id`, `name`, `is_deleted`, `created_by`, `created_time`, `modified_by`, `modified_time`) VALUES
(1,	'iOS',	0,	0,	'2017-11-02 12:20:51',	NULL,	'2017-11-02 12:20:51'),
(2,	'Android',	0,	0,	'2017-11-02 12:21:04',	NULL,	'2017-11-02 12:21:04'),
(3,	'Web',	0,	0,	'2017-11-02 12:21:04',	NULL,	'2017-11-02 12:21:04');
SQL;
        $this->execute($sql);

    }

    public function down()
    {
        echo "m171102_114012_devices cannot be reverted.\n";

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
