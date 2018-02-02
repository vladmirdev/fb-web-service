<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 09.09.17
 * Time: 16:06
 */

namespace app\helpers;

use app\constants\Roles;
use app\models\User;

class Security
{
    const ALGORITHM = 'sha1';
    const ITERATIONS = 10000;
    const PIN_LENGTH = 6;
    const PASS_LENGTH = 8;
    const SALT_LENGTH = 32;
    const HASH_LENGTH = 32;

    /**
     * Generate authorisation token
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateToken($length = self::HASH_LENGTH)
    {
        return \Yii::$app->security->generateRandomString($length);
    }

    /**
     * Generate random password
     *
     * @param string|null $password
     * @param string|null $salt
     * @param int $length
     *
     * @return string
     */
    public static function generatePassword($password = null, $salt = null, $length = self::PASS_LENGTH)
    {
        if($password === null)
            $password = self::generateToken($length);

        if($salt === null)
            $salt = self::generateSalt();

        return hash_pbkdf2(self::ALGORITHM, $password, $salt, self::ITERATIONS, self::HASH_LENGTH);
    }

    /**
     * Check password hash
     *
     * @param string $password
     * @param string $salt
     * @param string $hash
     *
     * @return bool
     */
    public static function checkPassword($password, $salt, $hash)
    {
        $currentHash = hash_pbkdf2(self::ALGORITHM, $password, $salt, self::ITERATIONS, self::HASH_LENGTH);

        return $currentHash === $hash;
    }

    /**
     * Generate random salt
     *
     * @return string
     */
    public static function generateSalt()
    {
        return \Yii::$app->security->generateRandomString(self::SALT_LENGTH);
    }

    /**
     * Generate random digital PIN
     *
     * @param int $length
     *
     * @return int
     */
    public static function generatePin($length = self::PIN_LENGTH)
    {
        $min = (int) str_pad('1', $length, '0');
        $max = (int) str_pad('9', $length, '9');

        return rand($min, $max);
    }

    /**
     * Get record author
     * @see System records workflow
     *
     * @return int|null|string
     */
    public static function getAuthor()
    {
        if(\Yii::$app->request->isConsoleRequest)
            return User::SYSTEM;

        if(\Yii::$app->user->isGuest)
            return null;

        if((\Yii::$app->request->isPost || \Yii::$app->request->isPut || \Yii::$app->request->isDelete) && self::isAdmin())
            return User::SYSTEM;

        return \Yii::$app->user->getId();
    }

    /**
     * Check user is admin
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return !\Yii::$app->user->isGuest && \Yii::$app->user->identity->isAdmin;
    }
}
