<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Preparation;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class PreparationCest
{
    public $sId = null;
    public $rId = null;
    public $categoryId = null;

    public static $ids = [
        'existing' => 1,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test preparations index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/preparations');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkSearchAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test preparations search');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/preparations/search');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreatePreparation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new preparation');

        $data = [
            'name' => 'Test preparation',
            'type' => 'formula',
            'alternate_name' => 'Test alternate name',
            'method' => 'Some method description'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/preparations', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->sId = $response['id'];

    }

    public function checkCreatePreparationCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new preparation category');

        $data = [
            'name' => 'Preparation category',
            'type' => Preparation::ITEM_TYPE,
            'color' => '#000000'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendPreparationCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Append preparation category');

        $data = [
            'category_id' => $this->categoryId,
            'prep_id' => $this->sId
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/preparation-categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->rId = $response['id'];
    }

    public function checkRemovePreparationCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Remove preparation category');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendDELETE(sprintf('/v1/preparation-categories/%d', $this->rId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdatePreparation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing preparation');

        $data = [
            'name' => 'Test preparation (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/preparations/%d', $this->sId), $data);

        $I->seeResponseContainsJson(['id' => $this->sId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewPreparation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing preparation');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/preparations/%d', $this->sId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/preparations/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeletePreparation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing preparation');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/preparations/%d', $this->sId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedPreparation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted preparation');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/preparations/%d', $this->sId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
