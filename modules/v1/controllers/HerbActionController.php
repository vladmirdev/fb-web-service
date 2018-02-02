<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbAction;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbCategory;

class HerbActionController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbAction';

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
     * Get list herb actions
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbAction::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb action
     * 
     * @return HerbAction|array
     */
    public function actionCreate()
    {
        $model = new HerbAction();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Herb::ITEM_TYPE, $model->herb_id, sprintf('New action appended %s', $model->action->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete herb action
     *
     * @param $id
     *
     * @return HerbAction|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadHerbAction($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Herb::ITEM_TYPE, $model->herb_id, sprintf('Action deleted %s', $model->action->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load herb action model
     *
     * @param $id
     *
     * @return HerbAction
     * @throws NotFoundHttpException
     */
    private function loadHerbAction($id)
    {
        $model = HerbAction::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb action not found', Errors::HERB_ACTION_NOT_FOUND);
        }

        return $model;
    }
}
