<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:45
 */

namespace app\commands;

use app\modules\v1\models\Herb;
use yii\console\Controller;

/**
 * Data fix controller
 *
 * @package app\commands
 */
class FixController extends Controller
{
    /**
     * Fixes herb english commons
     */
    public function actionHerbEnglishCommons()
    {
        /** @var Herb[] $models */
        $models = Herb::find()->where(['english_name' => NULL])->all();

        /** @var Herb $model */
        foreach($models as $model)
        {
            $englishCommons = $model->englishCommons;

            echo sprintf('Got herb %d with empty english name and %d english commons', $model->id, sizeof($englishCommons)), PHP_EOL;

            if(sizeof($englishCommons) == 0) {
                echo 'Skip empty record', PHP_EOL;
                continue;
            }

            $model->english_name = $englishCommons[0]->name;

            echo sprintf('Save new english name as %s', $model->english_name), PHP_EOL;

            $model->save(false);
        }
    }

    /**
     * Fixes herb latin names
     */
    public function actionHerbLatinNames()
    {
        /** @var Herb[] $models */
        $models = Herb::find()->where(['latin_name' => NULL])->all();

        /** @var Herb $model */
        foreach($models as $model)
        {
            $latinNames = $model->latinNames;

            echo sprintf('Got herb %d with empty latin name and %d related latin names', $model->id, sizeof($latinNames)), PHP_EOL;

            if(sizeof($latinNames) == 0) {
                echo 'Skip empty record', PHP_EOL;
                continue;
            }

            $model->latin_name = $latinNames[0]->name;

            echo sprintf('Save new latin name as %s', $model->latin_name), PHP_EOL;

            $model->save(false);
        }
    }

}