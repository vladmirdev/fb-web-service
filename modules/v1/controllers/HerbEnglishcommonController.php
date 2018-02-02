<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbEnglishcommon;

class HerbEnglishcommonController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\HerbEnglishcommon';

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
     * Get list herb english commons
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbEnglishcommon::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new english common
     *
     * @return HerbEnglishcommon
     */
    public function actionCreate()
    {
        $fc = new HerbEnglishcommon();

        $fc->herb_id = \Yii::$app->request->post('herb_id');
        $fc->english_common_id = \Yii::$app->request->post('english_common_id');

        $fc->created_by = \Yii::$app->user->getId();

        $fc->save();

        return $fc;
    }

    /**
     * Delete english common
     *
     * @param $id
     *
     * @return HerbEnglishcommon
     */
    public function actionDelete($id)
    {
        $fc = $this->loadHerbEnglishCommon($id);

        $fc->is_deleted = 1;
        $fc->modified_by = \Yii::$app->user->getId();

        $fc->save();

        return $fc;
    }

    /**
     * Load herb english common model
     *
     * @param $id
     *
     * @return HerbEnglishcommon
     * @throws NotFoundHttpException
     */
    private function loadHerbEnglishCommon($id)
    {
        $model = HerbEnglishcommon::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb english common not found', Errors::HERB_ENGLISH_COMMON_NOT_FOUND);
        }

        return $model;
    }
}
