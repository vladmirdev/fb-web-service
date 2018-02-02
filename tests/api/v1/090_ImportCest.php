<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\modules\v1\models\Formula;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class ImportCest
{
    public $formulaId = null;
    public $herbId = null;

    public $formulaCategoryId = null;
    public $herbCategoryId = null;

    public function checkCreateImportFormula(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new complex formula');

        $data = [
            'english_name' => 'Complex formula',
            'pinyin' => 'This Is A Complex Formula',
            'pinyin_ton' => 'This Is A Complex Formula',
            'simplified_chinese' => 'CS Chinese',
            'traditional_chinese' => 'CT Chinese',
            'categories' => [
                [
                    'name' => 'Complex formula category'
                ]
            ],
            'notes' => [
                [
                    'title' => 'Formula note',
                    'content' => 'Note content'
                ]
            ]
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/formula/import', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->formulaId = $response['id'];

    }

    public function checkCreateImportHerb(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new complex herb');

        $data = [
            'english_name' => 'Complex herb',
            'pinyin' => 'This Is A Complex Herb',
            'pinyin_ton' => 'This Is A Complex Herb',
            'simplified_chinese' => 'CS Chinese',
            'traditional_chinese' => 'CT Chinese',
            'categories' => [
                [
                    'name' => 'Complex herb category'
                ]
            ],
            'notes' => [
                [
                    'title' => 'Herb note',
                    'content' => 'Note content'
                ]
            ]
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/v1/herb/import', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->formulaId = $response['id'];

    }
}
