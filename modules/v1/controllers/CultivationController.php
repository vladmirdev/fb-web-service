<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbCultivation;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Cultivation;

class CultivationController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Cultivation';

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
     * Get list cultivations
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Cultivation::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View cultivation
     *
     * @param $id
     *
     * @return Cultivation|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadCultivation($id);
    }

    /**
     * Create new cultivation
     *
     * @return Cultivation|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = $this->loadCultivation(null, Actions::CREATE);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Cultivation::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);

    }

    /**
     * Update existing cultivation
     *
     * @param $id
     *
     * @return Cultivation|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadCultivation($id, Actions::UPDATE);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Cultivation::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing cultivation
     *
     * @param $id
     *
     * @return Cultivation|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadCultivation($id, Actions::DELETE);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbCultivation $relations */
            $relations = HerbCultivation::findAll(['cultivation_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbCultivation $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Cultivation %s has been removed', $model->name));
            }

            Activity::store(Cultivation::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

       return $this->sendValidationResult($model);
    }

    /**
     * Load cultivation model
     *
     * @param $id
     * @param string $action
     *
     * @return Cultivation
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadCultivation($id, $action = Actions::VIEW)
    {
        if($action == Actions::CREATE)
            return new Cultivation();

        $model = Cultivation::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Cultivation not found', Errors::CULTIVATION_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Cultivation updating is forbidden', Errors::CULTIVATION_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Cultivation viewing is forbidden', Errors::CULTIVATION_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}