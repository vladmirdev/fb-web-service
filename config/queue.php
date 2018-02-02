<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:35
 */

return [
    'class' => \yii\queue\db\Queue::class,
    'as log' => \yii\queue\LogBehavior::class,
    'db' => 'db', // DB connection component or its config
    'tableName' => '{{%queue}}', // Table name
    'channel' => 'default', // Queue channel key
    'mutex' => \yii\mutex\MysqlMutex::class, // Mutex that used to sync queries,
    'ttr' => 5 * 60, // Max time for anything job handling
    'attempts' => 3, // Max number of attempts
];