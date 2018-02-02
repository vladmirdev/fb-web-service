<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 11:53
 */

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\models\User;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\Feedback;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class FeedbackController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\Feedback';

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
                'actions' => ['index', 'create', 'delete', 'reply', 'forward'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Get list messages
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        if(!\Yii::$app->user->isGuest && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
            $conditions['created_by'] = [User::SYSTEM, \Yii::$app->user->getId()];

        $type = \Yii::$app->request->get('type');

        if(!empty($type))
            $conditions['type'] = $type;

        $activeData = new ActiveDataProvider([
            'query' => Feedback::find()->where($conditions),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);

        return $activeData;
    }

    /**
     * Create new feedback message
     *
     * @return Feedback
     *
     * @throws Exception
     */
    public function actionCreate()
    {
        $transaction = Feedback::getDb()->beginTransaction();

        try {

            $feedback = new Feedback();

            $feedback->type = \Yii::$app->request->post('type');
            $feedback->from = \Yii::$app->request->post('from');
            $feedback->subject = \Yii::$app->request->post('subject');
            $feedback->content = \Yii::$app->request->post('content');

            $feedback->created_by = \Yii::$app->user->getId();

            $feedback->save();

            /*

            \Yii::$app->mailer->compose()
                ->setFrom(\Yii::$app->params['infoEmail'])
                ->setTo(\Yii::$app->params['adminEmail'])
                ->setSubject($feedback->subject)
                ->setHtmlBody($feedback->content)
                ->send();

            */

            $transaction->commit();

        } catch (Exception $ex) {

            \Yii::error($ex->getMessage());

            $transaction->rollBack();

            throw $ex;
        }

        $feedback->refresh();

        \Yii::$app->response->statusCode = Http::CREATED;

        return $feedback;
    }

    /**
     * Delete feedback message
     *
     * @param $id
     *
     * @return ActiveRecord
     */
    public function actionDelete($id)
    {
        $feedback = $this->loadFeedback($id);

        $feedback->is_deleted = 1;

        $feedback->refresh();

        $feedback->save();

        return $feedback;
    }

    /**
     * Reply to feedback message
     *
     * @return mixed
     *
     * @throws \Swift_TransportException
     */
    public function actionReply()
    {

        $id = \Yii::$app->request->post('feedback_id');

        $feedback = $this->loadFeedback($id);

        $subject = \Yii::$app->request->post('subject');

        if(empty($subject)) {
            $subject = 'Reply to '. $feedback->subject;
        }

        $content = \Yii::$app->request->post('content');

        // Send email

        try {

            /*

            \Yii::$app->mailer->compose()
                ->setFrom(\Yii::$app->params['infoEmail'])
                ->setTo($feedback->from)
                ->setSubject($subject)
                ->setHtmlBody($content)
                ->send();

            */

        } catch (\Swift_TransportException $ex) {

            \Yii::error($ex->getMessage());

            throw $ex;
        }

        return $this->sendResponse('Reply email successfully');
    }

    /**
     * Forward reply message
     *
     * @return mixed
     *
     * @throws \Swift_TransportException
     */
    public function actionForward()
    {

        $id = \Yii::$app->request->post('feedback_id');

        $feedback = $this->loadFeedback($id);

        $to = \Yii::$app->request->post('to');
        $subject = \Yii::$app->request->post('subject');

        if(empty($subject)) {
            $subject = 'Forward from '. $feedback->subject;
        }

        $content = 'Forward from '. $feedback->content;

        // Send email

        try {

            /*

            \Yii::$app->mailer->compose()
                ->setFrom(\Yii::$app->params['infoEmail'])
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlBody($content)
                ->send();

            */

        } catch (\Swift_TransportException $ex) {

            \Yii::error($ex->getMessage());

            throw $ex;
        }

        return $this->sendResponse('Forward email successfully');
    }

    /**
     * Load feedback model
     *
     * @param $id
     *
     * @return Feedback
     * @throws NotFoundHttpException
     */
    private function loadFeedback($id)
    {
        $model = Feedback::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('Feedback not found', Errors::FEEDBACK_NOT_FOUND);
        }

        return $model;
    }

}