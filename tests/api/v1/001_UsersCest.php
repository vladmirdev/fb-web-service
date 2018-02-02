<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\constants\Http;
use app\models\DevicePlatform;
use app\models\User;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;
use yii\test\Fixture;

class UsersCest
{
    private $userId = null;
    private $password = null;
    private $newPassword = null;
    private $token = null;
    private $uuid = null;

    public function checkGetListUsers(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get users list');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/users');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateUser(ApiTester $I)
    {
        $I->wantTo('Create new user');

        $this->password = \Yii::$app->security->generateRandomString(8);
        $this->uuid = uniqid();

        $data = [

            // User information

            'firstname' => 'api user',
            'lastname' => 'api user',
            'email' => 'api-user@user.com',
            'password' => $this->password,

            // Device information

            'device_uid' => $this->uuid,
            'device_name' => 'My awesome device',
            'device_vendor' => 'NNM',
            'device_model' => 'Note X',
            'device_platform' => DevicePlatform::PLATFORM_ANDROID,
            'os_version' => '7.0.0',
            'app_version' => '0.0.4'

        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users', $data);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['user_id']))
            $this->userId = $response['user_id'];

        Fixtures::add('user_id', $this->userId);
        Fixtures::add('device', $this->uuid);

        $I->seeResponseCodeIs(HttpCode::CREATED);
    }

    public function checkCreateUserWithNotUniqueEmail(ApiTester $I)
    {
        $I->wantTo('Create new user with not unique email');

        $password = \Yii::$app->security->generateRandomString(8);

        $data = [

            // User information

            'firstname' => 'api user',
            'lastname' => 'api user',
            'email' => 'api-user@user.com',
            'password' => $password

        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users', $data);

        $I->seeResponseContainsJson(['code' => Errors::USER_NOT_UNIQUE_EMAIL]);
    }

    public function checkGetExistingUser(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get existing user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/users/%d', $this->userId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetNotExistingUser(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get not existing user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/users/%d', $this->userId + 1));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson(['code' => Errors::USER_NOT_EXISTS]);
    }

    public function checkCreateUserWithEmptyEmail(ApiTester $I)
    {
        $I->wantTo('Create new user with empty email');

        $data = [
            'firstname' => 'api user empty',
            'lastname' => 'api user empty',
            'email' => '',
            'password' => \Yii::$app->security->generateRandomString(8)
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users', $data);

        $I->seeResponseContainsJson(['code' => Errors::USER_EMPTY_EMAIL]);
    }

    public function checkCreateUserWithInvalidEmail(ApiTester $I)
    {
        $I->wantTo('Create new user with invalid email');

        $data = [
            'firstname' => 'api user empty',
            'lastname' => 'api user empty',
            'email' => 'this-is-not-email',
            'password' => \Yii::$app->security->generateRandomString(8)
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users', $data);

        $I->seeResponseContainsJson(['code' => Errors::USER_INVALID_EMAIL]);
    }

    public function checkCreateUserWithEmptyPassword(ApiTester $I)
    {
        $I->wantTo('Create new user with empty password');

        $data = [
            'firstname' => 'api user empty',
            'lastname' => 'api user empty',
            'email' => 'api-user-empty@user.com',
            'password' => ''
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users', $data);

        $I->seeResponseContainsJson(['code' => Errors::USER_EMPTY_PASSWORD]);
    }

    public function checkLoginSuccess(ApiTester $I)
    {
        $I->wantTo('Login with correct credentials');

        $data = [
            'email' => 'api-user@user.com',
            'password' => $this->password
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/login', $data);

        $I->seeResponseContainsJson(['user_id' => $this->userId]);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && is_array($response) && isset($response['access_token'])) {

            $this->token = $response['access_token'];
            $this->userId = $response['user_id'];

            Fixtures::add('token', $this->token);
        }
    }

    public function checkChangePassword(ApiTester $I)
    {
        $I->amBearerAuthenticated($this->token);

        $I->wantTo('Change user password');

        $this->newPassword = \Yii::$app->security->generateRandomString(8);

        $payload = [
            'new_password' => $this->newPassword,
            'new_password2' => $this->newPassword
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/changepassword', $payload);

        $I->seeResponseContainsJson(['message' => 'Password changed successfully']);
    }

    public function checkLoginError(ApiTester $I)
    {
        $I->wantTo('Login with incorrect credentials');

        $data = [
            'email' => 'api-user@user.com',
            'password' => $this->password
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/login', $data);

        $I->seeResponseContainsJson(['code' => Errors::USER_INVALID_CREDENTIALS]);
    }

    /*

    public function checkLogout(ApiTester $I)
    {
        $I->amBearerAuthenticated($this->token);

        $I->wantTo('Logout current user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/logout');

        $I->seeResponseContainsJson(['message' => 'Logout successfully']);
    }

    public function checkExpiredToken(ApiTester $I)
    {
        $I->amBearerAuthenticated($this->token);

        $I->wantTo('Login with expired token');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/users/%d', $this->userId));

        //$I->seeResponseContainsJson(['code' => Errors::USER_INVALID_CREDENTIALS]);
        $I->seeResponseCodeIs(Http::UNAUTHORIZED);
    }

    public function checkDeleteUser(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete existing user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/users/%d', $this->userId));

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkLoginByDeletedUser(ApiTester $I)
    {
        $I->wantTo('Login by deleted user');

        $data = [
            'email' => 'api-user@user.com',
            'password' => $this->newPassword
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/login', $data);

        $I->seeResponseCodeIs(Http::NOT_FOUND);
    }

    */

    /**
     * @example ["/v1/", 404]
     * @example ["/v1/users", 401]
     */
    /*
    public function checkEndpoints(ApiTester $I, \Codeception\Example $example)
    {
        $I->sendGET($example[0]);
        $I->seeResponseCodeIs($example[1]);
    } */
}
