<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\helpers\Security;
use app\models\Activity;
use app\modules\v1\models\Herb;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbCategory;

class HerbCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbCategory';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = ['index', 'view', 'search', 'options'];

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
     * Get list herb categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbCategory::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb category
     *
     * @param null $id Herb ID
     *
     * @return HerbCategory|array
     */
    public function actionCreate($id = null)
    {
        $request = \Yii::$app->request->getBodyParams();

        $response = [];
        $errors = [];
        $ids = [];

        // Check request

        if(is_array($request)) {

            // If request contains multiple records

            if(isset($request[0])) {

                foreach($request as $record) {

                    $model = new HerbCategory();

                    if($id)
                        $model->herb_id = $id;

                    $model->load($record, '');

                    $relation = HerbCategory::findOne(['herb_id' => $model->herb_id, 'category_id' => $model->category_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

                    if($relation)
                        $model = $relation;

                    if($model->save()) {

                        $ids[] = $model->category_id;

                        $model->refresh();
                        $response[] = $model;
                    }

                    else {
                        $errors[] = $model->getErrors();
                        $response[] = ['message' => array_values($model->getFirstErrors())[0]];
                    }
                }

                if(sizeof($errors) === 0 && sizeof($ids) > 0)
                    \Yii::$app->response->statusCode = Http::CREATED;

                if(sizeof($ids) > 0) {

                    $excluded = HerbCategory::find()
                        ->where(['herb_id' => $id ? $id : $model->herb_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()])
                        ->andWhere(['not in', 'category_id', $ids])
                        ->all();

                    if(sizeof($excluded) > 0) {

                        /** @var HerbCategory $record */
                        foreach ($excluded as $record) {

                            $record->is_deleted = 1;
                            $record->save();

                        }
                    }
                }

                return $response;

            } elseif(sizeof($request) == 0 && $id) {

                // If request contains empty array and we have object id

                $excluded = HerbCategory::find()
                    ->where(['herb_id' => $id, 'created_by' => Security::getAuthor(), 'is_deleted' => 0])
                    ->all();

                if(sizeof($excluded) > 0) {

                    /** @var HerbCategory $record */
                    foreach ($excluded as $record) {

                        $record->is_deleted = 1;
                        $record->save();

                    }

                    return $this->sendResponse('Existing categories has been removed');
                }
            }
        }

        $model = new HerbCategory();

        if($id)
            $model->herb_id = $id;

        $model->load($request, '');

        $relation = HerbCategory::findOne(['category_id' => $model->category_id, 'herb_id' => $model->herb_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

        if($relation)
            $model = $relation;

        if($model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete herb category
     *
     * @param $id
     *
     * @return HerbCategory|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadHerbCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Herb::ITEM_TYPE, $model->herb_id, sprintf('Category deleted %s', $model->category->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load herb category model
     *
     * @param $id
     *
     * @return HerbCategory
     * @throws NotFoundHttpException
     */
    private function loadHerbCategory($id)
    {
        $model = HerbCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb category not found', Errors::HERB_CATEGORY_NOT_FOUND);
        }

        return $model;
    }
}
