<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 11:58
 */

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbLatinname;
use app\modules\v1\models\LatinName;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class LatinNameController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\LatinName';

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
     * List latin names
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => LatinName::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View latin name
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionView($id)
    {
        return $this->loadLatinName($id);
    }

    /**
     * Create latin name
     *
     * @return LatinName|array
     */
    public function actionCreate()
    {
        $model = new LatinName();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(LatinName::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing latin name
     *
     * @param $id
     *
     * @return LatinName|array
     */
    public function actionUpdate($id)
    {
        $model = $this->loadLatinName($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(LatinName::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete latin name
     *
     * @param $id
     *
     * @return LatinName|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadLatinName($id);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbLatinname $relations */
            $relations = HerbLatinname::findAll(['latin_name_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbLatinname $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Latin name %s has been removed', $model->name));
            }

            Activity::store(LatinName::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load latin name model
     *
     * @param $id
     *
     * @return LatinName
     * @throws NotFoundHttpException
     */
    private function loadLatinName($id)
    {
        $model = LatinName::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Latin name not found', Errors::LATIN_NAME_NOT_FOUND);
        }

        return $model;
    }
}
