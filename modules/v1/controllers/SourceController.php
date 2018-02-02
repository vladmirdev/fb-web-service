<?php
namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaSource;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbSource;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Source;

class SourceController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Source';

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
     * List sources
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Source::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View source
     *
     * @param $id
     *
     * @return Source|array
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadSource($id);
    }

    /**
     * Create new source
     *
     * @return Source|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Source();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Source::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing source
     *
     * @param $id
     *
     * @return Source|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadSource($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(Source::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete source
     *
     * @param $id
     *
     * @return Source|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadSource($id);

        $model->is_deleted = 1;

        if($model->save()) {

            // Remove related records

            switch($model->type) {

                case Formula::ITEM_TYPE:

                    /** @var FormulaSource $relations */
                    $relations = FormulaSource::findAll(['source_id' => $model->id, 'is_deleted' => 0]);

                    /** @var FormulaSource $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->formula->modified_time = new Expression('NOW()');
                        $relation->formula->save(false);

                        $relation->save(false);

                        Activity::store($model->type, $relation->formula_id, sprintf('Source %s has been removed', $model->chinese_name));
                    }

                    break;

                case Herb::ITEM_TYPE:

                    /** @var HerbSource $relations */
                    $relations = HerbSource::findAll(['source_id' => $model->id, 'is_deleted' => 0]);

                    /** @var HerbSource $relation */
                    foreach ($relations as $relation) {

                        $relation->is_deleted = 1;
                        $relation->modified_time = new Expression('NOW()');

                        $relation->herb->modified_time = new Expression('NOW()');
                        $relation->herb->save(false);

                        $relation->save(false);

                        Activity::store($model->type, $relation->herb_id, sprintf('Source %s has been deleted', $model->name));
                    }

                    break;
            }
        }

        Activity::store(Source::ITEM_TYPE, $source->id, Actions::DELETE);

        return $source;
    }

    /**
     * Load source model
     *
     * @param $id
     *
     * @return Source
     * @throws NotFoundHttpException
     */
    private function loadSource($id)
    {
        $model = Source::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Source not found', Errors::SOURCE_NOT_FOUND);
        }

        return $model;
    }
}
