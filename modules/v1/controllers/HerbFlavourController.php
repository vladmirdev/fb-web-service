<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbFlavour;

class HerbFlavourController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbFlavour';

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
     * List herb flavours
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbFlavour::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb flavour
     *
     * @return HerbFlavour
     */
    public function actionCreate()
    {
        $hf = new HerbFlavour();

        $hf->herb_id = \Yii::$app->request->post('herb_id');
        $hf->flavour_id = \Yii::$app->request->post('flavour_id');

        $hf->created_by = \Yii::$app->user->getId();

        $hf->save();

        return $hf;
    }

    /**
     * Delete herb flavour
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $hf = $this->loadHerbFlavour($id);

        $hf->is_deleted = 1;
        $hf->modified_by = \Yii::$app->user->getId();

        $hf->save();

        return $hf;
    }

    /**
     * Load herb flavour model
     *
     * @param $id
     *
     * @return HerbFlavour
     * @throws NotFoundHttpException
     */
    private function loadHerbFlavour($id)
    {
        $model = HerbFlavour::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb flavour not found', Errors::HERB_FLAVOUR_NOT_FOUND);
        }

        return $model;
    }

}