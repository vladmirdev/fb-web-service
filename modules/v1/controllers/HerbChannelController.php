<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbChannel;

class HerbChannelController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbChannel';

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
     * List herb channels
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbChannel::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb channel
     *
     * @return HerbChannel
     */
    public function actionCreate()
    {
        $herbChannel = new HerbChannel();

        $herbChannel->herb_id = \Yii::$app->request->post('herb_id');
        $herbChannel->channel_id = \Yii::$app->request->post('channel_id');

        $herbChannel->created_by = \Yii::$app->user->getId();

        $herbChannel->save();

        return $herbChannel;
    }

    /**
     * Delete herb channel
     * 
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $herbChannel = $this->loadHerbChannel($id);

        $herbChannel->is_deleted = 1;
        $herbChannel->modified_by = \Yii::$app->user->getId();

        $herbChannel->save();

        return $herbChannel;
    }

    /**
     * Load herb channel model
     *
     * @param $id
     *
     * @return HerbChannel
     * @throws NotFoundHttpException
     */
    private function loadHerbChannel($id)
    {
        $model = HerbChannel::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb channel not found', Errors::HERB_CHANNEL_NOT_FOUND);
        }

        return $model;
    }
}
