<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 12.12.2017
 * Time: 13:33
 */

namespace app\constants;

use app\modules\v1\models\Action;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Formula;
use app\modules\v1\models\Herb;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Symptom;

class Types
{
    const TYPE_FORMULA      = Formula::ITEM_TYPE;
    const TYPE_HERB         = Herb::ITEM_TYPE;
    const TYPE_ACTION       = Action::ITEM_TYPE;
    const TYPE_SYMPTOM      = Symptom::ITEM_TYPE;
    const TYPE_PREPARATION  = Preparation::ITEM_TYPE;
    const TYPE_CAUTION      = Caution::ITEM_TYPE;

    const TYPES = [
        self::TYPE_FORMULA,
        self::TYPE_HERB,
        self::TYPE_SYMPTOM,
        self::TYPE_ACTION,
        self::TYPE_PREPARATION,
        self::TYPE_CAUTION
    ];
}