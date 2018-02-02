<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\forms\HerbForm;
use app\modules\v1\models\Category;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Channel;
use app\modules\v1\models\Cultivation;
use app\modules\v1\models\Flavour;
use app\modules\v1\models\Formula;
use app\modules\v1\models\HerbFavorites;
use app\modules\v1\models\HerbSearch;
use app\modules\v1\models\Nature;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Source;
use app\modules\v1\models\Species;
use app\modules\v1\models\User;
use app\traits\Filtered;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbCategory;
use app\modules\v1\models\HerbCaution;
use app\modules\v1\models\HerbCultivation;
use app\modules\v1\models\HerbChannel;
use app\modules\v1\models\HerbFlavour;
use app\modules\v1\models\FormulaHerb;
use app\modules\v1\models\HerbNature;
use app\modules\v1\models\HerbPreparation;
use app\modules\v1\models\HerbSource;
use app\modules\v1\models\HerbSpecies;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\v1\models\HerbNote;
use app\modules\v1\models\Note;

class HerbController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Herb';
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
                    'cautions',
                    'channels',
                    'cultivations',
                    'flavours',
                    'formulas',
                    'natures',
                    'preparations',
                    'sources',
                    'species',
                    'englishcommons',
                    'latinnames',
                    'notes',
                    'alternates',
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
     * List herbs
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Herb::find()->where($conditions),
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
     * Search by formulas
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new HerbSearch();

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
     * Get herb by ID
     *
     * @param $id
     *
     * @return Herb|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->loadHerb($id);

        return $model;
    }

    /**
     * Create new herb
     *
     * @return Herb|array
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

        $herb = new Herb();

        if($herb->load(\Yii::$app->request->getBodyParams(), '') && $herb->save()) {

            Activity::store(Herb::ITEM_TYPE, $herb->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $herb->refresh();

            return $herb;
        }

        return $this->sendValidationResult($herb);
    }

    /**
     * Batch herbs import
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

        $form = new HerbForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                Activity::store(Herb::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                Activity::store(Herb::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Herb::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::HERB_CREATION_ERROR : Errors::HERB_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update existing herb
     *
     * @param $id
     *
     * @return Herb|array
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

        $herb = $this->loadHerb($id, Actions::UPDATE);

        if($herb->load(\Yii::$app->request->getBodyParams(), '') && $herb->save()) {

            Activity::store(Herb::ITEM_TYPE, $herb->id, Actions::UPDATE);

            $herb->refresh();

            return $herb;
        }

        return $this->sendValidationResult($herb);
    }

    /**
     * Delete herb
     *
     * @param $id
     *
     * @return Herb|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $herb = $this->loadHerb($id, Actions::DELETE);

        $herb->is_deleted = 1;

        if($herb->save()) {

            // Delete related data

            /** @var FormulaHerb $relations */
            $relations = FormulaHerb::findAll(['herb_id' => $herb->id, 'is_deleted' => 0]);

            /** @var FormulaHerb $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->formula->modified_time = new Expression('NOW()');
                $relation->formula->save(false);

                $relation->save(false);

                Activity::store(Formula::ITEM_TYPE, $relation->formula_id, sprintf('Herb %s has been deleted', $herb->name));
            }

            Activity::store(Herb::ITEM_TYPE, $herb->id, Actions::DELETE);

            // Delete favorites

            HerbFavorites::updateAll(['is_deleted' => 1], ['herb_id' => $herb->id, 'is_deleted' => 0]);

            // Delete notes

            HerbNote::updateAll(['is_deleted' => 1], ['herb_id' => $herb->id, 'is_deleted' => 0]);

            $herb->refresh();

            return $herb;
        }

        return $this->sendValidationResult($herb);
    }

    /**
     * Get herb activities
     *
     * @param $id
     *
     * @return array
     */
    public function actionActivities($id)
    {
        $model = $this->loadHerb($id);

        $activities_formatted = [];

        //var_dump(\Yii::$app->controller->module->isAdmin);

        $activities = $model->getActivities()->all();

        /** @var Activity $activity */
        foreach($activities as $activity) {

            $action = [
                'username' => $activity->user->full_name,
                'action' => $activity->action,
                'herb_name' => $model->name,
                'herb_pinyin' => $model->pinyin_ton,
                'action_time' => $activity->created_time,
                'relative_time' => $activity->relative_date
            ];

            array_push($activities_formatted, $action);
        }

        return $activities_formatted;
    }

    /**
     * Get herb categories
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function actionCategories($id)
    {
        $herb = $this->loadHerb($id);

        if(\Yii::$app->request->isPost || \Yii::$app->request->isPut)
            return \Yii::$app->runAction('/v1/herb-category/create', ['id' => $id]);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        $categories = $herb->getCategories()->andWhere(['created_by' => $owners, 'is_deleted' => 0])->all();

        if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

            $newCategories = [];

            /** @var Category $category */
            foreach($categories as $category) {

                /** @var HerbCategory $herbCategory */
                $herbCategory = HerbCategory::find()->where(['herb_id' => $id, 'category_id' => $category->id, 'created_by' => $owners, 'is_deleted' => 0])->one();

                if(!empty($herbCategory)) {
                    $category->herb_category_id = $herbCategory->id;
                    array_push($newCategories, $category);
                }
            }

            $categories = $newCategories;

        } else {

            /** @var Category $category */
            foreach($categories as $category) {

                /** @var HerbCategory $herbCategory */
                $herbCategory = HerbCategory::find()->where(['herb_id '=> $id, 'category_id' => $category->id])->one();
                $category->herb_category_id = $herbCategory->id;
            }
        }

        return $categories;

    }

    /**
     * Get herb cautions
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Caution[]|array
     */
    public function actionCautions($id)
    {

        $herb = $this->loadHerb($id);

        if(!empty($herb)){

            $cautions = $herb->cautions;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newCautions = [];

                /** @var Caution $caution */
                foreach($cautions as $caution) {

                    /** @var HerbCaution $hc */
                    $hc = HerbCaution::find()->where(['herb_id' => $id, 'caution_id' => $caution->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hc)) {
                        $caution->herb_caution_id = $hc->id;
                        array_push($newCautions, $caution);
                    }
                }

                $cautions = $newCautions;

            } else {

                /** @var Caution $caution */
                foreach($cautions as $caution) {

                    /** @var HerbCaution $hc */
                    $hc = HerbCaution::find()->where(['herb_id' => $id, 'caution_id' => $caution->id])->one();
                    $caution->herb_caution_id = $hc->id;
                }
            }

            return $cautions;
        }

        return [];
    }

    /**
     * Get herb channels
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Channel[]|array
     */
    public function actionChannels($id)
    {
 
        $herb = $this->loadHerb($id);

        if(!empty($herb)){

            $channels = $herb->channels;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newChannels = [];

                /** @var Channel $chn */
                foreach($channels as $chn) {

                    /** @var HerbChannel $hc */
                    $hc = HerbChannel::find()->where(['herb_id' => $id,'channel_id' => $chn->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hc)) {
                        $chn->herb_channel_id = $hc->id;
                        array_push($newChannels, $chn);
                    }
                }

                $channels = $newChannels;

            } else {

                /** @var Channel $chn */
                foreach($channels as $chn) {

                    /** @var HerbChannel $hc */
                    $hc = HerbChannel::find()->where(['herb_id' => $id, 'channel_id' => $chn->id])->one();
                    $chn->herb_channel_id = $hc->id;
                }
            }

            return $channels;
        }

        return [];
    }

    /**
     * Get herb cultivations
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Cultivation[]|array
     */
    public function actionCultivations($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)) {

            $cultivations = $herb->cultivations;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newCuls = [];

                /** @var Cultivation $cul */
                foreach($cultivations as $cul) {

                    /** @var HerbCultivation $hc */
                    $hc = HerbCultivation::find()->where(['herb_id' => $id, 'cultivation_id' => $cul->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hc)) {
                        $cul->herb_cultivation_id = $hc->id;
                        array_push($newCuls, $cul);
                    }
                }

                $cultivations = $newCuls;

            } else {

                /** @var Cultivation $cul */
                foreach($cultivations as $cul) {

                    /** @var HerbCultivation $hc */
                    $hc = HerbCultivation::find()->where(['herb_id' => $id, 'cultivation_id' => $cul->id])->one();
                    $cul->herb_cultivation_id = $hc->id;
                }
            }

            return $cultivations;
        }

        return [];
    }

    /**
     * Get herb flavours
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Flavour[]|array
     */
    public function actionFlavours($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)){

            $flavours = $herb->flavours;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newFlvs = [];

                /** @var Flavour $flv */
                foreach($flavours as $flv) {

                    /** @var HerbFlavour $hf */
                    $hf = HerbFlavour::find()->where(['herb_id' => $id, 'flavour_id' => $flv->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hf)) {
                        $flv->herb_flavour_id = $hf->id;
                        array_push($newFlvs, $flv);
                    }
                }

                $flavours = $newFlvs;

            } else {

                /** @var Flavour $flv */
                foreach($flavours as $flv) {

                    /** @var HerbFlavour $hf */
                    $hf = HerbFlavour::find()->where(['herb_id' => $id, 'flavour_id' => $flv->id])->one();
                    $flv->herb_flavour_id = $hf->id;
                }
            }
            
            return $flavours;
        }

        return [];
    }

    /**
     * Get herb formulas
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Formula[]|array
     */
    public function actionFormulas($id)
    {
        $herb = $this->loadHerb($id);

        if(\Yii::$app->request->isPost || \Yii::$app->request->isPut)
            return \Yii::$app->runAction('/v1/herb-formula/create', ['id' => $id]);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        $formulas = $herb->getFormulas()->where(['created_by' => $owners, 'is_deleted' => 0])->all();

        if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

            $newFormulas = [];

            /** @var Formula $formula */
            foreach($formulas as $formula) {

                /** @var FormulaHerb $fh */
                $fh = FormulaHerb::find()->where(['herb_id' => $id, 'formula_id' => $formula->id, 'created_by' => $owners])->one();

                if(!empty($fh)) {

                    $formula->herb_formula_id = $fh->id;

                    array_push($newFormulas, $formula);
                }
            }

            $formulas = $newFormulas;

        } else {

            /** @var Formula $formula */
            foreach($formulas as $formula) {

                /** @var FormulaHerb $fh */
                $fh = FormulaHerb::find()->where(['herb_id' => $id, 'formula_id' => $formula->id])->one();

                $formula->herb_formula_id = $fh->id;
            }
        }

        return $formulas;
    }

    /**
     * Get herb natures
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Nature[]|array
     */
    public function actionNatures($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)){

            $natures = $herb->natures;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newNatures = [];

                /** @var Nature $na */
                foreach($natures as $na) {

                    /** @var HerbNature $hn */
                    $hn = HerbNature::find()->where(['herb_id' => $id, 'nature_id' => $na->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hn)) {
                        $na->herb_nature_id = $hn->id;
                        array_push($newNatures, $na);
                    }
                }

                $natures = $newNatures;

            } else {

                /** @var Nature $na */
                foreach($natures as $na) {

                    /** @var HerbNature $hn */
                    $hn = HerbNature::find()->where(['herb_id' => $id, 'nature_id' => $na->id])->one();
                    $na->herb_nature_id = $hn->id;
                }
            }

            return $natures;
        }

        return [];
    }

    /**
     * Get herb alternates
     *
     * @param $id
     *
     * @return \app\modules\v1\models\HerbAlternate[]|array
     */
    public function actionAlternates($id)
    {
        $herb = $this->loadHerb($id);

        return $herb->alternates;
    }

    /**
     * Get herb preparations
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Preparation[]|array
     */
    public function actionPreparations($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)) {

            $preparations = $herb->preparations;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newPreps = [];

                /** @var Preparation $prep */
                foreach($preparations as $prep) {

                    /** @var HerbPreparation $hp */
                    $hp = HerbPreparation::find()->where(['herb_id' => $id, 'prep_id' => $prep->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hp)) {
                        $prep->herb_prep_id = $hp->id;
                        array_push($newPreps, $prep);
                    }
                }

                $preparations = $newPreps;

            } else {

                /** @var Preparation $prep */
                foreach($preparations as $prep) {

                    /** @var HerbPreparation $hp */
                    $hp = HerbPreparation::find()->where(['herb_id' => $id,'prep_id' => $prep->id])->one();
                    $prep->herb_prep_id = $hp->id;
                }
            }
  
            return $preparations;
        }

        return [];
    }

    /**
     * Get herb sources
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Source[]|array
     */
    public function actionSources($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)) {

            $sources = $herb->sources;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newSrcs = [];

                /** @var Source $src */
                foreach($sources as $src) {

                    /** @var HerbSource $hs */
                    $hs = HerbSource::find()->where(['herb_id' => $id, 'source_id' => $src->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hs)) {
                        $src->herb_source_id = $hs->id;
                        array_push($newSrcs, $src);
                    }
                }

                $sources = $newSrcs;

            } else {

                /** @var Source $src */
                foreach($sources as $src) {

                    /** @var HerbSource $hs */
                    $hs = HerbSource::find()->where(['herb_id' => $id, 'source_id' => $src->id])->one();
                    $src->herb_source_id = $hs->id;
                }
            }

            return $sources;
        }

        return [];
    }

    /**
     * Get herb species
     *
     * @param $id
     *
     * @return \app\modules\v1\models\Species[]|array
     */
    public function actionSpecies($id)
    {
        $herb = $this->loadHerb($id);

        if(!empty($herb)) {

            $species = $herb->species;

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

                $newSps = [];

                /** @var Species $sp */
                foreach($species as $sp) {

                    /** @var HerbSpecies $hs */
                    $hs = HerbSpecies::find()->where(['herb_id' => $id, 'species_id' => $sp->id, 'created_by' => [User::SYSTEM, \Yii::$app->user->getId()]])->one();

                    if(!empty($hs)) {
                        $sp->herb_species_id = $hs->id;
                        array_push($newSps, $sp);
                    }
                }

                $species = $newSps;

            } else {

                /** @var Species $sp */
                foreach($species as $sp) {

                    /** @var HerbSpecies $hs */
                    $hs = HerbSpecies::find()->where(['herb_id' => $id, 'species_id' => $sp->id])->one();
                    $sp->herb_species_id = $hs->id;
                }
            }

            return $species;
        }

        return [];
    }

    /**
     * Get herb english commons
     *
     * @param $id
     *
     * @return \app\modules\v1\models\EnglishCommon[]|array
     */
    public function actionEnglishcommons($id)
    {
        $herb = $this->loadHerb($id);

        return $herb->englishCommons;
    }

    /**
     * Get herb latin names
     *
     * @param $id
     *
     * @return \app\modules\v1\models\LatinName[]|array
     */
    public function actionLatinnames($id)
    {
        $herb = $this->loadHerb($id);

        return $herb->latinNames;
    }

    /**
     * Get herb notes
     *
     * @param $id
     *
     * @return Note[]|array
     */
    public function actionNotes($id)
    {
        $herb = $this->loadHerb($id);

        $filter = \Yii::$app->request->get('filter', 'all');

        $owners = [User::SYSTEM, \Yii::$app->user->getId()];

        if($filter == 'self' || $filter == 'own')
            $owners = [\Yii::$app->user->getId()];

        $notes = $herb->getNotes()->andWhere(['created_by' => $owners, 'is_deleted' => 0])->all();

        /** @var Note $note */
        foreach($notes as &$note) {

            $conditions = ['herb_id' => $id, 'note_id' => $note->id];

            if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                $conditions['created_by'] = [User::SYSTEM, \Yii::$app->user->getId()];

            $herbNote = HerbNote::findOne($conditions);
            $note->herb_note_id = $herbNote->id;
        }

        return $notes;

    }

    /**
     * Load herb model
     *
     * @param integer $id
     * @param string $action
     *
     * @return Herb
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadHerb($id, $action = Actions::VIEW)
    {
        $model = Herb::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Herb not found', Errors::HERB_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Herb updating is forbidden', Errors::HERB_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Herb viewing is forbidden', Errors::HERB_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}
