<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Roles;
use app\models\User;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaPreparation;
use app\modules\v1\models\Preparation;

class FormulaPreparationController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaPreparation';

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
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        $filter = \Yii::$app->request->get('filter', 'all');

        if($filter == 'own' || $filter == 'self')
            $conditions['created_by'] = [\Yii::$app->user->getId()];
        elseif($filter == 'all')
            $conditions['created_by'] = [User::SYSTEM, \Yii::$app->user->getId()];

        if(!\Yii::$app->user->isGuest && $filter == 'all' && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
            unset($conditions['created_by']);

        $activeData = new ActiveDataProvider([
            'query' => FormulaPreparation::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new formula preparation
     *
     * @return FormulaPreparation
     */
    public function actionCreate()
    {
        $prep = new Preparation();

        $prep->name = \Yii::$app->request->post('name');
        $prep->method = \Yii::$app->request->post('method');
        $prep->type = 'formula';

        $prep->save();

        $fp = new FormulaPreparation();

        $fp->formula_id = \Yii::$app->request->post('formula_id');
        $fp->prep_id = $prep->id;

        $fp->save();

        return $fp;
    }

    /**
     * Delete formula preparation
     *
     * @param $id
     *
     * @return FormulaPreparation
     */
    public function actionDelete($id)
    {
        $fp = $this->loadFormulaPreparation($id);

        $fp->is_deleted = 1;

        $fp->save();

        return $fp;
    }

    /**
     * Load formula preparation model
     *
     * @param $id
     *
     * @return FormulaPreparation
     * @throws NotFoundHttpException
     */
    private function loadFormulaPreparation($id)
    {
        $model = FormulaPreparation::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula preparation not found', Errors::FORMULA_PREP_NOT_FOUND);
        }

        return $model;
    }

}
