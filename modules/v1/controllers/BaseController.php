<?php
namespace app\modules\v1\controllers;

use app\components\PostParamAuth;
use app\constants\Errors;
use app\constants\Roles;
use app\models\LoginHistory;
use app\models\User;
use app\constants\Http;
use app\modules\v1\models\Formula;
use app\modules\v1\models\Herb;
use Codeception\Util\HttpCode;
use Symfony\Component\DomCrawler\Form;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\models\Token;
use yii\web\ForbiddenHttpException;

class BaseController extends ActiveController
{
    public $user_id;
    public $isJsonRequest = false;

    private $user = false;

    /**
     * @var bool See details {@link \yii\web\Controller::$enableCsrfValidation}.
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if(\Yii::$app->request->getRawBody())
            $this->isJsonRequest = true;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        unset($actions['options']);

        return $actions;
    }

    public function guestActions()
    {
        return ['options'];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Origin' => ['*'],
                //'Access-Control-Allow-Methods' => ['*'],
                'Access-Control-Request-Method' => [
                    'POST'
                ],
                'Access-Control-Request-Headers' => [
                    'X-Pagination-Current-Page',
                    'Origin',
                    'X-Requested-With',
                    'Content-Type',
                    'Accept',
                    'Authorization'
                ],
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Current-Page',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Per-Page',
                    'X-Pagination-Total-Count'
                ],
                'Access-Control-Allow-Headers' => [
                    'Origin',
                    'X-Requested-With',
                    'Content-Type',
                    'Accept',
                    'Authorization'
                ],
                'Access-Control-Allow-Credentials' => true,
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBasicAuth::className(),
                HttpBearerAuth::className(),
                PostParamAuth::className(),
                [
                    'class' => QueryParamAuth::className(),
                    'tokenParam' => 'access_token'
                ]
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
        ];

        return $behaviors;
    }

    /**
     * Check access to perform action
     *
     * @param string $action
     * @param null $model
     * @param array $params
     *
     * @todo move implementation to RBAC
     * @see UserController
     * @deprecated
     *
     * @return array
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if(!empty($params) && !empty($params['access_token'])) {

            /** @var Token $token */
            $token = Token::findOne(['access_token' => $params['access_token'], 'is_deleted' => 0]);

            if(empty($token)) {
                return $this->sendResponse('Invalid access token', Errors::USER_WRONG_TOKEN);
            } elseif((time()-strtotime($token['created_time'])) > \Yii::$app->params['access_token_timeout']) {
                return $this->sendResponse('Session timeout, you need to login again', Errors::USER_SESSION_EXPIRED);
            } else {
                $this->user_id = $token['user_id'];
            }
        } else {
            //throw new ForbiddenHttpException('Access token param missing');
        }
    }

    /**
     * Login implementation
     *
     * @param null $user
     * @param null $timeout
     */
    public function login($user = null, $timeout = null)
    {
        if(!$timeout)
            $timeout = \Yii::$app->params['access_token_timeout'];

        \Yii::$app->user->login($user ? $user : $this->getUser(), $timeout);
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findOne($this->user_id);
        }

        return $this->user;
    }

    /**
     * Prepare and send JSON response with correct HTTP status code
     *
     * @param $message
     * @param null $code
     * @param int $status
     *
     * @return mixed
     */
    public function sendResponse($message, $code = null, $status = Http::OK)
    {
        $response = [];

        if($status !== null)
            \Yii::$app->response->statusCode = $status;

        if($code !== null)
            $response['code'] = $code;

        if(is_string($message))
            $response['message'] = $message;
        elseif(is_array($message))
            $response = array_merge($response, $message);

        return $response;
    }

    /**
     * Send message by code
     *
     * @param $code
     * @param int $status
     *
     * @return array
     */
    public function sendResponseByCode($code, $status = Http::OK)
    {
        $response = [];

        if($status !== null)
            \Yii::$app->response->statusCode = $status;

        if($code !== null)
            $response['code'] = $code;

        $response['message'] = Errors::getMessage($code);

        return $response;
    }

    /**
     * Prepare validation results
     *
     * @param ActiveRecord $model
     * @param bool $single
     * @param int $code
     *
     * @return array
     */
    public function sendValidationResult($model, $single = true, $code = Errors::MODEL_VALIDATION_ERROR)
    {

        $errors = $model->getFirstErrors();

        \Yii::$app->response->headers->add('Cache-control', 'private');

        if(sizeof($errors) == 0)
            return [];

        switch ($model::className()) {
            case Formula::className():
                if($model->isNewRecord)
                    $code = Errors::FORMULA_CREATION_ERROR;
                else
                    $code = Errors::FORMULA_UPDATING_ERROR;
                break;
            case Herb::className():
                if($model->isNewRecord)
                    $code = Errors::HERB_CREATION_ERROR;
                else
                    $code = Errors::HERB_UPDATING_ERROR;
                break;
        }

        if($single) {

            reset($errors);

            $field = key($errors);

            $error = $errors[$field];

            if(strpos($error, '|') !== false) {
                list($code, $message) = explode('|', $error);
                return $this->sendResponse($message, (int) $code);
            }

            return $this->sendResponse($error, (int) $code);
        }

        return $this->sendResponse(['message' => $errors], (int) $code);
    }

    /**
     * @inheritdoc
     * @return array
     */
    protected function verbs()
    {
        $verbs = parent::verbs();
        $verbs['index'][] = 'OPTIONS'; //just add the 'POST' to "GET" and "HEAD"
        return $verbs;
    }

    /**
     * Override OPTIONS method
     *
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionOptions()
    {
        if (\Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            \Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST, GET, PUT, DELETE');
            \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE');
            \Yii::$app->end();
        }

        return true;
    }
}
