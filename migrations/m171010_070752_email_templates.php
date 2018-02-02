<?php

use yii\db\Migration;

class m171010_070752_email_templates extends Migration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `template`;
CREATE TABLE `template` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(100) NOT NULL COMMENT 'Code',
  `title` varchar(255) NOT NULL COMMENT 'Title',
  `content` text NOT NULL COMMENT 'Content',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is deleted',
  `created_by` int(11) DEFAULT NULL COMMENT 'Created by',
  `created_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Created time',
  `modified_by` int(11) DEFAULT NULL COMMENT 'Modified by',
  `modified_time` timestamp NULL DEFAULT NULL COMMENT 'Modified time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  CONSTRAINT `template_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `template_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email templates';


-- 2017-10-10 07:07:32
SQL;
        $this->execute($sql);

        $sql = <<<SQL
INSERT INTO `template` (`id`, `code`, `title`, `content`, `is_deleted`, `created_by`, `created_time`, `modified_by`, `modified_time`) VALUES
(1,	'reset',	'Reset password',	'Hi {user}! <br/>\r\n\r\nThe verification code to reset your Formula Builder App password is:<br/>\r\n                    <span style=\"margin-left:10px;font-weight:bold;font-size:16px\">{code}</span><br/><br/>\r\n                    This code will expire in 24 hours.<br/>\r\n                    Regards,<br/>\r\n                    Formula Builder App Team',	0,	NULL,	'2017-10-10 08:36:27',	NULL,	'2017-10-10 08:36:27'),
(2,	'verify',	'Account verification',	'Welcome {user}!<br/>\r\nThe verification code for your Formula Builder App registration is:<br/>\r\n                    <span style=\"margin-left:10px;font-weight:bold;font-size:16px\">{code}</span><br/>\r\nThis code will expire in 24 hours.<br/><br/>\r\nRegards,<br/>\r\nFormula Builder App Team\'',	0,	NULL,	'2017-10-10 08:36:20',	NULL,	'2017-10-10 08:36:09');
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        $this->dropTable('template');
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
