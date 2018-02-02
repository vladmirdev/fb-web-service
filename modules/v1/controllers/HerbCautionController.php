<?php
namespace app\modules\v1\controllers;

use app\constants\Errors;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbCaution;

class HerbCautionController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbCaution';

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
     * Get list herb cautions
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => HerbCaution::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb caution
     *
     * @return HerbCaution
     */
    public function actionCreate()
    {
        $herbCaution = new HerbCaution();

        $herbCaution->herb_id = \Yii::$app->request->post('herb_id');
        $herbCaution->caution_id = \Yii::$app->request->post('caution_id');

        $herbCaution->created_by = \Yii::$app->user->getId();

        $herbCaution->save();

        return $herbCaution;
    }

    /**
     * Delete herb caution
     * 
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $herbCaution = $this->loadHerbCaution($id);

        $herbCaution->is_deleted = 1;
        $herbCaution->modified_by = \Yii::$app->user->getId();

        $herbCaution->save();

        return $herbCaution;
    }

    /**
     * Load herb caution model
     *
     * @param $id
     *
     * @return HerbCaution
     * @throws NotFoundHttpException
     */
    private function loadHerbCaution($id)
    {
        $model = HerbCaution::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Herb caution not found', Errors::HERB_CAUTION_NOT_FOUND);
        }

        return $model;
    }
}
