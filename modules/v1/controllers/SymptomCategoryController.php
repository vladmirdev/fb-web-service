<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\modules\v1\models\Symptom;
use app\modules\v1\models\SymptomCategory;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class SymptomCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\SymptomCategory';

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
     * Get list symptom categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => SymptomCategory::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new symptom category
     *
     * @return SymptomCategory|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new SymptomCategory();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Symptom::ITEM_TYPE, $model->symptom_id, sprintf('New category appended %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);

    }

    /**
     * Delete symptom category
     *
     * @param $id
     *
     * @return SymptomCategory|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadSymptomCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Symptom::ITEM_TYPE, $model->symptom_id, sprintf('Category deleted %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load symptom category model
     *
     * @param $id
     *
     * @return SymptomCategory
     * @throws NotFoundHttpException
     */
    private function loadSymptomCategory($id)
    {
        $model = SymptomCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Preparation category not found', Errors::SYMPTOM_CATEGORY_NOT_FOUND);
        }

        return $model;
    }

}