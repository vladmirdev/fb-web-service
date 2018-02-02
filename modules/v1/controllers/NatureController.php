<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbNature;
use app\traits\Filtered;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Nature;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

class NatureController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Nature';

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
     * Get list natures
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Nature::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View nature
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionView($id)
    {
        return $this->loadNature($id);
    }

    /**
     * Create new nature
     *
     * @return Nature|array
     */
    public function actionCreate()
    {
        $model = new Nature();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Nature::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing nature
     *
     * @param $id
     *
     * @return Nature|array
     */
    public function actionUpdate($id)
    {
        $model = $this->loadNature($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Nature::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing nature
     *
     * @param $id
     *
     * @return Nature|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadNature($id);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbNature $relations */
            $relations = HerbNature::findAll(['nature_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbNature $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Nature %s has been removed', $model->name));
            }

            Activity::store(Nature::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load nature model
     *
     * @param $id
     *
     * @return Nature
     * @throws NotFoundHttpException
     */
    private function loadNature($id)
    {
        $model = Nature::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Nature not found', Errors::NATURE_NOT_FOUND);
        }

        return $model;
    }
}
