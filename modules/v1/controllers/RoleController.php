<?php

namespace app\modules\v1\controllers;

use app\models\auth\Item;
use yii\data\ActiveDataProvider;

class RoleController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\User';

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
                'actions' => ['index'],
                'roles' => ['@']
            ]
        ];

        return $behaviors;
    }

    /**
     * Show roles index
     *
     * @return array|ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = ['type' => 1];

        $activeData = new ActiveDataProvider([
            'query' => Item::find()->where($conditions),
            'pagination' => false
        ]);

        return $activeData;
    }
}
