<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 04.10.17
 * Time: 16:41
 */

$db = require(__DIR__ . '/db.php');
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=localhost;dbname=formula';

return $db;