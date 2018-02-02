<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\modules\v1\models\Formula;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class FormulasCest
{
    public $formulaIdPost = null;
    public $formulaIdJson = null;
    public $categoryId = null;
    public $relationId = null;

    public static $ids = [
        'existing' => 3,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test formulas index');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/formulas');

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing formula');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/formulas/%d', self::$ids['existing']));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing formula');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/formulas/%d', self::$ids['notExisting']));
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkCreateFormulaPost(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new formula (post request)');

        $data = [
            'english_name' => 'English name',
            'pinyin' => 'Some Pinyin Formula Name',
            'pinyin_ton' => 'Some Pinyin Formula Name',
            'simplified_chinese' => 'S Chinese',
            'traditional_chinese' => 'T Chinese'
        ];

        $I->sendPOST('/v1/formulas', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->formulaIdPost = $response['id'];

    }

    public function checkCreateFormulaValidation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new formula with empty pinyin');

        $data = [
            'english_name' => 'English name',
            'name' => 'Pinyin',
            'pinyin_ton' => 'Pinyin Ton',
            'pinyin_code' => 'Pinyin Code',
            'simplified_chinese' => 'S Chinese',
            'traditional_chinese' => 'T Chinese'
        ];

        $I->sendPOST('/v1/formulas', $data);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['code' => Errors::FORMULA_CREATION_ERROR]);
    }

    public function checkCreateFormulaJson(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new formula (json request)');

        $data = [
            'english_name' => 'English name',
            'pinyin' => 'Some Other Pinyin Formula Updated',
            'pinyin_ton' => 'Some Other Pinyin Formula Updated',
            'simplified_chinese' => 'S Chinese',
            'traditional_chinese' => 'T Chinese'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/formulas', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->formulaIdJson = $response['id'];

    }

    public function checkAddFormulaToFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Add formula to favorites');

        $data = [
            'formula_id' => $this->formulaIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/favorites', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson(['formula_id' => $this->formulaIdJson]);
    }

    public function checkFormulaInFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Check formula in favorites');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/v1/formulas/%d', $this->formulaIdJson));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['is_favorite' => 1]);
    }

    public function checkRemoveFormulaFromFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Remove formuls to favorites');

        $data = [
            'formula_id' => $this->formulaIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/favorites/remove', $data);

        $I->seeResponseContainsJson(['formula_id' => $this->formulaIdJson]);
    }

    public function checkCreateFormulaCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('[admin] Create new formula category');

        $data = [
            'name' => 'Formula category',
            'type' => Formula::ITEM_TYPE,
            'color' => '#000000'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendFormulaCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('[user] Append formula category');

        $data = [
            'category_id' => $this->categoryId,
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(sprintf('/v1/formulas/%d/categories', $this->formulaIdJson), $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->relationId = $response['id'];
    }

    public function checkRemoveFormulaCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Remove formula category');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendDELETE(sprintf('/v1/formula-categories/%d', $this->relationId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkCreateFormulaNote(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new formula note');

        $data = [
            'title' => 'This is a formula note title',
            'content' => 'Some note content',
            'formula_id' => $this->formulaIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/notes', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);
    }

    public function checkUpdateFormulaPost(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing formula (post request)');

        $data = [
            'name' => 'Test formula (post updated)',
        ];

        $I->sendPUT(sprintf('/v1/formulas/%d', $this->formulaIdPost), $data);

        $I->seeResponseContainsJson(['id' => $this->formulaIdPost]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateFormulaJson(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing formula (json request)');

        $data = [
            'pinyin' => 'Test Formula Json Updated',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/formulas/%d', $this->formulaIdJson), $data);

        $I->seeResponseContainsJson(['id' => $this->formulaIdJson]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateFormulaValidation(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing formula with empty pinyin (json request)');

        $data = [
            'pinyin' => '',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/formulas/%d', $this->formulaIdJson), $data);

        $I->seeResponseContainsJson(['code' => Errors::FORMULA_UPDATING_ERROR]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetFormulaActivities(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get formula activities');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/formulas/%d/activities', $this->formulaIdPost));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetFormulaCategories(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get formula categories');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/formulas/%d/categories', $this->formulaIdJson));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkDeleteFormula(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing formulas');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/formulas/%d', $this->formulaIdPost));

        $I->seeResponseContainsJson(['is_deleted' => 1]);

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/formulas/%d', $this->formulaIdJson));

        $I->seeResponseContainsJson(['is_deleted' => 1]);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedFormula(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted formula');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/formulas/%d', $this->formulaIdPost));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
