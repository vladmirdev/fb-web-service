<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Symptom;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class SymptomsCest
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

        $I->wantTo('Test symptoms index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/symptoms');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateSymptom(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new symptom');

        $data = [
            'name' => 'Test symptom',
            'color' => '#111111'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/symptoms', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->sId = $response['id'];

    }

    public function checkCreateSymptomCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new symptom category');

        $data = [
            'name' => 'Symptom category',
            'type' => Symptom::ITEM_TYPE,
            'color' => '#000000'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendSymptomCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Append symptom category');

        $data = [
            'category_id' => $this->categoryId,
            'symptom_id' => $this->sId
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/symptom-categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->rId = $response['id'];
    }

    public function checkRemoveSymptomCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Remove symptom category');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendDELETE(sprintf('/v1/symptom-categories/%d', $this->rId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateSymptom(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing symptom');

        $data = [
            'name' => 'Test symptom (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/symptoms/%d', $this->sId), $data);

        $I->seeResponseContainsJson(['id' => $this->sId]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewSymptom(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing symptom');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/symptoms/%d', $this->sId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing caution');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/symptoms/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteSymptom(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing symptom');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/symptoms/%d', $this->sId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedSymptom(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted symptom');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/symptoms/%d', $this->sId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
