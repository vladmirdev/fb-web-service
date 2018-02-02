<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 27.10.17
 * Time: 17:00
 */

namespace app\modules\v1\controllers;

use yii\base\Exception;

class ErrorController extends BaseController
{
    public $modelClass = 'none';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = array_merge(parent::guestActions(), ['index']);

        $behaviors['authenticator']['except'] = $guestActions;
        $behaviors['access']['except'] = $guestActions;

        return $behaviors;
    }

    /**
     * Show error page
     *
     * @return bool|\Exception
     * @throws \yii\base\ExitException
     */
    public function actionIndex()
    {
        /** @var Exception $exception */
        $exception = \Yii::$app->errorHandler->exception;

        if(\Yii::$app->request->isOptions)
            return $this->actionOptions();

        if ($exception !== null) {
            return $exception;
        }
    }

    /**
     * Parse OPTIONS
     * @throws \yii\base\ExitException
     */
    public function actionOptions()
    {
        \Yii::$app->getResponse()->setStatusCode(200);

        \Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST, GET, PUT, DELETE');
        \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE');

        \Yii::$app->end();
    }
}