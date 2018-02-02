<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 13.11.17
 * Time: 15:33
 */

namespace app\helpers;

class Converter
{

    /**
     * Generate Pinyin Code
     *
     * @param $string
     *
     * @return string
     */
    public static function toPinyinCode($string)
    {
        $ret = '';

        if(strpos($string, ' ') === false)
            return mb_strtolower(mb_substr($string, 0, 1), 'utf-8');

        foreach (explode(' ', $string) as $word)
            $ret .= mb_strtolower($word[0], 'utf-8');

        return $ret;
    }
}
