<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbCultivation;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class HerbCultivationController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbCultivation';

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
     * Get list herb cultivations
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbCultivation::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb cultivation
     *
     * @return HerbCultivation
     */
    public function actionCreate()
    {
        $hc = new HerbCultivation();

        $hc->herb_id = \Yii::$app->request->post('herb_id');
        $hc->cultivation_id = \Yii::$app->request->post('cultivation_id');

        $hc->created_by = \Yii::$app->user->getId();

        $hc->save();

        return $hc;
    }

    /**
     * Delete herb cultivation
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $hc = $this->loadHerbCultivation($id);

        $hc->is_deleted = 1;
        $hc->modified_by = \Yii::$app->user->getId();

        $hc->save();

        return $hc;
    }

    /**
     * Load herb cultivation model
     *
     * @param $id
     *
     * @return HerbCultivation
     * @throws NotFoundHttpException
     */
    private function loadHerbCultivation($id)
    {
        $model = HerbCultivation::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb cultivation not found', Errors::HERB_CULTIVATION_NOT_FOUND);
        }

        return $model;
    }
}
