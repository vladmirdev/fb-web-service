<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class ActionCest
{
    public $actId = null;
    public $categoryId = null;
    public $relationId = null;

    public static $ids = [
        'existing' => 1,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test action index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/actions');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing action');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/actions/%d', self::$ids['existing']));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing action');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/actions/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkCreateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new action');

        $data = [
            'name' => 'Test action',
            'color' => '#000000'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/actions', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->actId = $response['id'];

    }

    public function checkCreateActionWithCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new action with category');

        $data = [
            'name' => 'Test action',
            'color' => '#000000',
            'categories' => [
                [
                    'name' => 'Some action category'
                ]
            ]
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/action/import', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->actId = $response['id'];

    }

    public function checkCreateActionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new action category');

        $data = [
            'name' => 'Action category',
            'type' => Action::ITEM_TYPE,
            'color' => '#ffffff'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendActionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Append action category');

        $data = [
            'category_id' => $this->categoryId,
            'action_id' => $this->actId
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/action-categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->relationId = $response['id'];
    }

    public function checkRemoveActionCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Remove action category');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendDELETE(sprintf('/v1/action-categories/%d', $this->relationId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Update existing action');

        $data = [
            'name' => 'Test action (updated)',
            'color' => '#ffffff'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/actions/%d', $this->actId), $data);

        $I->seeResponseContainsJson(['id' => $this->actId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkDeleteAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete existing action');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/actions/%d', $this->actId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get deleted action');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/actions/%d', $this->actId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
