<?php
namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\forms\FormulaForm;
use app\modules\v1\models\Category;
use app\modules\v1\models\FormulaFavorites;
use app\modules\v1\models\FormulaSearch;
use app\modules\v1\models\Herb;
use app\modules\v1\models\Note;
use app\modules\v1\models\Preparation;
use app\traits\Filtered;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaHerb;
use app\modules\v1\models\FormulaCategory;
use app\modules\v1\models\FormulaSource;
use app\modules\v1\models\FormulaPreparation;
use app\modules\v1\models\Activity;
use app\modules\v1\models\User;
use app\modules\v1\models\FormulaNote;

class FormulaController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Formula';
    public $smartApi = false;

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
                'actions' => [
                    'index',
                    'view',
                    'search',
                    'update',
                    'delete',
                    'create',
                    'activities',
                    'categories',
                    'herbs',
                    'sources',
                    'preparations',
                    'notes',
                    'import'
                ],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    protected function verbs()
    {
        $verbs = parent::verbs();

        $verbs['import'][] = 'POST'; //just add the 'POST' to "GET" and "HEAD"
        $verbs['import'][] = 'PUT'; //just add the 'POST' to "GET" and "HEAD"
        $verbs['import'][] = 'OPTIONS'; //just add the 'POST' to "GET" and "HEAD"

        return $verbs;
    }


    /**
     * List formulas
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Formula::find()->where($conditions),
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Search by formulas
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new FormulaSearch();

        $searchModel->is_deleted = 0;

        $isAdmin = \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR);
        $filters = \Yii::$app->request->queryParams;

        if(!\Yii::$app->user->isGuest && !$isAdmin)
            $searchModel->created_by = [User::SYSTEM, \Yii::$app->user->getId()];
        elseif($isAdmin && (!isset($filters['created']) || empty($filters['created'])))
            $searchModel->created_by = [User::SYSTEM];

        $dataProvider = $searchModel->search($filters);

        return $dataProvider;
    }

    /**
     * Get one formula
     *
     * @param $id
     *
     * @return Formula|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->loadFormula($id);

        return $model;
    }

    /**
     * Create new formula
     *
     * @return Formula|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        if($this->smartApi)
            return $this->actionImport();

        $formula = new Formula();

        if($formula->load(\Yii::$app->request->getBodyParams(), '') && $formula->save()) {

            Activity::store(Formula::ITEM_TYPE, $formula->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $formula->refresh();

            return $formula;
        }

        return $this->sendValidationResult($formula);
    }

    /**
     * Batch formulas import
     *
     * @param integer|null $id
     *
     * @return array|boolean
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionImport($id = null)
    {
        if(\Yii::$app->request->isOptions)
            return $this->actionOptions();

        $form = new FormulaForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                Activity::store(Formula::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                Activity::store(Formula::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Formula::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::FORMULA_CREATION_ERROR : Errors::FORMULA_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update existing formula
     *
     * @param $id
     *
     * @return Formula|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        if($this->smartApi)
            return $this->actionImport($id);

        $formula = $this->loadFormula($id, 'update');

        if($formula->load(\Yii::$app->request->getBodyParams(), '') && $formula->save()) {

            Activity::store(Formula::ITEM_TYPE, $formula->id, Actions::UPDATE);

            $formula->refresh();

            return $formula;
        }

        return $this->sendValidationResult($formula);
    }

    /**
     * Delete formula
     *
     * @param $id
     *
     * @return array|static|ActiveRecord
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $formula = $this->loadFormula($id, 'delete');

        $formula->is_deleted = 1;

        if($formula->save()) {

            // Delete related data

            /** @var FormulaHerb $relations */
            $relations = FormulaHerb::findAll(['formula_id' => $formula->id, 'is_deleted' => 0]);

            /** @var FormulaHerb $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Formula %s has been deleted', $formula->name));
            }

            Activity::store(Formula::ITEM_TYPE, $formula->id, Actions::DELETE);

            // Delete favorites

            FormulaFavorites::updateAll(['is_deleted' => 1], ['formula_id' => $formula->id, 'is_deleted' => 0]);

            // Delete notes

            FormulaNote::updateAll(['is_deleted' => 1], ['formula_id' => $formula->id, 'is_deleted' => 0]);

            $formula->refresh();

            return $formula;
        }

        return $this->sendResponse(['message' => $formula->getErrors()], Errors::FORMULA_DELETING_ERROR);

    }

    /**
     * Get formula activities
     *
     * @param $id
     *
     * @return array
     */
    public function actionActivities($id)
    {
        $formula = $this->loadFormula($id);

        $activities_formatted = [];

        //var_dump(\Yii::$app->controller->module->isAdmin);

        $activities = $formula->getActivities()->all();

        /** @var Activity $activity */
        foreach($activities as $activity) {

            $action = [
                'username' => $activity->user->full_name,
                'action' => $activity->action,
                'formula_name' => $formula->name,
                'formula_pinyin' => $formula->pinyin_ton,
                'action_time' => $activity->created_time,
                'relative_time' => $activity->relative_date
            ];

            array_push($activities_formatted, $action);
        }

        return $activities_formatted;
    }

    /**
     * Get formula categories
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Category[]|array
     */
    public function actionCategories($id)
    {
        $formula = $this->loadFormula($id);

        if(\Yii::$app->request->isPost || \Yii::$app->request->isPut)
            return \Yii::$app->runAction('/v1/formula-category/create', ['id' => $id]);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        $categories = $formula->getCategories()->where(['created_by' => $owners, 'is_deleted' => 0])->all();

        if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

            $newCategories = [];

            /** @var Category $category */
            foreach($categories as $category) {

                /** @var FormulaCategory $formulaCategory */
                $formulaCategory = FormulaCategory::find()->where(['formula_id' => $id, 'category_id' => $category->id, 'created_by' => $owners, 'is_deleted' => 0])->one();

                if(!empty($formulaCategory)) {

                    $category->formula_category_id = $formulaCategory->id;

                    array_push($newCategories, $category);
                }
            }

            $categories = $newCategories;

        } else {

            /** @var Category $category */
            foreach($categories as $category) {

                /** @var FormulaCategory $formulaCategory */
                $formulaCategory = FormulaCategory::find()->where(['formula_id' => $id, 'category_id' => $category->id, 'created_by' => User::SYSTEM, 'is_deleted' => 0])->one();

                $category->formula_category_id = $formulaCategory->id;
            }
        }

        return $categories;

    }

    /**
     * Get formula herbs
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Herb[]|array
     */
    public function actionHerbs($id)
    {
        $formula = $this->loadFormula($id);

        if(\Yii::$app->request->isPost || \Yii::$app->request->isPut)
            return \Yii::$app->runAction('/v1/formula-herb/create', ['id' => $id]);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        $herbs = $formula->getHerbs()->where(['created_by' => $owners, 'is_deleted' => 0])->all();

        if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

            $newHerbs = [];

            /** @var Herb $herb */
            foreach($herbs as $herb) {

                /** @var FormulaHerb $formulaHerb */
                $formulaHerb = FormulaHerb::find()->where(['formula_id' => $id, 'herb_id' => $herb->id, 'created_by' => $owners, 'is_deleted' => 0])->one();

                if(!empty($formulaHerb)) {

                    $herb->formula_herb_id = $formulaHerb->id;

                    array_push($newHerbs, $herb);
                }
            }

            $herbs = $newHerbs;

        } else {

            /** @var Herb $herb */
            foreach($herbs as $herb) {

                /** @var FormulaHerb $formulaHerb */
                $formulaHerb = FormulaHerb::find()->where(['formula_id' => $id, 'herb_id' => $herb->id, 'created_by' => User::SYSTEM, 'is_deleted' => 0])->one();

                $herb->formula_herb_id = $formulaHerb->id;
            }
        }

        return $herbs;
    }

    /**
     * Get formula sources
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Source[]|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSources($id)
    {
        $formula = $this->loadFormula($id);

        if(!empty($formula)) {

            $sources = $formula->sources;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newSources = [];

                foreach($sources as $source) {

                    /** @var FormulaSource $formulaSource */
                    $formulaSource = FormulaSource::find()->where(['formula_id' => $id, 'source_id' => $source->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($formulaSource)) {

                        $source->formula_source_id = $formulaSource->id;

                        array_push($newSources, $source);
                    }
                }

                $sources = $newSources;

            } else {

                foreach($sources as $source) {

                    /** @var FormulaSource $formulaSource */
                    $formulaSource = FormulaSource::find()->where(['formula_id' => $id, 'source_id' => $source->id])->one();

                    $source->formula_source_id = $formulaSource->id;
                }
            }

            return $sources;
        }

        return [];
    }

    /**
     * Get formula preparations
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Preparation[]|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionPreparations($id)
    {
        $formula = $this->loadFormula($id);

        if(!empty($formula)) {

            $preps = $formula->preparations;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newPreps = [];

                /** @var Preparation $prep */
                foreach($preps as $prep) {

                    /** @var FormulaPreparation $fp */
                    $fp = FormulaPreparation::find()->where(['formula_id' => $id, 'prep_id' => $prep->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($fp)) {

                        $prep->formula_prep_id = $fp->id;

                        array_push($newPreps, $prep);
                    }
                }

                $preps = $newPreps;

            } else {

                /** @var Preparation $prep */
                foreach($preps as $prep) {

                    /** @var FormulaPreparation $fp */
                    $fp = FormulaPreparation::find()->where(['formula_id' => $id, 'prep_id' => $prep->id])->one();

                    $prep->formula_prep_id = $fp->id;
                }
            }
            
            return $preps;
        }

        return [];
    }

    /**
     * Get formula notes
     *
     * @param $id
     *
     * @return array|ActiveRecord[]
     */
    public function actionNotes($id)
    {
        $formula = $this->loadFormula($id);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        if(!empty($formula)) {

            $notes = $formula->getNotes()->andWhere(['created_by' => $owners, 'is_deleted' => 0])->all();

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                /** @var Note $note */
                foreach($notes as $note) {

                    /** @var FormulaNote $fn */
                    $formulaNote = FormulaNote::findOne(['formula_id' => $id, 'note_id' => $note->id, 'created_by' => $owners, 'is_deleted' => 0]);

                    $note->formula_note_id = $formulaNote->id;
                }

            } else {

                /** @var Note $note */
                foreach($notes as $note) {

                    /** @var FormulaNote $fn */
                    $formulaNote = FormulaNote::findOne(['formula_id' => $id, 'note_id' => $note->id]);

                    $note->formula_note_id = $formulaNote->id;
                }
            }

            return $notes;
        }

        return [];
    }

    /**
     * Load formula model
     *
     * @param integer|null $id
     * @param string $action
     *
     * @return Formula
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadFormula($id, $action = 'view')
    {
        $model = Formula::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Formula not found', Errors::FORMULA_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Formula updating is forbidden', Errors::FORMULA_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && (\Yii::$app->user->isGuest || !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)))
                throw new ForbiddenHttpException('Formula viewing is forbidden', Errors::FORMULA_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}
