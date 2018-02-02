<?php

namespace app\tests\api\v1;

use app\tests\ApiTester;
use Codeception\Util\HttpCode;

class ReferenceCest
{
    public $refId = null;

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test references index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/references');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateReference(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new reference');

        $data = [
            'content' => 'Test reference',
            'web_url' => 'http://some-url.com',

        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/references', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->refId = $response['id'];

    }

    public function checkUpdateReference(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Update existing reference');

        $data = [
            'content' => 'Test reference (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/references/%d', $this->refId), $data);

        $I->seeResponseContainsJson(['id' => $this->refId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get existing reference');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/references/%d', $this->refId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing reference');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/references/%d', $this->refId + 1));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteReference(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete existing reference');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/references/%d', $this->refId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedReference(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get deleted reference');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/references/%d', $this->refId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
