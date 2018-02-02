<?php

namespace app\tests;

use app\models\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class UserTester extends \Codeception\Actor
{
    /**
     * Access token
     * @var string
     */
    public $access_token = 's_VpIYj8IoNT5esK341R7m3TxTnPPMAQ';

    use _generated\ApiTesterActions;
}
