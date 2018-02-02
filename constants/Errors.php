<?php

namespace app\constants;

/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 08.09.17
 * Time: 12:16
 */

class Errors
{
    // User errors

    const USER_EMPTY_EMAIL                  = 1000;
    const USER_EMPTY_PASSWORD               = 1001;
    const USER_NOT_UNIQUE_EMAIL             = 1002;
    const USER_INVALID_EMAIL                = 1003;
    const USER_INVALID_CREDENTIALS          = 1004;
    const USER_WRONG_TOKEN                  = 1005;
    const USER_SESSION_EXPIRED              = 1006;
    const USER_WRONG_PASSWORD               = 1007;
    const USER_NOT_EXISTS                   = 1008;
    const USER_INVALID_VERIFY_CODE          = 1009;
    const USER_VERIFY_CODE_EXPIRED          = 1010;
    const USER_NOT_FOUND                    = 1012; // ? Possible duplicate for 1008
    const USER_VERIFY_CODE_USED             = 1013;
    const USER_ROLE_NOT_FOUND               = 1014;
    const USER_ROLE_ALREADY_ASSIGNED        = 1015;
    const USER_ROLE_NOT_ASSIGNED            = 1016;

    // Sync errors

    const SYNC_EMPTY_TOKEN_OR_DEVICE        = 1011;
    const SYNC_GENERAL_ERROR                = 1017;
    const SYNC_UNKNOWN_DEVICE               = 1018;
    const SYNC_UNKNOWN_TOKEN                = 1019;

    // General errors

    const MODEL_VALIDATION_ERROR            = 1020;

    // Herb errors

    const HERB_NOT_FOUND                    = 1100;
    const HERB_CATEGORY_NOT_FOUND           = 1101;
    const HERB_CAUTION_NOT_FOUND            = 1102;
    const HERB_CHANNEL_NOT_FOUND            = 1103;
    const HERB_NOTE_NOT_FOUND               = 1104;
    const HERB_CULTIVATION_NOT_FOUND        = 1105;
    const HERB_FLAVOUR_NOT_FOUND            = 1106;
    const HERB_NATURE_NOT_FOUND             = 1107;
    const HERB_PREP_NOT_FOUND               = 1108;
    const HERB_SOURCE_NOT_FOUND             = 1109;
    const HERB_SPECIE_NOT_FOUND             = 1110;
    const HERB_ALTERNATE_NOT_FOUND          = 1111;
    const HERB_ENGLISH_COMMON_NOT_FOUND     = 1112;
    const HERB_LATIN_NAME_NOT_FOUND         = 1113;
    const HERB_VIEWING_IS_FORBIDDEN         = 1114;
    const HERB_UPDATING_IS_FORBIDDEN        = 1115;
    const HERB_DELETING_IS_FORBIDDEN        = 1116;
    const HERB_SYMPTOM_NOT_FOUND            = 1117;
    const HERB_ACTION_NOT_FOUND             = 1118;
    const HERB_CREATION_ERROR               = 1119;
    const HERB_UPDATING_ERROR               = 1120;
    const HERB_DELETING_ERROR               = 1121;

    // Formula errors

    const FORMULA_NOT_FOUND                 = 1200;
    const FORMULA_HERB_NOT_FOUND            = 1201;
    const FORMULA_CATEGORY_NOT_FOUND        = 1202;
    const FORMULA_NOTE_NOT_FOUND            = 1203;
    const FORMULA_PREP_NOT_FOUND            = 1204;
    const FORMULA_SOURCE_NOT_FOUND          = 1205;
    const FORMULA_VIEWING_IS_FORBIDDEN      = 1206;
    const FORMULA_UPDATING_IS_FORBIDDEN     = 1207;
    const FORMULA_DELETING_IS_FORBIDDEN     = 1208;
    const FORMULA_CREATION_ERROR            = 1209;
    const FORMULA_UPDATING_ERROR            = 1210;
    const FORMULA_DELETING_ERROR            = 1211;
    const FORMULA_ACTION_NOT_FOUND          = 1212;
    const FORMULA_SYMPTOM_NOT_FOUND         = 1213;

    // Feedback errors

    const FEEDBACK_NOT_FOUND                = 1300;

    // Note errors

    const NOTE_NOT_FOUND                    = 1400;

    // Source errors

    const SOURCE_NOT_FOUND                  = 1500;

    // Element errors

    const ELEMENT_NOT_FOUND                 = 1600;

    // Channel errors

    const CHANNEL_NOT_FOUND                 = 1700;
    const CHANNEL_VIEWING_IS_FORBIDDEN      = 1701;
    const CHANNEL_UPDATING_IS_FORBIDDEN     = 1702;
    const CHANNEL_DELETING_IS_FORBIDDEN     = 1703;
    const CHANNEL_CREATION_ERROR            = 1704;
    const CHANNEL_UPDATING_ERROR            = 1705;
    const CHANNEL_DELETING_ERROR            = 1706;

    // Caution errors

    const CAUTION_NOT_FOUND                 = 1800;
    const CAUTION_VIEWING_IS_FORBIDDEN      = 1801;
    const CAUTION_UPDATING_IS_FORBIDDEN     = 1802;
    const CAUTION_DELETING_IS_FORBIDDEN     = 1803;
    const CAUTION_CATEGORY_NOT_FOUND        = 1804;
    const CAUTION_CREATION_ERROR            = 1805;
    const CAUTION_UPDATING_ERROR            = 1806;
    const CAUTION_DELETING_ERROR            = 1807;

    // Category errors

    const CATEGORY_NOT_FOUND                = 1900;
    const CATEGORY_CREATION_ERROR           = 1901;
    const CATEGORY_UPDATING_ERROR           = 1902;
    const CATEGORY_DELETING_ERROR           = 1903;

    // Book errors

    const BOOK_NOT_FOUND                    = 2000;
    const BOOK_CHAPTER_NOT_FOUND            = 2001;
    const BOOK_PAGE_NOT_FOUND               = 2002;
    const BOOK_VIEWING_IS_FORBIDDEN         = 2003;
    const BOOK_UPDATING_IS_FORBIDDEN        = 2004;
    const BOOK_DELETING_IS_FORBIDDEN        = 2005;

    // Cultivation errors

    const CULTIVATION_NOT_FOUND             = 2100;
    const CULTIVATION_VIEWING_IS_FORBIDDEN  = 2101;
    const CULTIVATION_UPDATING_IS_FORBIDDEN = 2102;
    const CULTIVATION_DELETING_IS_FORBIDDEN = 2103;

    // Cultivation errors

    const SYMPTOM_NOT_FOUND                 = 2200;
    const SYMPTOM_CATEGORY_NOT_FOUND        = 2201;
    const SYMPTOM_VIEWING_IS_FORBIDDEN      = 2202;
    const SYMPTOM_UPDATING_IS_FORBIDDEN     = 2203;
    const SYMPTOM_DELETING_IS_FORBIDDEN     = 2204;
    const SYMPTOM_CREATION_ERROR            = 2205;
    const SYMPTOM_UPDATING_ERROR            = 2206;
    const SYMPTOM_DELETING_ERROR            = 2207;

    // Nature errors

    const NATURE_NOT_FOUND                  = 2300;

    // Preparation errors

    const PREPARATION_NOT_FOUND             = 2400;
    const PREPARATION_CATEGORY_NOT_FOUND    = 2401;

    // Flavour errors

    const FLAVOUR_NOT_FOUND                 = 2500;

    // English common

    const ENGLISH_COMMON_NOT_FOUND          = 2600;

    // Latin name

    const LATIN_NAME_NOT_FOUND              = 2700;

    // References

    const REFERENCE_NOT_FOUND               = 2800;

    // Actions

    const ACTION_NOT_FOUND                  = 2900;
    const ACTION_CATEGORY_NOT_FOUND         = 2901;
    const ACTION_VIEWING_IS_FORBIDDEN       = 2902;
    const ACTION_UPDATING_IS_FORBIDDEN      = 2903;
    const ACTION_DELETING_IS_FORBIDDEN      = 2904;
    const ACTION_CREATION_ERROR             = 2905;
    const ACTION_UPDATING_ERROR             = 2906;
    const ACTION_DELETING_ERROR             = 2907;

    // Templates

    const TEMPLATE_NOT_FOUND                = 3000;

    // Countries and languages

    const COUNTRY_NOT_FOUND                 = 3100;
    const LANGUAGE_NOT_FOUND                = 3101;

    // Devices

    const DEVICE_NOT_FOUND                  = 3200;
    const PLATFORM_NOT_FOUND                = 3201;
    const DEVICE_VIEWING_IS_FORBIDDEN       = 3202;
    const DEVICE_UPDATING_IS_FORBIDDEN      = 3203;
    const DEVICE_DELETING_IS_FORBIDDEN      = 3204;
    const DEVICE_VALIDATION_ERROR           = 3205;


    private static $messages = [
        self::USER_EMPTY_EMAIL => 'Empty email',
        self::USER_VERIFY_CODE_EXPIRED => 'Verify code expired'
    ];

    /**
     * Returns error message
     *
     * ```php
     * <?php
     * Errors::getMessage(Errors::USER_EMPTY_EMAIL); // 'Empty email'
     * // or
     * Errors::getMessage(1000); // 'Empty email'
     * ```
     *
     * @param $code
     * @return mixed
     */
    public static function getMessage($code)
    {
        if (isset(self::$messages[$code])) {
            return self::$messages[$code];
        }

        return null;
    }
}