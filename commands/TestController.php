<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:45
 */

namespace app\commands;

use app\jobs\SendEmailJob;
use app\jobs\TestJob;
use yii\console\Controller;

/**
 * Command test controller
 *
 * @package app\commands
 */
class TestController extends Controller
{
    /**
     * Create new test job
     */
    public function actionAddTestJob()
    {
        $id = \Yii::$app->queue->push(new TestJob([
            'message' => 'Hello world',
            'datetime' => date('Y-m-d H:i:s')
        ]));

        echo sprintf('Pushed new job with id %d', $id);
    }

    /**
     * Create new mail job
     *
     * @param string $email
     * @param string $subject
     * @param string $content
     */
    public function actionAddMailJob($email, $subject, $content)
    {
        $id = \Yii::$app->queue->push(new SendEmailJob([
            'email' => $email,
            'subject' => $subject,
            'content' => $content
        ]));

        echo sprintf('Pushed new job with id %d', $id);
    }

    /**
     * Show list tables
     */
    public function actionDatabase()
    {
        $connection = \Yii::$app->db;
        $dbSchema = $connection->schema;
        $tables = $dbSchema->tableNames;

        foreach($tables as $tbl) {
            echo $tbl, PHP_EOL;
        }
    }
}