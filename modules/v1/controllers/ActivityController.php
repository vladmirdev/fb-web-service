<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\ActivitySearch;
use yii\data\ActiveDataProvider;

class ActivityController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Activity';

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
                'actions' => ['index', 'search'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Show formulas index
     *
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $searchModel = new ActivitySearch();

        $dataProvider = $searchModel->search([]);

        return $dataProvider;
    }

    /**
     * Search by activity events
     *
     * @return array|ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new ActivitySearch();

        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $dataProvider;
    }
}
