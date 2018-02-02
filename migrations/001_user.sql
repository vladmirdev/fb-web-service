SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `role`, `salt`, `password`, `verify_phone`, `verify_method`, `status`, `country_id`, `language_id`, `is_paid`, `is_deleted`, `created_by`, `created_time`, `modified_by`, `modified_time`) VALUES
  (0,	'System',	'User',	'systemuser@plumflowerinternational.com',	NULL,	'JO3wjWZYHYulOcClmrTl5r6GDEHEOMuW',	'a0021160a38e75fed79cb8f1ee49d0b6',	NULL,	NULL,	1,	230,	1,	0,	0,	NULL,	'2017-10-17 05:48:49',	NULL,	'2017-10-17 05:48:49'),
  (1,	'Test',	'User',	'fbadmin@example.com',	NULL,	'JO3wjWZYHYulOcClmrTl5r6GDEHEOMuW',	'a0021160a38e75fed79cb8f1ee49d0b6',	NULL,	NULL,	1,	230,	1,	0,	0,	NULL,	'2017-10-17 05:48:49',	NULL,	'2017-10-17 05:48:49');