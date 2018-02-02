<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\forms\CautionForm;
use app\models\User;
use app\modules\v1\models\Formula;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbCaution;
use app\traits\Filtered;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Caution;

class CautionController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Caution';
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
     * List cautions
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Caution::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View caution
     *
     * @param $id
     *
     * @return Caution
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadCaution($id);
    }

    /**
     * Create new caution
     *
     * @return Caution|array
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

        $caution = new Caution();

        if($caution->load(\Yii::$app->request->getBodyParams(), '') && $caution->save()) {

            Activity::store(Caution::ITEM_TYPE, $caution->id, Actions::CREATE);

            $caution->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $caution;
        }

        return $this->sendValidationResult($caution);
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
        $form = new CautionForm();

        if($id)
            $form->id = $id;

        $form->load(\Yii::$app->request->getBodyParams(), '');

        if($form->save()) {

            if($form->newRecord) {
                \Yii::$app->response->statusCode = Http::CREATED;
                Activity::store(Caution::ITEM_TYPE, $form->id, Actions::CREATE);
            } else {
                Activity::store(Caution::ITEM_TYPE, $form->id, Actions::UPDATE);
            }

            return Caution::find()
                ->with(array_values($form->getRelations()))
                ->where(['id' => $form->id])
                ->asArray()
                ->one();
        }

        return $this->sendResponse(['message' => $form->getErrors()], $form->newRecord ? Errors::CAUTION_CREATION_ERROR : Errors::CAUTION_UPDATING_ERROR, Http::BAD_REQUEST);
    }

    /**
     * Update existing caution
     *
     * @param $id
     *
     * @return Caution|array
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

        $caution = $this->loadCaution($id, Actions::UPDATE);

        if($caution->load(\Yii::$app->request->getBodyParams(), '') && $caution->save()) {

            Activity::store(Caution::ITEM_TYPE, $caution->id, Actions::UPDATE);

            $caution->refresh();

            return $caution;
        }

        return $this->sendValidationResult($caution);
    }

    /**
     * Delete caution
     *
     * @param $id
     *
     * @return Caution|array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $caution = $this->loadCaution($id, Actions::DELETE);

        $caution->is_deleted = 1;

        if($caution->save()) {

            /** @var HerbCaution $relations */
            $relations = HerbCaution::findAll(['caution_id' => $caution->id, 'is_deleted' => 0]);

            /** @var HerbCaution $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('Caution %s has been deleted', $caution->name));
            }

            Activity::store(Caution::ITEM_TYPE, $caution->id, Actions::DELETE);

            $caution->refresh();

            return $caution;
        }

        return $this->sendValidationResult($caution);
    }

    /**
     * Load caution model
     *
     * @param $id
     * @param string $action
     *
     * @return Caution
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadCaution($id, $action = Actions::VIEW)
    {
        if($action == Actions::CREATE)
            return new Caution();

        $model = Caution::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Caution not found', Errors::CAUTION_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Caution updating is forbidden', Errors::CAUTION_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Caution viewing is forbidden', Errors::CAUTION_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}
