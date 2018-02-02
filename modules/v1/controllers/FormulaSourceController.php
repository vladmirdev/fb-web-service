<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Roles;
use app\models\User;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\FormulaSource;
use app\modules\v1\models\Source;

class FormulaSourceController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\FormulaSource';

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

    /**
     * Get list formula sources
     *
     * @return ActiveDataProvider
     */
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
            'query' => FormulaSource::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new formulas source
     *
     * @return FormulaSource
     */
    public function actionCreate()
    {
        $fs = new FormulaSource();

        $fs->formula_id = \Yii::$app->request->post('formula_id');
        $fs->source_id = \Yii::$app->request->post('source_id');

        $fs->save();

        return $fs;
    }

    /**
     * Update existing source
     *
     * @param $id
     */
    public function actionUpdate($id)
    {

        $source = Source::findOne($id);

        $source->date = \Yii::$app->request->getBodyParam('date');
        $source->author = \Yii::$app->request->getBodyParam('author');
        $source->english_name = \Yii::$app->request->getBodyParam('english_name');
        $source->chinese_name = \Yii::$app->request->getBodyParam('chinese_name');
        $source->type = 'formula';

        $source->save();
    }

    /**
     * Delete formula source
     *
     * @param $id
     *
     * @return FormulaSource
     */
    public function actionDelete($id)
    {

        $fs = $this->loadFormulaSource($id);

        $fs->is_deleted = 1;

        $fs->save();

        return $fs;
    }

    /**
     * Load formula source model
     *
     * @param $id
     *
     * @return FormulaSource
     * @throws NotFoundHttpException
     */
    private function loadFormulaSource($id)
    {
        $model = FormulaSource::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Formula source not found', Errors::FORMULA_SOURCE_NOT_FOUND);
        }

        return $model;
    }

}