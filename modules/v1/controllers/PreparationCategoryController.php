<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\PreparationCategory;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class PreparationCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\PreparationCategory';

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
     * Get list preparation categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => PreparationCategory::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new preparation category
     *
     * @return PreparationCategory|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new PreparationCategory();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Preparation::ITEM_TYPE, $model->prep_id, sprintf('New category appended %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete preparation category
     *
     * @param $id
     *
     * @return PreparationCategory|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadPreparationCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Preparation::ITEM_TYPE, $model->prep_id, sprintf('Category deleted %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load preparation category model
     *
     * @param $id
     *
     * @return PreparationCategory
     * @throws NotFoundHttpException
     */
    private function loadPreparationCategory($id)
    {
        $model = PreparationCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Preparation category not found', Errors::PREPARATION_CATEGORY_NOT_FOUND);
        }

        return $model;
    }

}