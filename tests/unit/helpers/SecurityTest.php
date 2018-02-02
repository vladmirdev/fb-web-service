<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 09.09.17
 * Time: 16:39
 */

use app\helpers\Security;

class SecurityTest extends \Codeception\Test\Unit
{

    public function testPinGeneration()
    {
        $pin = Security::generatePin(Security::PIN_LENGTH);

        $this->assertTrue(is_numeric($pin) === true);
        $this->assertTrue(strlen($pin) === Security::PIN_LENGTH);
    }

    public function testTokenGeneration()
    {
        $token = Security::generateToken(Security::HASH_LENGTH);

        $this->assertTrue(is_string($token));
        $this->assertTrue(strlen($token) === Security::HASH_LENGTH);
    }

    public function testSaltGeneration()
    {
        $salt = Security::generateSalt();

        $this->assertTrue(is_string($salt));
        $this->assertTrue(strlen($salt) === Security::SALT_LENGTH);

        $salt2 = Security::generateSalt();

        $this->assertFalse($salt2 === $salt);
    }

    public function testGenerateRandomPassword()
    {
        $password = null;

        $hash = Security::generatePassword();

        $this->assertTrue(is_string($hash));
        $this->assertTrue(strlen($hash) === Security::HASH_LENGTH);
    }

    public function testGenerateDefinedPassword()
    {
        $password = 'sOmErAnDoMpAsSw0rD1';

        $hash = Security::generatePassword($password);

        $this->assertTrue(is_string($hash));
        $this->assertTrue(strlen($hash) === Security::HASH_LENGTH);
    }

    public function testCheckDefinedPassword()
    {
        $password = 'sOmErAnDoMpAsSw0rD2';

        $salt = Security::generateSalt();
        $hash = Security::generatePassword($password, $salt);

        $this->assertTrue(is_string($hash));
        $this->assertTrue(strlen($hash) === Security::HASH_LENGTH);

        $result = Security::checkPassword($password, $salt, $hash);

        $this->assertTrue(is_bool($result));
        $this->assertTrue($result);

        $result2 = Security::checkPassword(strrev($password), $salt, $hash);

        $this->assertTrue(is_bool($result2));
        $this->assertFalse($result2);
    }
}