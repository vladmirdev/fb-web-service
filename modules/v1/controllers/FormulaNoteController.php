<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 11:55
 */

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaNote;

class FormulaNoteController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaNote';

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
     * Get list formula notes
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
            'query' => FormulaNote::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Assign note to formula
     *
     * @return FormulaNote|array
     */
    public function actionCreate()
    {
        $model = new FormulaNote();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(FormulaNote::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing formula note relation
     *
     * @param $id
     *
     * @return FormulaNote|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadFormulaNote($id);

        $model->is_deleted = 1;

        if($model->save()) {

            Activity::store(FormulaNote::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load formula note model
     *
     * @param $id
     *
     * @return FormulaNote
     * @throws NotFoundHttpException
     */
    private function loadFormulaNote($id)
    {
        $model = FormulaNote::findOne(['id' => $id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

        if(!$model)
            throw new NotFoundHttpException('Formula note not found', Errors::FORMULA_NOTE_NOT_FOUND);

        return $model;
    }
}
