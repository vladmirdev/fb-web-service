<?php

namespace app\tests\api\v1;

use app\constants\Errors;
use app\models\DevicePlatform;
use app\tests\ApiTester;
use Codeception\Util\Fixtures;
use Codeception\Util\HttpCode;

class DeviceCest
{
    public $deviceId = null;


    public function checkIndexAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test devices index');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/devices');

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkPlatformsAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Test devices platforms');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/device/platforms');

        $I->seeResponseCodeIs(HttpCode::OK);

    }

    public function checkDeviceValidation(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new device without uuid');

        $data = [
            'name' => 'My awesome device',
            'vendor' => 'Google',
            'model' => 'Pixel 2',
            'os_version' => '7.0',
            'platform_id' => DevicePlatform::PLATFORM_ANDROID,
            'app_version' => '0.0.3'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/devices', $data);

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseContainsJson(['code' => Errors::MODEL_VALIDATION_ERROR]);
        $I->seeResponseContainsJson(['message' => 'Uid cannot be blank.']);
    }

    public function checkCreateDevice(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Create new device');

        $data = [
            'uid' => uniqid(),
            'name' => 'My awesome device',
            'vendor' => 'Google',
            'model' => 'Pixel 2',
            'os_version' => '7.0',
            'platform_id' => DevicePlatform::PLATFORM_ANDROID,
            'app_version' => '0.0.3'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/v1/devices', $data);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $response = (array) \GuzzleHttp\json_decode($I->grabResponse());

        if($response && isset($response['id']))
            $this->deviceId = $response['id'];

    }

    public function checkUpdateDevice(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Update existing device');

        $data = [
            'name' => 'My fantastic device',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT(sprintf('/v1/devices/%d', $this->deviceId), $data);

        $I->seeResponseContainsJson(['id' => $this->deviceId]);
        $I->seeResponseContainsJson(['name' => $data['name']]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function checkViewAction(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get existing device');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/devices/%d', $this->deviceId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->wantTo('Get not existing device');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/devices/%d', $this->deviceId + 1));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function checkDeleteDevice(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Delete existing device');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE(sprintf('/v1/devices/%d', $this->deviceId));

        $I->seeResponseContainsJson(['is_deleted' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }


    public function checkGetDeletedDevice(ApiTester $I)
    {
        $I->amBearerAuthenticated(ApiTester::$tokens['admin']);

        $I->wantTo('Get deleted device');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET(sprintf('/v1/devices/%d', $this->deviceId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
