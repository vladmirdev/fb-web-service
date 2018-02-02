<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbLatinname;

class HerbLatinnameController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\HerbLatinname';

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
     * Herb latin names list
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbLatinname::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb latin name
     *
     * @return HerbLatinname
     */
    public function actionCreate()
    {
        $fc = new HerbLatinname();

        $fc->herb_id = \Yii::$app->request->post('herb_id');
        $fc->latin_name_id = \Yii::$app->request->post('latin_name_id');

        $fc->created_by = \Yii::$app->user->getId();

        $fc->save();

        return $fc;
    }

    /**
     * Delete herb latin name
     *
     * @param $id
     *
     * @return HerbLatinname
     */
    public function actionDelete($id)
    {
        $fc = $this->loadHerbLatinName($id);

        $fc->is_deleted = 1;
        $fc->modified_by = \Yii::$app->user->getId();

        $fc->save();

        return $fc;
    }

    /**
     * Load herb latin name model
     *
     * @param $id
     *
     * @return HerbLatinname
     * @throws NotFoundHttpException
     */
    private function loadHerbLatinName($id)
    {
        $model = HerbLatinname::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb latin name not found', Errors::HERB_LATIN_NAME_NOT_FOUND);
        }

        return $model;
    }
}
