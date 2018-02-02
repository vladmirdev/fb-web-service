<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 11:58
 */

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\HerbNote;

class HerbNoteController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\HerbNote';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = parent::guestActions();

        $behaviors['authenticator']['except'] = $guestActions;
        $behaviors['access']['except'] = $guestActions;

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
     * List herb notes
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0,
            'created_by' => \Yii::$app->user->getId()
        ];

        $activeData = new ActiveDataProvider([
            'query' => HerbNote::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new herb note
     *
     * @return HerbNote|array
     */
    public function actionCreate()
    {
        $model = new HerbNote();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(HerbNote::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete herb note
     *
     * @param $id
     *
     * @return HerbNote
     */
    public function actionDelete($id)
    {
        $model = $this->loadHerbNote($id);

        if($model->save()) {

            Activity::store(HerbNote::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }
    }

    /**
     * Load herb note model
     *
     * @param $id
     *
     * @return HerbNote
     * @throws NotFoundHttpException
     */
    private function loadHerbNote($id)
    {
        $model = HerbNote::findOne(['id' => $id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

        if(!$model)
            throw new NotFoundHttpException('Herb note not found', Errors::HERB_NOTE_NOT_FOUND);

        return $model;
    }
}
