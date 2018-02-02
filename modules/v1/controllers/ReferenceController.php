<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\Reference;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class ReferenceController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Reference';

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
                'roles' => [Roles::ADMINISTRATOR],
            ]
        ];

        return $behaviors;
    }

    /**
     * Get references list
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => Reference::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View reference
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionView($id)
    {
        return $this->loadReference($id);
    }

    /**
     * Create new reference
     *
     * @return Reference
     */
    public function actionCreate()
    {
        $model = new Reference();

        $model->content = \Yii::$app->request->post('content');
        $model->book_id = \Yii::$app->request->post('book_id');
        $model->web_url = \Yii::$app->request->post('web_url');
        $model->app_url = \Yii::$app->request->post('app_url');

        if(\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
            $model->created_by = User::SYSTEM;

        $model->save();

        Activity::store(Reference::ITEM_TYPE, $model->id, Actions::CREATE);

        \Yii::$app->response->statusCode = Http::CREATED;

        return $model;
    }

    /**
     * Update existing reference
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionUpdate($id)
    {
        $model = $this->loadReference($id);

        $content = \Yii::$app->request->getBodyParam('content');

        if(!empty($content))
            $model->content = $content;

        $model->book_id = \Yii::$app->request->getBodyParam('book_id');
        $model->web_url = \Yii::$app->request->getBodyParam('web_url');
        $model->app_url = \Yii::$app->request->getBodyParam('app_url');

        $model->save();

        Activity::store(Reference::ITEM_TYPE, $model->id, Actions::UPDATE);

        return $model;
    }

    /**
     * Delete existing reference
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $model = $this->loadReference($id);

        $model->is_deleted = 1;

        $model->save();

        Activity::store(Reference::ITEM_TYPE, $model->id, Actions::DELETE);

        return $model;
    }

    /**
     * Load reference model
     *
     * @param $id
     *
     * @return Reference
     * @throws NotFoundHttpException
     */
    private function loadReference($id)
    {
        $model = Reference::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Reference not found', Errors::REFERENCE_NOT_FOUND);
        }

        return $model;
    }
}
