<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Roles;
use app\models\Activity;
use app\models\Template;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Nature;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class TemplateController extends BaseController
{
    public $modelClass = 'app\models\Template';

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
                'roles' => [Roles::ADMINISTRATOR, Roles::MODERATOR, Roles::EDITOR],
            ]
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Get list templates
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => Template::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View template
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionView($id)
    {
        return $this->loadTemplate($id);
    }

    /**
     * Create new template
     *
     * @return Template
     */
    public function actionCreate()
    {
        $template = new Template();

        $template->code = \Yii::$app->request->post('code');
        $template->title = \Yii::$app->request->post('title');
        $template->content = \Yii::$app->request->post('content');

        $template->created_by = \Yii::$app->user->getId();

        $template->save();

        return $template;
    }

    /**
     * Update existing template
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionUpdate($id)
    {
        $template = $this->loadTemplate($id);

        $title = \Yii::$app->request->getBodyParam('title');

        if(!empty($name))
            $template->title = $title;

        $content = \Yii::$app->request->getBodyParam('content');

        if(!empty($name))
            $template->content = $content;

        $template->modified_by = \Yii::$app->user->getId();

        $template->save();

        return $template;
    }

    /**
     * Delete existing template
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $template = $this->loadTemplate($id);

        $template->is_deleted = 1;
        $template->modified_by = \Yii::$app->user->getId();

        $template->save();

        return $template;
    }

    /**
     * Load template model
     *
     * @param $id
     *
     * @return Template
     * @throws NotFoundHttpException
     */
    private function loadTemplate($id)
    {
        $model = Template::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Template not found', Errors::TEMPLATE_NOT_FOUND);
        }

        return $model;
    }
}
