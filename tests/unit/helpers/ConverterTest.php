<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 09.09.17
 * Time: 16:39
 */

use app\helpers\Converter;

class ConverterTest extends \Codeception\Test\Unit
{

    public function testPinyinGeneration()
    {
        $string = 'Some Defined String';
        $code = 'sds';

        $result = Converter::toPinyinCode($string);

        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) === strlen($code));
        $this->assertTrue($result === $code);

        $string = 'String';
        $code = 's';

        $result = Converter::toPinyinCode($string);

        $this->assertTrue(is_string($result));
        $this->assertTrue(strlen($result) === strlen($code));
        $this->assertTrue($result === $code);
    }
}