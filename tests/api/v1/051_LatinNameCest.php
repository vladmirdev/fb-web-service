<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class LatinNameCest
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

        $I->wantTo('Test latin names index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/latin-names');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new latin name');

        $data = [
            'name' => 'Test latin name',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/latin-names', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->cId = $response['id'];

    }

    public function checkUpdateLatinName(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing latin name');

        $data = [
            'name' => 'Test latin name (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/latin-names/%d', $this->cId), $data);

        $I->seeResponseContainsJson(['id' => $this->cId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewLatinName(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing latin name');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/latin-names/%d', $this->cId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing latin name');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/latin-names/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteLatinName(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing latin name');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/latin-names/%d', $this->cId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedLatinName(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted latin name');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/latin-names/%d', $this->cId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
