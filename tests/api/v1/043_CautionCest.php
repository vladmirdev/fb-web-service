<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class CautionCest
{
    public $cId = null;
    public $rId = null;
    public $categoryId = null;

    public static $ids = [
        'existing' => 1,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test cautions index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/cautions');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new caution');

        $data = [
            'name' => 'Test caution',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/cautions', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->cId = $response['id'];

    }

    public function checkCreateCautionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new caution category');

        $data = [
            'name' => 'Caution category',
            'type' => Caution::ITEM_TYPE,
            'color' => '#ffffff'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendCautionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Append caution category');

        $data = [
            'category_id' => $this->categoryId,
            'caution_id' => $this->cId
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/caution-categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->rId = $response['id'];
    }

    public function checkRemoveCautionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Remove caution category');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendDELETE(sprintf('/v1/caution-categories/%d', $this->rId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateCaution(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing caution');

        $data = [
            'name' => 'Test caution (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/cautions/%d', $this->cId), $data);

        $I->seeResponseContainsJson(['id' => $this->cId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewCaution(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/cautions/%d', $this->cId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/cautions/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteCaution(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/cautions/%d', $this->cId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedCaution(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/cautions/%d', $this->cId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
