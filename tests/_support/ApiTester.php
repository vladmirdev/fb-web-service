<?php

namespace app\tests;

use app\models\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /** @var User $user */
    public $user = null;

    /** @var string $token */
    public $token = null;

    public $password = 'password1';
    public $username = 'api-test@test.com';

    public static $tokens = [
        'admin' => 's_VpIYj8IoNT5esK341R7m3TxTnPPMAQ',
        'guest' => '',
        'user' => ''
    ];

    /**
     * Register new account with default roles
     *
     * @param string|null $username
     * @param string|null $password
     */
    public function wantToCreateNewAccount($username = null, $password = null)
    {
        if ($this->user === null) {

            $data = [
                'firstname' => 'api test user',
                'lastname' => 'api test user',
                'email' => $username ? $username : $this->username,
                'password' => $password ? $password : $this->password
            ];

            $this->sendPOST('/v1/users?test=1', $data);

            $response = (array) \GuzzleHttp\json_decode($this->grabResponse());

            if($response && isset($response['user_id'])) {
                $this->user = User::findOne($response['user_id']);
                $this->token = $response['access_token'];
            }

        }
    }

    /**
     * Login by username and password
     *
     * @param string|null $username
     * @param string|null $password
     */
    public function wantToLogin($username = null, $password = null)
    {
        if($this->user && $this->token)
            return;

        $data = [
            'email' => $username ? $username : $this->username,
            'password' => $password ? $password : $this->password
        ];

        $this->sendPOST('/v1/users/login', $data);

        $response = (array) \GuzzleHttp\json_decode($this->grabResponse());

        if($response && is_array($response) && isset($response['access_token'])) {
            $this->user = User::findOne($response['user_id']);
            $this->token = $response['access_token'];
        } else {
            $this->wantToCreateNewAccount();
            $this->wantToLogin();
        }
    }

    /**
     * Logout
     */
    public function wantToLogout()
    {
        $data = [
            'access_token' => $this->token
        ];

        $this->sendPOST('/v1/users/logout', $data);
    }
}
