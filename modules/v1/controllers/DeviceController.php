<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\Device;
use app\models\DevicePlatform;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DeviceController extends BaseController
{
    public $modelClass = 'api\models\Device';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = parent::guestActions();

        $behaviors['authenticator']['except'] = $guestActions;
        $behaviors['access']['except'] = $guestActions;

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'platforms'],
                'roles' => ['@']
            ]
        ];

        return $behaviors;
    }

    /**
     * Show devices index
     *
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $conditions['created_by'] = [\Yii::$app->user->getId()];

        if(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
            unset($conditions['created_by']);

        $activeData = new ActiveDataProvider([
            'query' => Device::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new book
     *
     * @return Device|array
     */
    public function actionCreate()
    {
        $device = $this->loadDevice(null, Actions::CREATE);

        $device->load(\Yii::$app->request->getBodyParams(), '');

        $device->created_by = \Yii::$app->user->getId();

        if($device->save()) {

            Activity::store(Device::ITEM_TYPE, $device->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $device->refresh();

            return $device;
        }

        return $this->sendValidationResult($device);
    }

    /**
     * Update existing device
     *
     * @param $id
     *
     * @return Device|array
     */
    public function actionUpdate($id)
    {
        $device = $this->loadDevice($id, Actions::UPDATE);

        $device->load(\Yii::$app->request->getBodyParams(), '');

        $device->modified_by = \Yii::$app->user->getId();

        if($device->save()) {

            Activity::store(Device::ITEM_TYPE, $device->id, Actions::UPDATE);

            $device->refresh();

            return $device;
        }

        return $this->sendValidationResult($device);
    }

    /**
     * View book
     *
     * @param $id
     *
     * @return Device
     */
    public function actionView($id)
    {
        return $this->loadDevice($id);
    }

    /**
     * Delete device
     *
     * @param $id
     *
     * @return Device|array
     */
    public function actionDelete($id)
    {
        $device = $this->loadDevice($id, Actions::DELETE);

        $device->is_deleted = 1;
        $device->modified_by = \Yii::$app->user->getId();

        if($device->save()) {

            Activity::store(Device::ITEM_TYPE, $device->id, Actions::DELETE);

            $device->refresh();

            return $device;
        }

        return $this->sendValidationResult($device);
    }

    /**
     * Show devices platforms
     *
     * @return array|ActiveDataProvider
     */
    public function actionPlatforms()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => DevicePlatform::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Load device model
     *
     * @param $id
     *
     * @param string $action
     *
     * @return Device
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadDevice($id, $action = Actions::VIEW)
    {
        if($action == Actions::CREATE)
            return new Device();

        $model = Device::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Device not found', Errors::DEVICE_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Device updating is forbidden', Errors::DEVICE_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != 0 && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Device viewing is forbidden', Errors::DEVICE_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}
