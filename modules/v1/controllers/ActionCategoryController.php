<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\modules\v1\models\Action;
use app\modules\v1\models\ActionCategory;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class ActionCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\ActionCategory';

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
                'roles' => [Roles::ADMINISTRATOR],
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list action categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => ActionCategory::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new action category
     *
     * @return ActionCategory|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new ActionCategory();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Action::ITEM_TYPE, $model->action_id, sprintf('New category appended %s', $model->category->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete action category
     *
     * @param $id
     *
     * @return ActionCategory|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadActionCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Action::ITEM_TYPE, $model->action_id, sprintf('Category deleted %s', $model->category->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load action category model
     *
     * @param $id
     *
     * @return ActionCategory
     * @throws NotFoundHttpException
     */
    private function loadActionCategory($id)
    {
        $model = ActionCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Action category not found', Errors::ACTION_CATEGORY_NOT_FOUND);
        }

        return $model;
    }

}