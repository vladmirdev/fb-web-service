<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\Action;
use app\modules\v1\models\ActionCategory;
use app\modules\v1\models\CategorySearch;
use app\modules\v1\models\Caution;
use app\modules\v1\models\CautionCategory;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaCategory;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbCategory;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\PreparationCategory;
use app\modules\v1\models\Symptom;
use app\modules\v1\models\SymptomCategory;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Category;

class CategoryController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Category';

    use Filtered;

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
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'formulas', 'herbs', 'actions', 'preparations', 'symptoms', 'cautions', 'search'],
                'roles' => ['@'],
            ]
        ];

        return $behaviors;
    }

    /**
     * List categories
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Category::find()->where($conditions),
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC
                ]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Search categories
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new CategorySearch();

        $searchModel->is_deleted = 0;

        $conditions = $this->prepareFilter();

        $params = array_merge(\Yii::$app->request->queryParams, $conditions);

        $dataProvider = $searchModel->search($params);

        return $dataProvider;
    }

    /**
     * View category
     *
     * @param $id
     *
     * @return Category
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadCategory($id);
    }

    /**
     * Create new category
     *
     * @return Category|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = $this->loadCategory(null, Actions::CREATE);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save())
        {
            Activity::store(Category::ITEM_TYPE, $model->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model, true, Errors::CATEGORY_CREATION_ERROR);
    }

    /**
     * Update existing category
     *
     * @param $id
     *
     * @return Category|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadCategory($id, Actions::UPDATE);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Category::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model, true, Errors::CATEGORY_UPDATING_ERROR);

    }

    /**
     * Delete category
     *
     * @param $id
     *
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $category = $this->loadCategory($id, Actions::DELETE);

        $category->is_deleted = 1;

        if($category->save()) {

            // Remove related records

            switch($category->type) {

                case Formula::ITEM_TYPE:

                    /** @var FormulaCategory $relations */
                    $relations = FormulaCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var FormulaCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->formula->modified_time = new Expression('NOW()');
                        $relation->formula->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->formula_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;

                case Herb::ITEM_TYPE:

                    /** @var HerbCategory $relations */
                    $relations = HerbCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var HerbCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->herb->modified_time = new Expression('NOW()');
                        $relation->herb->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->herb_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;

                case Action::ITEM_TYPE:

                    /** @var ActionCategory $relations */
                    $relations = ActionCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var ActionCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->action->modified_time = new Expression('NOW()');
                        $relation->action->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->action_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;

                case Symptom::ITEM_TYPE:

                    /** @var SymptomCategory $relations */
                    $relations = SymptomCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var SymptomCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->symptom->modified_time = new Expression('NOW()');
                        $relation->symptom->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->symptom_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;

                case Preparation::ITEM_TYPE:

                    /** @var PreparationCategory $relations */
                    $relations = PreparationCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var PreparationCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->prep->modified_time = new Expression('NOW()');
                        $relation->prep->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->prep_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;

                case Caution::ITEM_TYPE:

                    /** @var CautionCategory $relations */
                    $relations = CautionCategory::findAll(['category_id' => $category->id, 'is_deleted' => 0]);

                    /** @var CautionCategory $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->caution->modified_time = new Expression('NOW()');
                        $relation->caution->save(false);

                        $relation->save(false);

                        Activity::store($category->type, $relation->caution_id, sprintf('Category %s has been deleted', $category->name));
                    }

                    break;
            }

            Activity::store(Category::ITEM_TYPE, $category->id, Actions::DELETE);

            $category->refresh();

            return $category;
        }

        return $this->sendResponse(['message' => $category->getErrors()], Errors::CATEGORY_DELETING_ERROR);
    }

    /**
     * Get category formulas
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Formula[]|array
     * @throws NotFoundHttpException
     */
    public function actionFormulas($id)
    {
        $category = $this->loadCategory($id);

        return $category->formulas;
    }

    /**
     * Get category herbs
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Herb[]|array
     * @throws NotFoundHttpException
     */
    public function actionHerbs($id)
    {
        $category = $this->loadCategory($id);

        return $category->herbs;
    }

    /**
     * Get category actions
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Action[]|array
     * @throws NotFoundHttpException
     */
    public function actionActions($id)
    {
        $category = $this->loadCategory($id);

        return $category->actions;
    }

    /**
     * Get category preparations
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Preparation[]|array
     * @throws NotFoundHttpException
     */
    public function actionPreparations($id)
    {
        $category = $this->loadCategory($id);

        return $category->preparations;
    }

    /**
     * Get category symptoms
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Symptom[]|array
     * @throws NotFoundHttpException
     */
    public function actionSymptoms($id)
    {
        $category = $this->loadCategory($id);

        return $category->symptoms;
    }

    /**
     * Get category cautions
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Caution[]|array
     * @throws NotFoundHttpException
     */
    public function actionCautions($id)
    {
        $category = $this->loadCategory($id);

        return $category->cautions;
    }

    /**
     * Load category model
     *
     * @param $id
     * @param string $action
     *
     * @return Category
     * @throws NotFoundHttpException
     */
    private function loadCategory($id, $action = Actions::VIEW)
    {

        if($action == Actions::CREATE)
            return new Category();

        $model = Category::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Category not found', Errors::CATEGORY_NOT_FOUND);
        }

        return $model;
    }
}
