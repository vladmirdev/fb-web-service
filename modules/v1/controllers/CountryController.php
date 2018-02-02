<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Roles;
use app\models\Continent;
use app\models\Country;
use app\modules\v1\models\CountrySearch;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class CountryController extends BaseController
{
    public $modelClass = 'app\models\Country';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = ['index', 'view', 'search', 'continents', 'options'];

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
     *
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $activeData = new ActiveDataProvider([
            'query' => Country::find()->where($conditions),
            'sort' => [
                'defaultOrder' => ['display_order' => SORT_ASC]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * List continents
     *
     * @return array|ActiveDataProvider
     */
    public function actionContinents()
    {
        $conditions = [];

        $activeData = new ActiveDataProvider([
            'query' => Continent::find()->where($conditions),
            'sort' => [
                'defaultOrder' => ['code' => SORT_ASC]
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
        $searchModel = new CountrySearch();

        $searchModel->is_deleted = 0;

        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $dataProvider;
    }


    /**
     * Get one country
     *
     * @param $id
     *
     * @return array|static|ActiveRecord
     */
    public function actionView($id)
    {
        $model = $this->loadCountry($id);

        return $model;
    }

    /**
     * Create new country
     *
     * @return Country|array
     */
    public function actionCreate()
    {
        $country = new Country();

        $country->name = \Yii::$app->request->post('name');
        $country->full_name = \Yii::$app->request->post('full_name');
        $country->code = \Yii::$app->request->post('code');
        $country->iso3 = \Yii::$app->request->post('iso3');
        $country->continent_code = \Yii::$app->request->post('continent_code');

        $country->save();

        return $country;
    }

    /**
     * Update existing country
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionUpdate($id)
    {

        $country = $this->loadCountry($id);

        $name = \Yii::$app->request->getBodyParam('name');

        if(!empty($name))
            $country->name = $name;

        $full_name = \Yii::$app->request->getBodyParam('full_name');

        if(!empty($full_name))
            $country->full_name = $full_name;

        $country->save();

        return $country;
    }

    /**
     * Delete country
     *
     * @param $id
     *
     * @return array|static|ActiveRecord
     */
    public function actionDelete($id)
    {
        $country = $this->loadCountry($id);

        $country->is_deleted = 1;
        $country->save();

        return $country;
    }

    /**
     * Load country model
     *
     * @param $id
     *
     * @return Country
     * @throws NotFoundHttpException
     */
    private function loadCountry($id)
    {
        $model = Country::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Country not found', Errors::COUNTRY_NOT_FOUND);
        }

        return $model;
    }

}
