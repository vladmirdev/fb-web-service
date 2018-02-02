<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaPreparation;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbPreparation;
use app\modules\v1\models\PreparationSearch;
use app\traits\Filtered;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Preparation;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

class PreparationController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Preparation';

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
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'search'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list preparations
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Preparation::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Search preparation methods
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new PreparationSearch();

        $searchModel->is_deleted = 0;

        $conditions = $this->prepareFilter();

        $params = array_merge(\Yii::$app->request->queryParams, $conditions);

        $dataProvider = $searchModel->search($params);

        return $dataProvider;
    }

    /**
     * Get preparation
     *
     * @param $id
     *
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadPreparation($id);
    }

    /**
     * Create new preparation
     *
     * @return Preparation|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Preparation();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Preparation::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing preparation
     *
     * @param $id
     *
     * @return Preparation|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadPreparation($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Preparation::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing preparation
     *
     * @param $id
     *
     * @return Preparation|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadPreparation($id);

        $model->is_deleted = 1;

        if($model->save()) {

            // Remove related records

            /** @var FormulaPreparation $relations */
            $relations = FormulaPreparation::findAll(['prep_id' => $model->id, 'is_deleted' => 0]);

            /** @var FormulaPreparation $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->formula->modified_time = new Expression('NOW()');
                $relation->formula->save(false);

                $relation->save(false);

                Activity::store($model->type, $relation->formula_id, sprintf('Preparation method %s has been removed', $model->name));
            }


            /** @var HerbPreparation $relations */
            $relations = HerbPreparation::findAll(['prep_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbPreparation $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store($model->type, $relation->herb_id, sprintf('Preparation method %s has been removed', $model->name));
            }

            Activity::store(Preparation::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load preparation model
     *
     * @param $id
     *
     * @return Preparation
     * @throws NotFoundHttpException
     */
    private function loadPreparation($id)
    {
        $model = Preparation::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Preparation not found', Errors::PREPARATION_NOT_FOUND);
        }

        return $model;
    }
}
