<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbSpecies;

class HerbSpeciesController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbSpecies';

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
     * List herb species
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbSpecies::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb specie
     *
     * @return HerbSpecies
     */
    public function actionCreate()
    {
        $hs = new HerbSpecies();

        $hs->herb_id = \Yii::$app->request->post('herb_id');
        $hs->species_id = \Yii::$app->request->post('species_id');

        $hs->created_by = \Yii::$app->user->getId();

        $hs->save();

        return $hs;
    }

    /**
     * Delete herb specie
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $hs = $this->loadHerbSpecie($id);

        $hs->is_deleted = 1;
        $hs->modified_by = \Yii::$app->user->getId();

        $hs->save();

        return $hs;
    }

    /**
     * Load herb specie model
     *
     * @param $id
     *
     * @return HerbSpecies
     * @throws NotFoundHttpException
     */
    private function loadHerbSpecie($id)
    {
        $model = HerbSpecies::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb specie not found', Errors::HERB_SPECIE_NOT_FOUND);
        }

        return $model;
    }

}