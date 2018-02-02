<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.09.17
 * Time: 23:23
 */

class UserTest extends \Codeception\Test\Unit
{
    private $userId = null;
    private $password = null;
    private $user;

    public function testCreateUser()
    {
        $this->password = \app\helpers\Security::generatePassword();

        $data = [
            'firstname' => 'unit user',
            'lastname' => 'unit user',
            'email' =>  Yii::$app->security->generateRandomString(8) .  '@user.com',
            'password' => $this->password
        ];

        $model = new \app\models\User();

        $model->load($data, '');

        $validateResult = $model->validate();

        $this->assertTrue($validateResult);
        $this->assertFalse($model->hasErrors());

        $saveResult = $model->save();

        $this->assertTrue($saveResult);
        $this->assertFalse($model->hasErrors());
        $this->assertTrue(is_numeric($model->id) && $model->id > 0);

        $this->userId = $model->id;
        $this->user = $model;
    }
}
