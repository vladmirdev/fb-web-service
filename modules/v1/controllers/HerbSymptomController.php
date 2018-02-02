<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbSymptom;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbCategory;

class HerbSymptomController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbSymptom';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

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
     * Get list herb symptoms
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbSymptom::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb symptom
     * 
     * @return HerbSymptom|array
     */
    public function actionCreate()
    {
        $model = new HerbSymptom();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Herb::ITEM_TYPE, $model->herb_id, sprintf('New symptom appended %s', $model->symptom->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete herb symptom
     *
     * @param $id
     *
     * @return HerbSymptom|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadHerbSymptom($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Herb::ITEM_TYPE, $model->herb_id, sprintf('Symptom deleted %s', $model->symptom->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load herb symptom model
     *
     * @param $id
     *
     * @return HerbSymptom
     * @throws NotFoundHttpException
     */
    private function loadHerbSymptom($id)
    {
        $model = HerbSymptom::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb symptom not found', Errors::HERB_SYMPTOM_NOT_FOUND);
        }

        return $model;
    }
}
