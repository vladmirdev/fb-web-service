<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\modules\v1\models\HerbAlternate;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbNature;

class HerbAlternateController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbNature';

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
     * Get lst herb alternates
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbAlternate::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb alternate
     *
     * @return HerbAlternate
     */
    public function actionCreate()
    {
        $hn = new HerbAlternate();

        $hn->herb_id = \Yii::$app->request->post('herb_id');
        $hn->alternate_herb_id = \Yii::$app->request->post('alternate_herb_id');

        $hn->created_by = \Yii::$app->user->getId();

        $hn->save();

        return $hn;
    }

    /**
     * Delete herb alternate
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $ha = $this->loadHerbAlternate($id);

        $ha->is_deleted = 1;
        $ha->modified_by = \Yii::$app->user->getId();

        $ha->save();

        return $ha;
    }

    /**
     * Load herb alternate model
     *
     * @param $id
     *
     * @return HerbAlternate
     * @throws NotFoundHttpException
     */
    private function loadHerbAlternate($id)
    {
        $model = HerbAlternate::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb alternate not found', Errors::HERB_ALTERNATE_NOT_FOUND);
        }

        return $model;
    }
}
