<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class NatureCest
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

        $I->wantTo('Test natures index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/natures');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new nature');

        $data = [
            'name' => 'Test nature',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/natures', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->cId = $response['id'];

    }

    public function checkUpdateNature(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing nature');

        $data = [
            'name' => 'Test nature (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/natures/%d', $this->cId), $data);

        $I->seeResponseContainsJson(['id' => $this->cId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewNature(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing nature');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/natures/%d', $this->cId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing nature');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/natures/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteNature(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing nature');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/natures/%d', $this->cId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedNature(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted nature');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/natures/%d', $this->cId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
