<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:31
 */

namespace app\jobs;

use app\constants\Errors;
use app\helpers\Security;
use app\models\Template;
use app\models\User;
use app\modules\v1\models\VerifyCode;
use yii\base\Object;
use yii\queue\Queue;
use yii\web\NotFoundHttpException;

class ResetPasswordJob extends Object implements \yii\queue\RetryableJob
{
    public $code = 'reset';
    public $userId;

    /**
     * Generate and send recovery code
     *
     * @param Queue $queue which pushed and is handling the job
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue)
    {

        $user = User::findOne(['id' => $this->userId, 'is_deleted' => 0]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $transaction = User::getDb()->beginTransaction();

        try {

            // Deactivate all existing verify codes

            VerifyCode::updateAll(['is_used' => 1], ['user_id' => $user->id, 'is_used' => 0]);

            // Generate new verify code

            $verify_code = $this->generateVerifycode($user->id);

            $template = Template::parse($this->code, ['{code}' => $verify_code, '{user}' => $user->firstname]);

            \Yii::$app->mailer->htmlLayout = '@app/views/mail/layout';

            \Yii::$app->mailer->compose('@app/views/mail/message', ['content' => $template->content])
                ->setFrom([\Yii::$app->params['noreplyEmail'] => \Yii::$app->params['noreplyTitle']])
                ->setTo($user->email)
                ->setSubject($template->title)
                ->send();

            $transaction->commit();

        } catch (\Swift_TransportException $ex) {

            $transaction->rollBack();

            //$this->generateVerifyCode($user->id, '000000');

            throw $ex;
        }
    }

    /**
     * Generate verify code
     *
     * @param $user_id
     *
     * @param null $pin
     *
     * @return string
     */
    private function generateVerifyCode($user_id, $pin = null)
    {
        $vf = new VerifyCode();

        $vf->user_id = $user_id;
        $vf->expired_time = date('Y-m-d H:i:s', time()+86400);
        $vf->verify_code = $pin ? $pin : Security::generatePin();
        $vf->is_used = 0;

        $vf->save(false);

        return $vf->verify_code;
    }

    /**
     * @return int time to reserve in seconds
     */
    public function getTtr()
    {
        return 1 * 60; // 1 minute
    }

    /**
     * @param int $attempt number
     * @param \Exception $error from last execute of the job
     *
     * @return bool
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < 3) && ($error instanceof \Swift_TransportException);

    }
}
