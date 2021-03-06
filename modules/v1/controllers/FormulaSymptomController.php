<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\User;
use app\modules\v1\models\Activity;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaAction;
use app\modules\v1\models\FormulaSymptom;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaCategory;

class FormulaSymptomController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaSymptom';

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
     * Get list formula symptoms
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
            'query' => FormulaSymptom::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new formula symptom
     * 
     * @return FormulaSymptom|array
     */
    public function actionCreate()
    {
        $model = new FormulaSymptom();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            Activity::store(Formula::ITEM_TYPE, $model->formula_id, sprintf('New symptom appended %s', $model->symptom->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete formula symptom
     *
     * @param $id
     *
     * @return FormulaSymptom|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadFormulaSymptom($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Formula::ITEM_TYPE, $model->formula_id, sprintf('Symptom deleted %s', $model->symptom->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load formula symptom model
     *
     * @param $id
     *
     * @return FormulaSymptom
     * @throws NotFoundHttpException
     */
    private function loadFormulaSymptom($id)
    {
        $model = FormulaSymptom::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula symptom not found', Errors::FORMULA_SYMPTOM_NOT_FOUND);
        }

        return $model;
    }

}