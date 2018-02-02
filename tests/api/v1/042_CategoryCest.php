<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\modules\v1\models\Formula;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class CategoryCest
{
    public $catId = null;

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/categories');

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCreateFormulaCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new category');

        $data = [
            'name' => 'Test category',
            'type' => Formula::ITEM_TYPE,
            'color' => '#eeeeee'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->catId = $response['id'];

    }

    public function checkCreateUnknownCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new category with unknown type');

        $data = [
            'name' => 'Test category',
            'type' => 'unknown',
            'color' => '#eeeeee'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['code' => Errors::CATEGORY_CREATION_ERROR]);
    }

    public function checkUpdateFormulaCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing category');

        $data = [
            'name' => 'Test category (updated)',
            'type' => 'herb',
            'color' => '#111111'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/categories/%d', $this->catId), $data);

        $I->seeResponseContainsJson(['id' => $this->catId]);
        $I->seeResponseContainsJson(['name' => $data['name']]);
        $I->seeResponseContainsJson(['type' => $data['type']]);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing category');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d', $this->catId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing category');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d', $this->catId + 1));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkCategoryFormulas(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category formulas');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/formulas', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCategoryHerbs(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category herbs');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/herbs', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCategoryActions(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category actions');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/actions', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCategoryPreparations(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category preparations');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/preparations', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCategorySymptoms(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category symptoms');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/symptoms', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCategoryCautions(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test category cautions');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d/cautions', $this->catId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkDeleteCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing category');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/categories/%d', $this->catId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted category');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/categories/%d', $this->catId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
