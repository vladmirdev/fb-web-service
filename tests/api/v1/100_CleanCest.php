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

class CleanCest
{
    public function checkLogout(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Logout current user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/users/logout');

        $I->seeResponseContainsJson(['message' => 'Logout successfully']);
    }

    public function checkDeleteUserData(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete user data');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/users/%d/data', Fixtures::get('user_id')));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['message' => 'Data deletion job pushed to queue']);

    }

    public function checkDeleteUser(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete existing user');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/users/%d', Fixtures::get('user_id')));

        $I->seeResponseCodeIs(HttpCode::OK);

    }
}
