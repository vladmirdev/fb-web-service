<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbFlavour;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Flavour;

class FlavourController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Flavour';

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
     * Get flavours list
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Flavour::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View flavour
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionView($id)
    {
        return $this->loadFlavour($id);
    }

    /**
     * Create new flavour
     *
     * @return Flavour|array
     */
    public function actionCreate()
    {
        $model = new Flavour();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Flavour::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing flavour
     *
     * @param $id
     *
     * @return Flavour|array
     */
    public function actionUpdate($id)
    {
        $model = $this->loadFlavour($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Flavour::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing flavour
     *
     * @param $id
     *
     * @return Flavour|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadFlavour($id);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbFlavour $relations */
            $relations = HerbFlavour::findAll(['flavour_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbFlavour $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Flavour %s has been removed', $model->name));
            }

            Activity::store(Flavour::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load flavour model
     *
     * @param $id
     *
     * @return Flavour
     * @throws NotFoundHttpException
     */
    private function loadFlavour($id)
    {
        $model = Flavour::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Flavour not found', Errors::FLAVOUR_NOT_FOUND);
        }

        return $model;
    }
}
