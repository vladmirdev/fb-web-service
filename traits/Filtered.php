<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 05.11.17
 * Time: 0:23
 */

namespace app\traits;

use app\constants\Roles;
use app\helpers\Security;
use app\models\User;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

trait Filtered
{
    /**
     * Build data provider conditions
     *
     * @param string $default Default filter value
     * @param string $suffix Table suffix
     *
     * @return array
     */
    public function prepareFilter($default = 'all', $suffix = '')
    {
        $conditions = [
            'is_deleted' => 0
        ];

        if($suffix && strpos($suffix, '.') === false)
            $suffix = $suffix . '.';

        $filter = \Yii::$app->request->get('filter', $default);

        if($filter == 'own' || $filter == 'self')
            $conditions["{$suffix}created_by"] = [\Yii::$app->user->getId()];
        elseif($filter == 'all')
            $conditions["{$suffix}created_by"] = [User::SYSTEM, \Yii::$app->user->getId()];
        else
            $conditions["{$suffix}created_by"] = [User::SYSTEM];

        if(Security::isAdmin())
            $conditions["{$suffix}created_by"] = [User::SYSTEM];

        return $conditions;
    }

    /**
     * Build relation conditions
     *
     * @param string $suffix Table suffix
     *
     * @return array
     */
    public function prepareRelationFilter($suffix = '')
    {

        if($suffix && strpos($suffix, '.') === false)
            $suffix = $suffix . '.';

        $conditions = [
            "{$suffix}is_deleted" => 0
        ];

        $conditions["created_by"] = [User::SYSTEM, \Yii::$app->user->getId()];

        return $conditions;
    }
}