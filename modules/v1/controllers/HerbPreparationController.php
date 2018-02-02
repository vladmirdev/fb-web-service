<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbPreparation;

class HerbPreparationController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbPreparation';

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
     * List herb preparations
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbPreparation::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb preparation
     *
     * @return HerbPreparation
     */
    public function actionCreate()
    {
        $hp = new HerbPreparation();

        $hp->herb_id = \Yii::$app->request->post('herb_id');
        $hp->prep_id = \Yii::$app->request->post('prep_id');

        $hp->created_by = \Yii::$app->user->getId();

        $hp->save();

        return $hp;
    }

    /**
     * Delete herb preparation
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $hp = $this->loadHerbPreparation($id);

        $hp->is_deleted = 1;

        $hp->modified_by = \Yii::$app->user->getId();
        $hp->save();

        return $hp;
    }

    /**
     * Load herb preparation model
     *
     * @param $id
     *
     * @return HerbPreparation
     * @throws NotFoundHttpException
     */
    private function loadHerbPreparation($id)
    {
        $model = HerbPreparation::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb preparation not found', Errors::HERB_PREP_NOT_FOUND);
        }

        return $model;
    }
}
