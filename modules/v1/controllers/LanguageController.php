<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Roles;
use app\models\Language;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class LanguageController extends BaseController
{
    public $modelClass = 'app\models\Language';

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
                'actions' => ['update', 'delete', 'create'],
                'roles' => [Roles::ADMINISTRATOR],
            ]
        ];

        return $behaviors;
    }

    /**
     * List countries
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {

        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => Language::find()->where($conditions),
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Get one language
     *
     * @param $id
     *
     * @return array|static|ActiveRecord
     */
    public function actionView($id)
    {
        $model = $this->loadLanguage($id);

        return $model;
    }

    /**
     * Create new language
     *
     * @return Language|array
     */
    public function actionCreate()
    {
        $language = new Language();

        $language->name = \Yii::$app->request->post('name');
        $language->code = \Yii::$app->request->post('code');

        $language->save();

        return $language;
    }

    /**
     * Update existing language
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionUpdate($id)
    {

        $language = $this->loadLanguage($id);

        $name = \Yii::$app->request->getBodyParam('name');

        if(!empty($name))
            $language->name = $name;

        $code = \Yii::$app->request->getBodyParam('code');

        if(!empty($code))
            $language->code = $code;

        $language->save();

        return $language;
    }

    /**
     * Delete language
     *
     * @param $id
     *
     * @return array|static|ActiveRecord
     */
    public function actionDelete($id)
    {
        $language = $this->loadLanguage($id);

        $language->is_deleted = 1;
        $language->save();

        return $language;
    }

    /**
     * Load language model
     *
     * @param $id
     *
     * @return Language
     * @throws NotFoundHttpException
     */
    private function loadLanguage($id)
    {
        $model = Language::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Language not found', Errors::LANGUAGE_NOT_FOUND);
        }

        return $model;
    }

}
