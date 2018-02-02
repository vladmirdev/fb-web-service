<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\helpers\Security;
use app\models\DevicePlatform;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class SyncCest
{
    public $token;

    public function checkInitAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test confirm synchronization without token');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $data = [
            'device_uid' => Fixtures::get('device'),
            'last_sync_time' => date('Y-m-d H:i:s', strtotime('-1 month')),
            'token' => ''
        ];

        $I->sendPOST('/v1/sync/pushlastsync', $data);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['is_confirmed' => 1]);
    }

    public function checkPullAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test request synchronization (pull)');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $data = [
            'device_uid' => Fixtures::get('device')
        ];

        $I->sendPOST('/v1/sync/pullchanges', $data);

        $I->seeResponseCodeIs(HttpCode::OK);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['meta']))
            $this->token = $response['meta']->token;
    }

    public function checkEmptyDevice(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test request synchronization without device UUID');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $data = [
            // empty response
        ];

        $I->sendPOST('/v1/sync/pullchanges', $data);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['code' => Errors::SYNC_UNKNOWN_DEVICE]);

    }

    public function checkWrongToken(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test confirm synchronization with wrong token');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $data = [
            'device_uid' => Fixtures::get('device'),
            'token' => Security::generateToken()
        ];

        $I->sendPOST('/v1/sync/pushlastsync', $data);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['code' => Errors::SYNC_UNKNOWN_TOKEN]);
    }

    public function checkPushAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test confirm synchronization (push)');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $data = [
            'device_uid' => Fixtures::get('device'),
            'token' => $this->token
        ];

        $I->sendPOST('/v1/sync/pushlastsync', $data);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['is_confirmed' => 1]);
    }
}
