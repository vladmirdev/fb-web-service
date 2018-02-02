<?php

namespace app\tests\api\v1;

use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class FeedbackCest
{
    public $messageId;

    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Test feedback index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/feedback');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkCreateFeedback(ApiTester $I)
    {
        $I->amBearerAuthenticated(Fixtures::get('token'));

        $I->wantTo('Create new message');

        $data = [
            'type' => 'Bug',
            'from' => 'reporter@example.com',
            'subject' => 'This is a bug report title',
            'content' => 'This is a bug report content',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/feedbacks', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->messageId = $response['id'];

    }
}
