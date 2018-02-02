<?php

use yii\db\Migration;

/**
 * Class m171214_073912_books
 */
class m171214_073912_books extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = <<<SQL
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELETE FROM book;

ALTER TABLE `book`
COMMENT='Sources/texts' AUTO_INCREMENT=1;

INSERT INTO `book` (`id`, `english_name`, `chinese_name`, `author`, `chinese_author`, `year`, `is_deleted`, `created_by`, `created_time`, `modified_by`, `modified_time`) VALUES
(1,	'Miscellaneous Records of Famous Physicians',	'名醫別錄',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(2,	'Tang Materia Medica',	'唐本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(3,	'Omissions from the Material Medica',	'本草拾遺',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(4,	'Medicinal Plants of China',	'中國藥植誌',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(5,	'Household Material Medica',	'日用本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(6,	'Guangxi Chinese Materia Medica',	'廣西中藥誌',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:05',	0,	'2017-12-14 07:38:30'),
(7,	'Collection of Commentaries on Classic of Materia Medica',	'本草經集注',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(8,	'Materia Medica of Medicinal Properties',	'藥性本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(9,	'Compendium of Materia Medica',	'本草綱目',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(10,	'Materia Medica of the Kaibo Era',	'開寶本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(11,	'Illustrated Classic of Materia Medica',	'圖經本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(12,	'Discussion of Medicinal Properties',	'藥對論',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(13,	'Medicinal Recipes',	'藥譜',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(14,	'Materia Medica of Ri Hua-Zi',	'日華子',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(15,	'Study of Medicinal Substances',	'藥材學',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(16,	'Treasury of Words on the Materia Medica',	'本草彙言',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(17,	'Encoutering the Sources of the Classic of Materia Media',	'本草逢原',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(18,	'Thoroughly Revised Materia Medica',	'本草從新',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(19,	'Great Compendium of Pharmacology',	'藥物學大成',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(20,	'Materia Medica of the Jiayou Era',	'嘉祐本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(21,	'Seaboard Herbal Medicine Materia Medica',	'海藥本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(22,	'Materia Medica of Diet Therapy',	'食療本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(23,	'Important Formulas Worth a Thousand Gold',	'千金要方',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(24,	'Materia Medica Arranged According to Pattern',	'證類本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(25,	'Materia Medica of Sichuan',	'蜀本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(26,	'Origins of Medicine',	'醫學啟源',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(27,	'Extensions of the Materia Medica',	'本草衍義',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(28,	'Modern Practical Chinese Medicine',	'現代實用中藥',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(29,	'Origins of the Materia Medica',	'本草原始',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(30,	'Hidden Aspects of the Materia Medica',	'本草蒙荃',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(31,	'Supplement to the Extension of the Materia Medica',	'本草衍義補遺',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:06',	0,	'2017-12-14 07:38:30'),
(32,	'Materia Medica of Steep Mountainsides',	'履巉巖本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:07',	0,	'2017-12-14 07:38:30'),
(33,	'Materia Medica Essential Distinctions',	'本草品匯精要',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:07',	0,	'2017-12-14 07:38:30'),
(34,	'Materia Medica of South Yunnan',	'滇南本草',	NULL,	NULL,	NULL,	0,	0,	'2017-06-04 11:11:07',	0,	'2017-12-14 07:38:30'),
(35,	'Straight Directions of Benevolent Aid',	'仁齋直指',	'Yang Tu-Ying',	NULL,	'1264',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(36,	'Systematic Differentiation of Warm Diseases',	'溫病條辨',	'Wu Ju-Tong',	NULL,	'1798',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(37,	'Restoration of Health from the Myriad Diseases',	'萬病回春',	'Gong Ting-Xian',	NULL,	'1587',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(38,	'Catalogued Essentials for Rectifying the Body',	'正體類要',	'Bi Li-Zhai',	NULL,	'1529',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(39,	'Imperial Grace Formulary of the Tai Ping Era',	'太平惠民和濟局方',	'Imperial Medical Department',	NULL,	'1078',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(40,	'Craft of Medicinal Treatment for Childhood Disease Patterns',	'小几藥證訣',	'Qian Yi',	NULL,	'1119',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(41,	'Analytic Collection of Medicinal Formulas',	'醫方集解',	'Wang Ang',	NULL,	'1682',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(42,	'Essentials from the Golden Cabinet',	'金匱要略',	'Zhang Zhong-Jing',	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(43,	'Discussion of Cold Damage',	'傷寒論',	'Zhang Zhong-Jing',	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(44,	'Collected Treatises of [Zhang] Jing-Yue',	'景岳全書',	'Zhang Jing-Yue',	NULL,	'1624',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(45,	'Compilation of Materials of Benevolence for the Body',	'體仁匯編',	'Peng Yong-Guang',	NULL,	'1549',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(46,	'Medical Revelations',	'醫學心悟',	'Cheng Guo-Peng',	NULL,	'1732',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(47,	'Teachings of Zhu Dan-Xi',	'丹溪心法',	'Zhu Zhen-Heng',	NULL,	'1481',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(48,	'Collected Treatises of [Zhong] Jing-Yue',	'景岳全書',	'Zhang Jing-Yue',	NULL,	'1624',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(49,	'Corrections of Errors Among Physicians',	'醫林改錯',	'Wang Qing-Ren',	NULL,	'1830',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(50,	'Discussion of the Spleen and Stomach',	'脾胃論',	'Li Ao',	NULL,	'1249',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(51,	'Formulas to Aid the Living',	'濟生方',	'Yan Yong-He',	NULL,	'1253',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(52,	'Thousand Ducat Formulas',	'千金要方',	'Sun Si-Miao',	NULL,	'652',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(53,	'Emergency Formulas to Keep Up One&rsquo;s Sleeve',	'时后备急方',	'Ge Hong',	NULL,	'341',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(54,	'Summary of Internal Medicine',	'內科摘要',	'Edited by Wen Sheng',	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(55,	'Clarifying Doubts about Inquiry from Internal and External Causes',	'內外傷辨惑',	'Li Ao',	NULL,	'1247',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(56,	'Supplement to the Thousand Ducat Formulas',	'千金翼方',	'Sun Si-Miao',	NULL,	'682',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(57,	'Secrets from the Orchid Chamber',	'蘭室柲藏',	'Li Ao',	NULL,	'1336',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(58,	'National Collection of Chinese Herbal Prepared Medicines',	'全國中成藥處方劑',	NULL,	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(59,	'Exquisite Formulas for Fostering Longevity',	'枎壽精方',	'Wu Min',	NULL,	'1530',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(60,	'Pattern, Cause, Pulse and Treatment',	'饪沔證因脉治',	'Qin Jing-Ming',	NULL,	'1702',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(61,	'Traditional Chinese Medical Formulas',	'方劑學',	'Shanghai College of Traditional Chinese Medicine',	NULL,	'1975',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(62,	'Medical Innovations',	'醫學發明',	'Li Ao',	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(63,	'Warp and Woof of Warm Febrile Diseases',	'醫學正傳',	'Wang Meng-Ying',	NULL,	'1852',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(64,	'Women&rsquo;s Diseases According to Fu Qing-Zhu',	'傅青主女科',	'Fu Shan (Fu Qing-Zhu)',	NULL,	'1827',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(65,	'Records of Heart-felt Experiences in Medicine Regarding the West',	'醫學衷中參西錄',	'Zhang Xi-Chun',	NULL,	'1918',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(66,	'Introduction to Medicine',	'醫學入門',	'Li Ting',	NULL,	'1575',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(67,	'Additions to the Teachings of [Zhu] Dan-Xi',	'丹溪心法附餘',	'Fang Guang-Lei',	NULL,	'1536',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(68,	'True Lineage of External Medicine',	'外科正宗',	'Chen Shi-Gong',	NULL,	'1617',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(69,	'Revised Popular Guide to the Discussion of Cold Damage',	'重訂通俗傷寒論',	'Yu Gen-Chu',	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(70,	'Six Texts on the Essentials of Medicine',	'醫略六書',	NULL,	NULL,	'0',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(71,	'Formulas of Universal Benefit from My Practice',	'普濟本事方',	'Xu Shu-Wei',	NULL,	'1132',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(72,	'Arcane Collection of Medicinal Formulas',	'㚈台秘要',	'Wang Tao',	NULL,	'1682',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(73,	'Six Texts on Cold Damage',	'傷寒六書',	'Tao Hua',	NULL,	'1445',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(74,	'True Lineage of Medicine',	'醫學正傳',	'Yu Tian-Min',	NULL,	'1515',	0,	0,	'2017-06-04 11:25:34',	0,	'2017-12-14 07:38:30'),
(75,	'Standards of Patterns and Treatment',	'證治準繩',	'Wang Ken-Tang',	NULL,	'1602',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(76,	'Comprehensive Medicine According to Master Han',	'韓氏醫通',	'Han Mao',	NULL,	'1522',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(77,	'Hard Won Knowledge',	'此事難知',	'Wang Hao-Gu',	NULL,	'1308',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(78,	'Discussion of Sudden Turmoil Disorders',	'霍㐖論',	'Wan Shi-Xiong',	NULL,	'1862',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(79,	'Revised Fine Formulas for Women',	'校注婦人良方',	'Bi Li-Zhai',	NULL,	'16',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(80,	'Handbook of Traditional Chinese Medicinal Preparations',	'中藥制劑手冊',	NULL,	NULL,	'0',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(81,	'Direct Investigations of Formulas for Cold Damage',	'傷寒直格方論',	'Liu Yuan-Su edited by Ge Yong',	NULL,	'0',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(82,	'Scrutiny of the Priceless Jade Case',	'審視瑤函',	'Fu Ren-Yu',	NULL,	'1644',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(83,	'Confucian&rsquo;s Duties to Their Parents',	'儒門事親',	'Zhang Cong-Zheng',	NULL,	'1228',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(84,	'Precious Mirror of Health',	'衛生宝鑒',	'Luo Tian-Yi',	NULL,	'0',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(85,	'Precious Mirror for Advancement of Medicine',	'醫藉宝鑒',	'Dong Xi-Yuan',	NULL,	'1777',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(86,	'Small Collection of Fine Formulas',	'良方集嘢',	'Xie Yuan-Qing',	NULL,	'1842',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(87,	'Collected Formulas of the Yang Family',	'楊氏家藏方',	'Yang Tan',	NULL,	'1178',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(88,	'Investigations of Medical Formulas',	'醫方攷',	'Wu Kun',	NULL,	'1584',	1,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(89,	'Warp and Woof of Warm Febrile Diseases',	'溫熱經緯',	'Wang Meng-Ying',	NULL,	'1852',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(90,	'Achievements Regarding Epidemic Rashes',	'疫疹一得',	'Yu Shi-Yu',	NULL,	'1794',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(91,	'Precepts for Physicians',	'醫門法律',	'Yu Chang',	NULL,	'1658',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(92,	'Master Shen&rsquo;s book for Revering Life',	'沈氏尊生書',	'Shen Jin-Ao',	NULL,	'1773',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(93,	'Fine Formulas for Women',	'婦人良方',	'Chen Zi-Ming',	NULL,	'1237',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(94,	'Extension of the Materia Medica',	'本草衍義',	'Kou Zong-Shi',	NULL,	'1116',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(95,	'Miraculous Book of Ten Remedies',	'十藥神書',	'Ge Qian-Sun',	NULL,	'1348',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(96,	'Effective Formulas from Generations of Physicians',	'世醫得效方',	'Wei Yi-Lin',	NULL,	'1345',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(97,	'Convenient Reader of Established Formulas',	'成方便讀',	'Zhang Bing-Cheng',	NULL,	'1904',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(98,	'New Compilation of Time-tested Formulas',	'驗方新編',	'Bao Xiang-Ao',	NULL,	'1846',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(99,	'Golden Mirror of the Medical Tradition',	'醫宗金鑒',	'Wu Qian',	NULL,	'1742',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(100,	'New Significance of Patterns and Treatment in Miscellaneous Diseases',	'雜病證治新義',	NULL,	NULL,	'0',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(101,	'Secret Investigations into Obtaining Health',	'攝生秘剖',	'Hong Ji (Jiu-You)',	NULL,	'1638',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(102,	'Comprehensive Medicine According to Master Zhang',	'張氏醫通',	'Zhang Lu-Xuan',	NULL,	'1695',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(103,	'Complete Collection of Patterns and Treatments in External Medicine',	'外科證治全生集',	'Wang Wei-De',	NULL,	'1740',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(104,	'Comprehensive Collection of Medicine Past and Present',	'古今醫通大全',	'Xu Chun-Fu',	NULL,	'1556',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(105,	'Jade Key to Many Towers',	'重熼樓玉鑰',	'Zheng Mei-Run',	NULL,	'1838',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(106,	'Synopsis of Caring for Infants',	'保嬰樶要',	'Xue Kai',	NULL,	'1555',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30'),
(107,	'Continuation of Famous Physicians&rsquo; Cases by Category',	'續名醫類案',	'Wei Zhi-Xiu',	NULL,	'1770',	0,	0,	'2017-06-04 11:25:35',	0,	'2017-12-14 07:38:30');

ALTER TABLE `formula_source`
DROP FOREIGN KEY `formula_source_ibfk_4`,
ADD FOREIGN KEY (`source_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `herb_source`
DROP FOREIGN KEY `herb_source_ibfk_2`,
ADD FOREIGN KEY (`source_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
SQL;
        $this->execute($sql);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171214_073912_books cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171214_073912_books cannot be reverted.\n";

        return false;
    }
    */
}
