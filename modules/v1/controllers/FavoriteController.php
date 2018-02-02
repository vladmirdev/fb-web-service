<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\User;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaFavorites;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbFavorites;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaHerb;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class FavoriteController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaFavorite';

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
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'remove'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['GET'],
                'view'   => ['GET'],
                'create' => ['GET', 'POST'],
                'update' => ['GET', 'PUT', 'POST'],
                'delete' => ['POST', 'DELETE'],
                'remove' => ['POST', 'DELETE'],
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list formula herbs
     *
     * @return ActiveDataProvider|array
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0,
            'created_by' => \Yii::$app->user->getId()
        ];

        $filter = \Yii::$app->request->get('filter', 'all');

        $formulaData = new ActiveDataProvider([
            'query' => FormulaFavorites::find()->where($conditions),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['created_time' => SORT_DESC]
            ]
        ]);

        $herbData = new ActiveDataProvider([
            'query' => HerbFavorites::find()->where($conditions),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['created_time' => SORT_DESC]
            ]
        ]);

        switch ($filter) {

            case 'formula':
                return $formulaData;
                break;

            case 'herb':
                return $herbData;
                break;

            default:

                $result = [];

                /** @var Formula $model */
                foreach ($formulaData->models as $model) {
                    $result[] = ['id' => $model->id, 'created_time' => $model->created_time, 'type' => Formula::ITEM_TYPE];
                }

                /** @var Herb $model */
                foreach ($herbData->models as $model) {
                    $result[] = ['id' => $model->id, 'created_time' => $model->created_time, 'type' => Herb::ITEM_TYPE];
                }

                return $result;
        }

    }

    /**
     * Create new favorites
     *
     * @return FormulaFavorites|HerbFavorites|array
     */
    public function actionCreate()
    {
        $request = \Yii::$app->request->getBodyParams();

        $response = [];
        $errors = [];

        if(is_array($request) && isset($request[0])) {

            foreach($request as $record) {

                if((isset($record['type']) && $record['type'] == Formula::ITEM_TYPE) || isset($record['formula_id']))
                    $model = new FormulaFavorites();
                else
                    $model = new HerbFavorites();

                if ($model instanceof FormulaFavorites) {

                    if(isset($record['id'])) {
                        $record['formula_id'] = $record['id'];
                        unset($record['id']);
                    }

                    $relation = FormulaFavorites::findOne(['formula_id' => $record['formula_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                    if($relation)
                        $model = $relation;
                }

                if ($model instanceof HerbFavorites) {

                    if(isset($record['id'])) {
                        $record['herb_id'] = $record['id'];
                        unset($record['id']);
                    }

                    $relation = HerbFavorites::findOne(['herb_id' => $record['herb_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                    if($relation)
                        $model = $relation;

                }

                $model->load($record, '');

                if($model->save()) {

                    $model->refresh();
                    $response[] = $model;
                }

                else {
                    $errors[] = $model->getErrors();
                    $response[] = ['message' => array_values($model->getFirstErrors())[0]];
                }
            }

            if(sizeof($errors) === 0)
                \Yii::$app->response->statusCode = Http::CREATED;

            return $response;

        }

        if((isset($request['type']) && $request['type'] == Formula::ITEM_TYPE) || isset($request['formula_id']))
            $model = new FormulaFavorites();
        else
            $model = new HerbFavorites();

        if(isset($request['id']) && $model instanceof FormulaFavorites) {
            $request['formula_id'] = $request['id'];
            unset($request['id']);

            $relation = FormulaFavorites::findOne(['formula_id' => $request['herb_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

            if($relation)
                $model = $relation;
        }

        if(isset($request['id']) && $model instanceof HerbFavorites) {
            $request['herb_id'] = $request['id'];
            unset($request['id']);

            $relation = HerbFavorites::findOne(['herb_id' => $request['herb_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

            if($relation)
                $model = $relation;
        }

        $model->load($request, '');

        if($model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    public function actionHerb()
    {

    }

    /**
     * Delete item(s) from favorites
     *
     * @return FormulaFavorites|HerbFavorites|array
     */
    public function actionRemove()
    {
        $request = \Yii::$app->request->getBodyParams();

        $response = [];
        $errors = [];

        if(is_array($request) && isset($request[0])) {

            foreach($request as $record) {

                if((isset($record['type']) && $record['type'] == Formula::ITEM_TYPE) || isset($record['formula_id']))
                    $model = new FormulaFavorites();
                else
                    $model = new HerbFavorites();

                if ($model instanceof FormulaFavorites) {

                    if(isset($record['id'])) {
                        $record['formula_id'] = $record['id'];
                        unset($record['id']);
                    }

                    $relation = FormulaFavorites::findOne(['formula_id' => $record['formula_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                    if($relation)
                        $model = $relation;
                }

                if ($model instanceof HerbFavorites) {

                    if(isset($record['id'])) {
                        $record['herb_id'] = $record['id'];
                        unset($record['id']);
                    }

                    $relation = HerbFavorites::findOne(['herb_id' => $record['herb_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                    if($relation)
                        $model = $relation;

                }

                if($model) {
                    $model->is_deleted = 1;
                }

                if($model->save()) {

                    $model->refresh();
                    $response[] = $model;
                }

                else {
                    $errors[] = $model->getErrors();
                    $response[] = ['message' => array_values($model->getFirstErrors())[0]];
                }
            }

            return $response;

        }

        if((isset($request['type']) && $request['type'] == Formula::ITEM_TYPE) || isset($request['formula_id']))
            $model = new FormulaFavorites();
        else
            $model = new HerbFavorites();

        if($model instanceof FormulaFavorites) {

            if(isset($request['id'])) {
                $request['formula_id'] = $request['id'];
                unset($request['id']);
            }

            $relation = FormulaFavorites::findOne(['formula_id' => $request['formula_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

            if($relation)
                $model = $relation;
        }

        if($model instanceof HerbFavorites) {

            if(isset($request['id'])) {
                $record['herb_id'] = $request['id'];
                unset($request['id']);
            }

            $relation = HerbFavorites::findOne(['herb_id' => $request['herb_id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

            if($relation)
                $model = $relation;
        }

        if(!$model || $model->isNewRecord)
            return [];

        $model->is_deleted = 1;

        if($model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }
}
