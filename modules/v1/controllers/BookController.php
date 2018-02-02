<?php

namespace app\modules\v1\controllers;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\Activity;
use app\modules\v1\models\BookChapter;
use app\modules\v1\models\User;
use app\traits\Filtered;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Book;

class BookController extends BaseController
{
    use Filtered;

    public $modelClass = 'api\modules\v1\models\Book';

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
                'actions' => ['index', 'view', 'create', 'update', 'delete', 'chapters', 'chapter'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list books
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = $this->prepareFilter();

        $activeData = new ActiveDataProvider([
            'query' => Book::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ],
            'sort' => [
                'defaultOrder' => [
                    'english_name' => SORT_ASC
                ]
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new book
     *
     * @return Book|array
     */
    public function actionCreate()
    {
        $book = $this->loadBook(null, Actions::CREATE);

        if($book->load(\Yii::$app->request->getBodyParams(), '') && $book->save()) {

            Activity::store(Book::ITEM_TYPE, $book->id, Actions::CREATE);

            \Yii::$app->response->statusCode = Http::CREATED;

            $book->refresh();

            return $book;
        }

        return $this->sendValidationResult($book);
    }

    /**
     * Update existing book
     *
     * @param $id
     *
     * @return Book|array
     */
    public function actionUpdate($id)
    {
        $book = $this->loadBook($id, Actions::UPDATE);

        if($book->load(\Yii::$app->request->getBodyParams(), '') && $book->save()) {

            Activity::store(Book::ITEM_TYPE, $book->id, Actions::UPDATE);

            $book->refresh();

            return $book;
        }

        return $this->sendValidationResult($book);
    }

    /**
     * View book
     *
     * @param $id
     *
     * @return Book
     */
    public function actionView($id)
    {
        return $this->loadBook($id);
    }

    /**
     * Delete book
     *
     * @param $id
     *
     * @return Book|array
     */
    public function actionDelete($id)
    {
        $book = $this->loadBook($id, Actions::DELETE);

        $book->is_deleted = 1;

        if($book->save()) {

            Activity::store(Book::ITEM_TYPE, $book->id, Actions::DELETE);

            // Delete related records

            BookChapter::updateAll(['is_deleted' => 1], ['book_id' => $book->id, 'is_deleted' => 0]);

            $book->refresh();

            return $book;

        }

        return $this->sendValidationResult($book);
    }

    /**
     * Get book chapters
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function actionChapters($id)
    {
        $book = $this->loadBook($id);

        $activeData = new ActiveDataProvider([
            'query' => $book->getChapters(),
            'pagination' => false
        ]);

        return $activeData;
    }

    /**
     * Get book chapter
     *
     * @param $id
     *
     * @param $chapter_id
     *
     * @return array|mixed
     */
    public function actionChapter($id, $chapter_id)
    {
        $book = $this->loadBook($id);

        $activeData = new ActiveDataProvider([
            'query' => $book->getChapters()->andWhere(['id' => $chapter_id]),
            'pagination' => false
        ]);

        return $activeData;
    }

    /**
     * Load book model
     *
     * @param $id
     *
     * @param string $action
     *
     * @return Book
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadBook($id, $action = Actions::VIEW)
    {
        if($action == Actions::CREATE)
            return new Book();

        $model = Book::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Book not found', Errors::BOOK_NOT_FOUND);
        }

        if($action == Actions::UPDATE || $action == Actions::DELETE) {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book updating is forbidden', Errors::BOOK_UPDATING_IS_FORBIDDEN);
        }

        if($action == Actions::VIEW) {

            if($model->created_by != 0 && $model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Book viewing is forbidden', Errors::BOOK_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
    
}
