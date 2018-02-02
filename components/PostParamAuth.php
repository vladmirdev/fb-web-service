<?php

namespace app\components;
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 09.09.17
 * Time: 12:42
 * @see QueryParamAuth
 */

use yii\filters\auth\QueryParamAuth;

/**
 * PostParamAuth is an action filter that supports the authentication based on the access token passed through a POST parameter.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PostParamAuth extends \yii\filters\auth\AuthMethod
{
    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'access_token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->post($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }
}