<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\User;
use app\modules\v1\models\Activity;
use app\traits\Filtered;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\BookPage;

class BookPageController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\BookPage';

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
                'actions' => ['index', 'view', 'create', 'update', 'delete'. 'search'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list book pages
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => BookPage::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new book page
     *
     * @return BookPage|array
     */
    public function actionCreate()
    {
        $page = $this->loadBookPage(null, Actions::CREATE);

        if($page->load(\Yii::$app->request->getBodyParams(), '') && $page->save()) {

            Activity::store(BookPage::ITEM_TYPE, $page->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $page->refresh();

            return $page;
        }

        return $this->sendValidationResult($page);
    }

    /**
     * Update existing book page
     *
     * @param $id
     *
     * @return BookPage|array
     */
    public function actionUpdate($id)
    {
        $page = $this->loadBookPage($id, Actions::UPDATE);

        if($page->load(\Yii::$app->request->getBodyParams(), '') && $page->save()) {

            Activity::store(BookPage::ITEM_TYPE, $page->id, Actions::UPDATE);

            $page->refresh();

            return $page;

        }

        return $this->sendValidationResult($page);
    }

    /**
     * Delete existing book page
     *
     * @param $id
     *
     * @return BookPage|array
     */
    public function actionDelete($id)
    {
        $page = $this->loadBookPage($id, Actions::DELETE);

        $page->is_deleted = 1;

        if($page->save()) {

            Activity::store(BookPage::ITEM_TYPE, $page->id, Actions::DELETE);

            $page->refresh();

            return $page;
        }

        return $this->sendValidationResult($page);
    }

    /**
     * Search by book pages
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $keywords = \Yii::$app->request->get('keywords');

        $search_filter = '1=1';

        if(!empty($keywords))
            $search_filter = '(english LIKE "%'.$keywords.'%" OR chinese LIKE "%'.$keywords.'%" OR pinyin LIKE "%'.$keywords.'%")';

        $activeData = new ActiveDataProvider([
            'query' => BookPage::find()->where('is_deleted=0 and '. $search_filter),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Load book page model
     *
     * @param $id
     * @param string $action
     *
     * @return BookPage
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadBookPage($id, $action = Actions::VIEW)
    {

        if($action == Actions::CREATE)
            return new BookPage();

        $model = BookPage::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Book page not found', Errors::BOOK_PAGE_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book page updating is forbidden', Errors::BOOK_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book page viewing is forbidden', Errors::BOOK_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }

}