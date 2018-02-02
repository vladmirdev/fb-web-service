<?php

namespace app\tests\api\v1;

use app\modules\v1\models\Herb;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class HerbsCest
{
    public $herbIdPost = null;
    public $herbIdJson = null;
    public $categoryId = null;
    public $relationId = null;

    public static $ids = [
        'existing' => 1,
        'notExisting' => 1000000
    ];

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test herbs index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/herbs');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing herb');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/herbs/%d', self::$ids['existing']));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing herb');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/herbs/%d', self::$ids['notExisting']));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkCreateHerbPost(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new herb (post request)');

        $data = [
            'english_name' => 'English name',
            'latin_name' => 'Latin',
            'pinyin' => 'Pinyin',
            'pinyin_ton' => 'Pinyin Ton',
            'simplified_chinese' => 'S Chinese',
            'traditional_chinese' => 'T Chinese'
        ];

        $I->sendPOST('/v1/herbs', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->herbIdPost = $response['id'];
    }

    public function checkCreateHerbJson(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new herb (json request)');

        $data = [
            'english_name' => 'English name',
            'latin_name' => 'Latin',
            'pinyin' => 'Pinyin Json',
            'pinyin_ton' => 'Pinyin Ton Json',
            'simplified_chinese' => 'S Chinese',
            'traditional_chinese' => 'T Chinese'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/herbs', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->herbIdJson = $response['id'];
    }

    public function checkAddHerbToFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Add herb to favorites');

        $data = [
            'herb_id' => $this->herbIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/favorites', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson(['herb_id' => $this->herbIdJson]);
    }

    public function checkHerbInFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Check herb in favorites');

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendGET(sprintf('/v1/herbs/%d', $this->herbIdJson));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['is_favorite' => 1]);
    }

    public function checkRemoveHerbFromFavorites(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Remove herb to favorites');

        $data = [
            'herb_id' => $this->herbIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/favorites/remove', $data);

        $I->seeResponseContainsJson(['herb_id' => $this->herbIdJson]);
    }

    public function checkCreateHerbCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('[admin] Create new herb category');

        $data = [
            'name' => 'Herb category',
            'type' => Herb::ITEM_TYPE,
            'color' => '#f0f0f0'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/categories', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->categoryId = $response['id'];

    }

    public function checkAppendHerbCategory(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('[user] Append herb category');

        $data = [
            'category_id' => $this->categoryId,
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(sprintf('/v1/herbs/%d/categories', $this->herbIdJson), $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);
    }

    public function checkCreateHerbNote(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new herb note');

        $data = [
            'title' => 'This is a herb note title',
            'content' => 'Some note content',
            'herb_id' => $this->herbIdJson
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/notes', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);
    }

    public function checkUpdateHerbPost(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing herb (post request)');

        $data = [
            'name' => 'Test Herb Post Updated',
        ];

        $I->sendPUT(sprintf('/v1/herbs/%d', $this->herbIdPost), $data);

        $I->seeResponseContainsJson(['id' => $this->herbIdPost]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkUpdateHerbJson(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing herb (json request)');

        $data = [
            'pinyin' => 'Test Herb Json Updated',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/herbs/%d', $this->herbIdJson), $data);

        $I->seeResponseContainsJson(['id' => $this->herbIdJson]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetHerbActivities(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get herb activities');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/herbs/%d/activities', $this->herbIdPost));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetHerbCategories(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get herb categories');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/herbs/%d/categories', $this->herbIdJson));

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkDeleteHerb(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing herbs');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/herbs/%d', $this->herbIdPost));

        $I->seeResponseContainsJson(['is_deleted' => 1]);

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/herbs/%d', $this->herbIdJson));

        $I->seeResponseContainsJson(['is_deleted' => 1]);

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedHerb(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted herb');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/herbs/%d', $this->herbIdPost));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
