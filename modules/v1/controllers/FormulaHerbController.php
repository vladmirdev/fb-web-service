<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\helpers\Security;
use app\models\User;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaHerb;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class FormulaHerbController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaHerb';

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
     * Get list formula herbs
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
            'query' => FormulaHerb::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new formula herb
     *
     * @param integer|null $id Formula ID
     *
     * @return FormulaHerb|array
     */
    public function actionCreate($id = null)
    {
        $request = \Yii::$app->request->getBodyParams();

        $response = [];
        $errors = [];
        $ids = [];

        // Check request

        if(is_array($request)) {

            // If request contains multiple records

            if(isset($request[0])) {

                foreach($request as $record) {

                    $model = new FormulaHerb();

                    if($id)
                        $model->formula_id = $id;

                    $model->load($record, '');

                    $relation = FormulaHerb::findOne(['herb_id' => $model->herb_id, 'formula_id' => $model->formula_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

                    if($relation)
                        $model = $relation;

                    if($model->save()) {

                        $ids[] = $model->herb_id;

                        $model->refresh();
                        $response[] = $model;
                    }

                    else {
                        $errors[] = $model->getErrors();
                        $response[] = ['message' => array_values($model->getFirstErrors())[0]];
                    }
                }

                if(sizeof($errors) === 0 && sizeof($ids) > 0)
                    \Yii::$app->response->statusCode = Http::CREATED;

                if(sizeof($ids) > 0) {

                    $excluded = FormulaHerb::find()
                        ->where(['formula_id' => $id ? $id : $model->formula_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()])
                        ->andWhere(['not in', 'herb_id', $ids])
                        ->all();

                    if(sizeof($excluded) > 0) {

                        /** @var FormulaHerb $record */
                        foreach ($excluded as $record) {

                            $record->is_deleted = 1;
                            $record->save();

                        }
                    }
                }

                return $response;

            } elseif(sizeof($request) == 0 && $id) {

                // If request contains empty array and we have object id

                if($id) {

                    $excluded = FormulaHerb::find()
                        ->where(['formula_id' => $id, 'created_by' => Security::getAuthor(), 'is_deleted' => 0])
                        ->all();

                    if(sizeof($excluded) > 0) {

                        /** @var FormulaHerb $record */
                        foreach ($excluded as $record) {

                            $record->is_deleted = 1;
                            $record->save();

                        }

                        return $this->sendResponse('Existing herbs has been removed');

                    }
                }
            }
        }

        $model = new FormulaHerb();

        if($id)
            $model->formula_id = $id;

        $model->load($request, '');

        $relation = FormulaHerb::findOne(['herb_id' => $model->herb_id, 'formula_id' => $model->formula_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

        if($relation)
            $model = $relation;

        if($model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update formula herb
     *
     * @param $id
     *
     * @return FormulaHerb
     */
    public function actionUpdate($id)
    {
        $formulaHerb = $this->loadFormulaHerb($id);

        $formulaHerb->load(\Yii::$app->request->getBodyParams(), '');

        $formulaHerb->modified_by = \Yii::$app->user->getId();

        $formulaHerb->save();

        return $formulaHerb;
    }

    /**
     * Delete formula herb
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $formulaHerb = $this->loadFormulaHerb($id);

        $formulaHerb->is_deleted = 1;

        $formulaHerb->save();

        return $formulaHerb;
    }

    /**
     * Load formula herb model
     *
     * @param $id
     *
     * @return FormulaHerb
     * @throws NotFoundHttpException
     */
    private function loadFormulaHerb($id)
    {
        $model = FormulaHerb::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula herb not found', Errors::FORMULA_HERB_NOT_FOUND);
        }

        return $model;
    }
}
