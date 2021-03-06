<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\modules\v1\models\Caution;
use app\modules\v1\models\CautionCategory;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class CautionCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\CautionCategory';

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
     * Get list caution categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => CautionCategory::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new caution category
     *
     * @return CautionCategory|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new CautionCategory();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Caution::ITEM_TYPE, $model->caution_id, sprintf('New category appended %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete caution category
     *
     * @param $id
     *
     * @return CautionCategory|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadCautionCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Caution::ITEM_TYPE, $model->caution_id, sprintf('Category deleted %s', $model->category->name));

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load caution category model
     *
     * @param $id
     *
     * @return CautionCategory
     * @throws NotFoundHttpException
     */
    private function loadCautionCategory($id)
    {
        $model = CautionCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Caution category not found', Errors::CAUTION_CATEGORY_NOT_FOUND);
        }

        return $model;
    }

}