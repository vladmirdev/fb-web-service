<?php

namespace app\tests\api\v1;

use app\tests\ApiTester;
use Codeception\Util\HttpCode;

class ActivityCest
{
    public function checkIndexAction(ApiTester $I)
    {
        $I->wantTo('Test activity index');

        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/activity');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeHttpHeader('X-Pagination-Total-Count');
    }

    public function checkSearchAction(ApiTester $I)
    {
        $I->wantTo('Test activity search');

        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/activity/search');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeHttpHeader('X-Pagination-Total-Count');
    }
}
