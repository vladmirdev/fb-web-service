<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 11:58
 */

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\models\Activity;
use app\modules\v1\models\EnglishCommon;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbEnglishcommon;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class EnglishCommonController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\EnglishCommon';

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
     * List english common
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => EnglishCommon::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * View english common
     *
     * @param $id
     *
     * @return EnglishCommon
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->loadEnglishCommon($id);
    }

    /**
     * Create english common
     *
     * @return EnglishCommon|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new EnglishCommon();

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(EnglishCommon::ITEM_TYPE, $model->id, Actions::CREATE);

            $model->refresh();

            \Yii::$app->response->statusCode = Http::CREATED;

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Update existing english common
     *
     * @param $id
     *
     * @return EnglishCommon|array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadEnglishCommon($id);

        if($model->load(\Yii::$app->request->getBodyParams(), '') && $model->save()) {

            Activity::store(EnglishCommon::ITEM_TYPE, $model->id, Actions::UPDATE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Delete english common
     *
     * @param $id
     *
     * @return EnglishCommon|array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->loadEnglishCommon($id);

        $model->is_deleted = 1;

        if($model->save()) {

            /** @var HerbEnglishcommon $relations */
            $relations = HerbEnglishcommon::findAll(['english_common_id' => $model->id, 'is_deleted' => 0]);

            /** @var HerbEnglishcommon $relation */
            foreach ($relations as $relation) {

                $relation->is_deleted = 1;
                $relation->modified_time = new Expression('NOW()');

                $relation->herb->modified_time = new Expression('NOW()');
                $relation->herb->save(false);

                $relation->save(false);

                Activity::store(Herb::ITEM_TYPE, $relation->herb_id, sprintf('English common %s has been removed', $model->name));
            }

            Activity::store(EnglishCommon::ITEM_TYPE, $model->id, Actions::DELETE);

            $model->refresh();

            return $model;
        }

        return $this->sendValidationResult($model);
    }

    /**
     * Load english common model
     *
     * @param $id
     *
     * @return EnglishCommon
     * @throws NotFoundHttpException
     */
    private function loadEnglishCommon($id)
    {
        $model = EnglishCommon::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('English common not found', Errors::ENGLISH_COMMON_NOT_FOUND);
        }

        return $model;
    }
}
