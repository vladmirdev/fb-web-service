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
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\FormulaNote;
use app\modules\v1\models\HerbNote;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Note;

class NoteController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Note';

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
     * List notes
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
            'query' => Note::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View note
     *
     * @param $id
     *
     * @return Note|array
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadNote($id);
    }

    /**
     * Create note
     *
     * @return Note|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Note();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Note::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing note
     *
     * @param $id
     *
     * @return Note|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadNote($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Note::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete note
     *
     * @param $id
     *
     * @return Note|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadNote($id);

        $model->is_deleted = 1;

        if($model->save()) {

            Activity::store(Note::ITEM_TYPE, $model->id, Actions::DELETE);

            // Delete related records

            FormulaNote::updateAll(['is_deleted' => 1], ['note_id' => $model->id, 'is_deleted' => 0]);
            HerbNote::updateAll(['is_deleted' => 1], ['note_id' => $model->id, 'is_deleted' => 0]);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load note model
     *
     * @param $id
     *
     * @return Note
     * @throws NotFoundHttpException
     */
    private function loadNote($id)
    {
        $model = Note::findOne(['id' => $id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

        if(!$model)
            throw new NotFoundHttpException('Note not found', Errors::NOTE_NOT_FOUND);

        return $model;
    }
}
