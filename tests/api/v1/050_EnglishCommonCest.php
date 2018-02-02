<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class EnglishCommonCest
{
    public $cId = null;
    public $rId = null;

    public static $ids = [
        'existing' => 1,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test english commons index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/english-commons');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new english common');

        $data = [
            'name' => 'Test english common',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/english-commons', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->cId = $response['id'];

    }

    public function checkUpdateEnglishCommon(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing english common');

        $data = [
            'name' => 'Test english common (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/english-commons/%d', $this->cId), $data);

        $I->seeResponseContainsJson(['id' => $this->cId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewEnglishCommon(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing english common');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/english-commons/%d', $this->cId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing english common');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/english-commons/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteEnglishCommon(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing english common');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/english-commons/%d', $this->cId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedEnglishCommon(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted english common');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/english-commons/%d', $this->cId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
