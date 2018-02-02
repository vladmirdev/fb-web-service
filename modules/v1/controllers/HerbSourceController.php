<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbSource;

class HerbSourceController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbSource';

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
     * List herb sources
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbSource::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb source
     *
     * @return HerbSource
     */
    public function actionCreate()
    {
        $hs = new HerbSource();

        $hs->herb_id = \Yii::$app->request->post('herb_id');
        $hs->source_id = \Yii::$app->request->post('source_id');

        $hs->created_by = \Yii::$app->user->getId();

        $hs->save();

        return $hs;
    }

    /**
     * Delete herb source
     *
     * @param $id
     *
     * @return HerbSource
     */
    public function actionDelete($id)
    {
        $hs = $this->loadHerbSource($id);

        $hs->is_deleted = 1;
        $hs->modified_by = \Yii::$app->user->getId();

        $hs->save();

        return $hs;
    }

    /**
     * Load herb source model
     *
     * @param $id
     *
     * @return HerbSource
     * @throws NotFoundHttpException
     */
    private function loadHerbSource($id)
    {
        $model = HerbSource::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb source not found', Errors::HERB_SOURCE_NOT_FOUND);
        }

        return $model;
    }
}
