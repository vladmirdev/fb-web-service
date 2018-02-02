<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\User;
use app\modules\v1\models\Activity;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaAction;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaCategory;

class FormulaActionController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaAction';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = ['index', 'view', 'search', 'options'];

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
     * Get list formula actions
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $filter = \Yii::$app->request->get('filter', 'all');

        if($filter == 'own' || $filter == 'self')
            $conditions['created_by'] = [\Yii::$app->user->getId()];
        elseif($filter == 'all')
            $conditions['created_by'] = [User::SYSTEM, \Yii::$app->user->getId()];

        if(!\Yii::$app->user->isGuest && $filter == 'all' && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
            unset($conditions['created_by']);

        $activeData = new ActiveDataProvider([
            'query' => FormulaAction::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new formula category
     * 
     * @return FormulaAction|array
     */
    public function actionCreate()
    {
        $model = new FormulaAction();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Formula::ITEM_TYPE, $model->formula_id, sprintf('New action appended %s', $model->action->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete formula action
     *
     * @param $id
     *
     * @return FormulaAction|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadFormulaAction($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Formula::ITEM_TYPE, $model->formula_id, sprintf('Action deleted %s', $model->action->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load formula action model
     *
     * @param $id
     *
     * @return FormulaAction
     * @throws NotFoundHttpException
     */
    private function loadFormulaAction($id)
    {
        $model = FormulaAction::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula action not found', Errors::FORMULA_ACTION_NOT_FOUND);
        }

        return $model;
    }

}