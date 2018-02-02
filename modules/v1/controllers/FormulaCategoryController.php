<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\helpers\Security;
use app\models\User;
use app\modules\v1\models\Activity;
use app\modules\v1\models\Formula;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaCategory;

class FormulaCategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaCategory';

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
     * Get list formula categories
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
            'query' => FormulaCategory::find()->where($conditions),
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
     * @param null $id Formula ID
     *
     * @return FormulaCategory|array
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

                    $model = new FormulaCategory();

                    if($id)
                        $model->formula_id = $id;

                    $model->load($record, '');

                    $relation = FormulaCategory::findOne(['formula_id' => $model->formula_id, 'category_id' => $model->category_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

                    if($relation)
                        $model = $relation;

                    if($model->save()) {

                        $ids[] = $model->category_id;

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

                    $excluded = FormulaCategory::find()
                        ->where(['formula_id' => $id ? $id : $model->formula_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()])
                        ->andWhere(['not in', 'category_id', $ids])
                        ->all();

                    if(sizeof($excluded) > 0) {

                        /** @var FormulaCategory $record */
                        foreach ($excluded as $record) {

                            $record->is_deleted = 1;
                            $record->save();

                        }
                    }
                }

                return $response;

            } elseif(sizeof($request) == 0 && $id) {

                // If request contains empty array and we have object id

                $excluded = FormulaCategory::find()
                    ->where(['formula_id' => $id, 'created_by' => Security::getAuthor(), 'is_deleted' => 0])
                    ->all();

                if(sizeof($excluded) > 0) {

                    /** @var FormulaCategory $record */
                    foreach ($excluded as $record) {

                        $record->is_deleted = 1;
                        $record->save();

                    }

                    return $this->sendResponse('Existing categories has been removed');

                }
            }
        }

        $model = new FormulaCategory();

        if($id)
            $model->formula_id = $id;

        $model->load($request, '');

        $relation = FormulaCategory::findOne(['category_id' => $model->category_id, 'formula_id' => $model->formula_id, 'is_deleted' => 0, 'created_by' => Security::getAuthor()]);

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
     * Delete formula category
     *
     * @param $id
     *
     * @return FormulaCategory|array
     */
    public function actionDelete($id)
    {
        $model = $this->loadFormulaCategory($id);

        $model->is_deleted = 1;

        if($model->save()) {

            $model->refresh();

            Activity::store(Formula::ITEM_TYPE, $model->formula_id, sprintf('Category deleted %s', $model->category->name));

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load formula category model
     *
     * @param $id
     *
     * @return FormulaCategory
     * @throws NotFoundHttpException
     */
    private function loadFormulaCategory($id)
    {
        $model = FormulaCategory::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula category not found', Errors::FORMULA_CATEGORY_NOT_FOUND);
        }

        return $model;
    }

}