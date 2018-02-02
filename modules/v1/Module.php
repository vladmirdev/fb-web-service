<?php

namespace app\modules\v1;

use app\constants\Roles;

/**
 * Class Module
 * @package api\modules\v1
 */
class Module extends \yii\base\Module
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;

        $this->params['isAdmin'] = false;
        $this->params['isGuest'] = true;

    }
}
