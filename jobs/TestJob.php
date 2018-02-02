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

class TestJob extends Object implements \yii\queue\Job
{
    public $message;
    public $datetime = null;

    /**
     * Execute test job
     *
     * @param Queue $queue which pushed and is handling the job
     *
     * @return bool
     */
    public function execute($queue)
    {
        echo $this->message;

        if($this->datetime)
            echo $this->datetime;

        echo PHP_EOL;

        return true;
    }
}
