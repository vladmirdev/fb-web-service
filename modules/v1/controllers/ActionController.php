<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\forms\ActionForm;
use app\models\User;
use app\modules\v1\models\Action;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaAction;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbAction;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class ActionController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Action';
    public $smartApi = true;

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
                'actions' => ['index', 'view'],
                'roles' => ['@']
            ],
            [
                'allow' => true,
                'actions' => ['create', 'update', 'delete', 'categories', 'import'],
                'roles' => [Roles::ADMINISTRATOR],
            ]
        ];

        return $behaviors;
    }


    /**
     * Get actions list
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => Action::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View action
     *
     * @param $id
     *
     * @return Action
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadAction($id);
    }

    /**
     * Create new action
     *
     * @return Action|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        if($this->smartApi)
            return $this->actionImport();

        $model = new Action();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Action::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Batch actions import
     *
     * @param integer|null $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionImport($id = null)
    {
        $form = new ActionForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                Activity::store(Action::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                Activity::store(Action::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Action::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::ACTION_CREATION_ERROR : Errors::ACTION_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update existing action
     *
     * @param $id
     *
     * @return Action|array
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        if($this->smartApi)
            return $this->actionImport($id);

        $model = $this->loadAction($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Action::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing action
     *
     * @param $id
     *
     * @return Action|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadAction($id);

        $model->is_deleted = 1;

        if($model->save()) {

            // Remove related records

            /** @var FormulaAction $relations */
            $relations = FormulaAction::findAll(['action_id' => $model->id, 'is_deleted' => 0]);

            /** @var FormulaAction $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->formula->modified_time = new Expression('NOW()');
                $relation->formula->save(false);

                $relation->save(false);

                Activity::store(Formula::ITEM_TYPE, $relation->formula_id, sprintf('Action %s has been removed', $model->name));
            }

            /** @var HerbAction $relations */
            $relations = HerbAction::findAll(['action_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbAction $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Action %s has been removed', $model->name));
            }

            Activity::store(Action::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);

    }

    /**
     * Get action categories
     *
     * @param $id
     *
     * @return ActiveDataProvider
     * @throws NotFoundHttpException
     */
    public function actionCategories($id)
    {
        $model = $this->loadAction($id);

        $activeData = new ActiveDataProvider([
            'query' => $model->getCategories(),
            'pagination' => false
        ]);

        return $activeData;
    }

    /**
     * Load action model
     *
     * @param $id
     *
     * @return Action
     * @throws NotFoundHttpException
     */
    private function loadAction($id)
    {
        $model = Action::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Action not found', Errors::ACTION_NOT_FOUND);
        }

        return $model;
    }
}
