<?php

namespace app\tests\api\v1;

use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class BookCest
{
    public $bookId = null;
    public $chapId = null;
    public $pageId = null;

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test books index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/books');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateBook(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new book');

        $data = [
            'english_name' => 'Test book',
            'author' => 'Test author',
            'chinese_author' => 'Chinese author',
            'year' => date('Y'),
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/books', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->bookId = $response['id'];

    }

    public function checkCreateBookChapter(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new book chapter');

        $data = [
            'book_id' => $this->bookId,
            'english_name' => 'English chapter name',
            'chinese_name' => 'Chinese chapter name',
            'english' => 'English chapter content',
            'chinese' => 'Chinese chapter content',
            'pinyin' => 'Pinyin chapter content'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/book-chapters', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->chapId = $response['id'];

    }

    public function checkUpdateBook(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Update existing book');

        $data = [
            'english_name' => 'Test book (updated)',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/books/%d', $this->bookId), $data);

        $I->seeResponseContainsJson(['id' => $this->bookId]);
        $I->seeResponseContainsJson(['english_name' => $data['english_name']]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing book');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/books/%d', $this->bookId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing book');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/books/%d', $this->bookId + 1));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkGetBookChapters(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get existing book chapter');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/books/%d/chapters', $this->bookId));
        $I->seeResponseCodeIs(HttpCode::OK);
    }


    public function checkDeleteBook(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Delete existing book');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/books/%d', $this->bookId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkGetDeletedBook(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Get deleted book');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/books/%d', $this->bookId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
