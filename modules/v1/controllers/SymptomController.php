<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\forms\SymptomForm;
use app\models\User;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaSymptom;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbSymptom;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Symptom;

class SymptomController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Symptom';
    public $smartApi = true;

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
                'actions' => ['index', 'view', 'create', 'update', 'delete'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list notes
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Symptom::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View symptom
     *
     * @param $id
     *
     * @return Symptom
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadSymptom($id);
    }

    /**
     * Create new symptom
     *
     * @return Symptom|array
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

        $model = new Symptom();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            \Yii::$app->response->statusCode = Http::CREATED;

            $model->refresh();

            // @activity

            return $model;

        }

        return $this->sendValidationResult($model);
    }

    /**
     * Batch symptoms import
     *
     * @param integer|null $id
     *
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionImport($id = null)
    {
        $form = new SymptomForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                // Activity::store(Action::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                // Activity::store(Action::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Symptom::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::SYMPTOM_CREATION_ERROR : Errors::SYMPTOM_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update existing symptom
     *
     * @param $id
     *
     * @return Symptom|array
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

        $model = $this->loadSymptom($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            $model->refresh();

            // @activity

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete existing symptom
     *
     * @param $id
     *
     * @return Symptom|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadSymptom($id);

        $model->is_deleted = 1;

        if($model->save()) {

            // Remove related records

            /** @var FormulaSymptom $relations */
            $relations = FormulaSymptom::findAll(['symptom_id' => $model->id, 'is_deleted' => 0]);

            /** @var FormulaSymptom $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->formula->modified_time = new Expression('NOW()');
                $relation->formula->save(false);

                $relation->save(false);

                Activity::store(Formula::ITEM_TYPE, $relation->formula_id, sprintf('Symptom %s has been removed', $model->name));
            }

            /** @var HerbSymptom $relations */
            $relations = HerbSymptom::findAll(['symptom_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbSymptom $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Symptom %s has been removed', $model->name));
            }

            $model->refresh();

            // @activity

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load symptom model
     *
     * @param $id
     *
     * @return Symptom
     * @throws NotFoundHttpException
     */
    private function loadSymptom($id)
    {
        $model = Symptom::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Symptom not found', Errors::SYMPTOM_NOT_FOUND);
        }

        return $model;
    }

}
