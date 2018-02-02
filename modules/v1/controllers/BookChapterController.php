<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\models\User;
use app\traits\Filtered;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\BookChapter;

class BookChapterController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\BookChapter';

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
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'pages', 'search'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list book chapters
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => BookChapter::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new book chapter
     *
     * @return BookChapter|array
     */
    public function actionCreate()
    {

        $chapter = $this->loadBookChapter(null, Actions::CREATE);

        if($chapter->load(\Yii::$app->request->getBodyParams(), '') && $chapter->save()) {

            Activity::store(BookChapter::ITEM_TYPE, $chapter->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $chapter->refresh();

            return $chapter;
        }

        return $this->sendValidationResult($chapter);
    }

    /**
     * Update existing book chapter
     *
     * @param $id
     *
     * @return BookChapter|array
     */
    public function actionUpdate($id)
    {
        $chapter = $this->loadBookChapter($id, Actions::UPDATE);

        if($chapter->load(\Yii::$app->request->getBodyParams(), '') && $chapter->save()) {

            Activity::store(BookChapter::ITEM_TYPE, $chapter->id, Actions::CREATE);

            $chapter->refresh();

            return $chapter;
        }

        return $this->sendValidationResult($chapter);
    }

    /**
     * Delete book chapter
     *
     * @param $id
     *
     * @return ActiveRecord|array
     */
    public function actionDelete($id)
    {
        $chapter = $this->loadBookChapter($id, Actions::DELETE);

        $chapter->is_deleted = 1;

        if($chapter->save()) {

            Activity::store(BookChapter::ITEM_TYPE, $chapter->id, Actions::DELETE);

            $chapter->refresh();

            return $chapter;
        }

        return $this->sendValidationResult($chapter);
    }

    /**
     * Get book chapter pages
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function actionPages($id)
    {
        $chapter = $this->loadBookChapter($id);

        return $chapter->pages;
    }

    /**
     * Search by book chapters
     *
     * @todo use search model
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $keywords = \Yii::$app->request->get('keywords');

        $search_filter = '1=1';

        if(!empty($keywords))
            $search_filter = '(english_name LIKE "%'.$keywords.'%" OR chinese_name LIKE "%'.$keywords.'%")';

        $activeData = new ActiveDataProvider([
            'query' => BookChapter::find()->where('is_deleted=0 and '. $search_filter),
            'pagination' => [
                'defaultPageSize' => 20
            ]
        ]);

        return $activeData;
    }

    /**
     * Load book chapter model
     *
     * @param $id
     * @param string $action
     *
     * @return BookChapter
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadBookChapter($id, $action = Actions::VIEW)
    {

        if($action == Actions::CREATE)
            return new BookChapter();

        $model = BookChapter::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Book chapter not found', Errors::BOOK_CHAPTER_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book chapter updating is forbidden', Errors::BOOK_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book chapter viewing is forbidden', Errors::BOOK_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
}
