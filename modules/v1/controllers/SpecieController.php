<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbSpecies;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Species;

class SpecieController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Species';

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
     * Get list species
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Species::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Get specie
     *
     * @param $id
     *
     * @return Species
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
       return $this->loadSpecie($id);
    }

    /**
     * Create new specie
     *
     * @return Species|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Species();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Species::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing specie
     *
     * @param $id
     *
     * @return Species|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadSpecie($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Species::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing specie
     *
     * @param $id
     *
     * @return Species|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadSpecie($id);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbSpecies $relations */
            $relations = HerbSpecies::findAll(['species_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbSpecies $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Specie %s has been removed', $model->name));
            }

            Activity::store(Species::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load species model
     *
     * @param $id
     *
     * @return Species
     * @throws NotFoundHttpException
     */
    private function loadSpecie($id)
    {
        $model = Species::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Specie not found', Errors::SYMPTOM_NOT_FOUND);
        }

        return $model;
    }
}
