<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\helpers\Security;
use app\models\Activity;
use app\models\forms\ChannelForm;
use app\models\User;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbChannel;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Channel;

class ChannelController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Channel';
    public $smartApi = true;

    use Filtered;

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
                'actions' => ['index', 'view', 'create', 'update', 'delete'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * List channels
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Channel::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View channel
     *
     * @param $id
     *
     * @return Channel|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
       return $this->loadChannel($id);
    }

    /**
     * Create new channel
     *
     * @return Channel|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        if($this->smartApi)
            return $this->actionImport();

        $channel = $this->loadChannel(null, Actions::CREATE);

        if($channel->load(\Yii::$app->request->getBodyParams(), '') && $channel->save()) {

            Activity::store(Channel::ITEM_TYPE, $channel->id, Actions::CREATE);

            $channel->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $channel;
        }

        return $this->sendValidationResult($channel);
    }

    /**
     * Batch channels import
     *
     * @param integer|null $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionImport($id = null)
    {
        $form = new ChannelForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                Activity::store(Channel::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                Activity::store(Channel::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Channel::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::CHANNEL_CREATION_ERROR : Errors::CHANNEL_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update channel
     *
     * @param $id
     *
     * @return Channel|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        if($this->smartApi)
            return $this->actionImport($id);

        $channel = $this->loadChannel($id, Actions::UPDATE);

        if($channel->load(\Yii::$app->request->getBodyParams(), '') && $channel->save()) {

            Activity::store(Channel::ITEM_TYPE, $channel->id, Actions::UPDATE);

            $channel->refresh();

            return $channel;
        }

        return $this->sendValidationResult($channel);
    }

    /**
     * Delete channel
     *
     * @param $id
     *
     * @return Channel|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadChannel($id, Actions::DELETE);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbChannel $relations */
            $relations = HerbChannel::findAll(['channel_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbChannel $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Channel %s has been removed', $model->english_name));
            }

            Activity::store(Channel::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load channel model
     *
     * @param $id
     * @param string $action
     *
     * @return Channel
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadChannel($id, $action = Actions::VIEW)
    {
        if($action == Actions::CREATE)
            return new Channel();

        $model = Channel::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Channel not found', Errors::CHANNEL_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !Security::isAdmin())
                throw new ForbiddenHttpException('Channel updating is forbidden', Errors::CHANNEL_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !Security::isAdmin())
                throw new ForbiddenHttpException('Channel viewing is forbidden', Errors::CHANNEL_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}
