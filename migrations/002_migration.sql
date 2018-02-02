SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `migration` (`version`, `apply_time`) VALUES
  ('m000000_000000_base',	1506944430),
  ('m171002_102759_init_structure',	1506944446),
  ('m171002_182321_foreign_keys',	1507013543),
  ('m171003_063843_notes_fk',	1507013543),
  ('m171003_104323_formula_activity',	1507040270),
  ('m171005_130059_database_structure',	1507214422),
  ('m171005_184828_database_structure',	1507234359),
  ('m171006_063455_database_structure',	1507283591),
  ('m171006_102944_database_structure',	1507289309),
  ('m171006_165135_user_table',	1507401716),
  ('m171010_043928_queue',	1507616757),
  ('m171010_070752_email_templates',	1507625668),
  ('m171012_072119_countries_references',	1507797453),
  ('m171012_072130_user_country_fk',	1507797558),
  ('m171012_075729_languages_references',	1507797559),
  ('m171012_080156_user_language_fk',	1507797559),
  ('m171013_101720_users_login_history',	1507893836),
  ('m171017_054520_system_user',	1508224271),
  ('m171017_105214_references',	1508243879),
  ('m171017_172222_actions',	1508264636);