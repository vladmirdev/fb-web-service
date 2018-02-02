<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:31
 */

namespace app\jobs;

use yii\base\Object;
use yii\queue\Queue;

class SendEmailJob extends Object implements \yii\queue\RetryableJob
{
    public $email;
    public $subject;
    public $content;

    /**
     * Send email to user
     *
     * @param Queue $queue which pushed and is handling the job
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue)
    {

        try {

            \Yii::$app->mailer->compose()
                ->setFrom([\Yii::$app->params['noreplyEmail'] => \Yii::$app->params['noreplyTitle']])
                ->setTo($this->email)
                ->setSubject($this->subject)
                ->setHtmlBody($this->content)
                ->setTextBody(strip_tags($this->content))
                ->send();

        } catch (\Exception $ex) {

            \Yii::error($ex);

            throw $ex;
        }
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
