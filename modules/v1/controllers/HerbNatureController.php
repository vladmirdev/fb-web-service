<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbNature;

class HerbNatureController extends BaseController
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
     * Get lst herb natures
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbNature::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb nature
     *
     * @return HerbNature
     */
    public function actionCreate()
    {
        $hn = new HerbNature();

        $hn->herb_id = \Yii::$app->request->post('herb_id');
        $hn->nature_id = \Yii::$app->request->post('nature_id');

        $hn->created_by = \Yii::$app->user->getId();

        $hn->save();

        return $hn;
    }

    /**
     * Delete herb nature
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $hn = $this->loadHerbNature($id);

        $hn->is_deleted = 1;
        $hn->modified_by = \Yii::$app->user->getId();

        $hn->save();

        return $hn;
    }

    /**
     * Load herb nature model
     *
     * @param $id
     *
     * @return HerbNature
     * @throws NotFoundHttpException
     */
    private function loadHerbNature($id)
    {
        $model = HerbNature::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb nature not found', Errors::HERB_NATURE_NOT_FOUND);
        }

        return $model;
    }
}
