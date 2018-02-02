<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 10.10.17
 * Time: 10:31
 */

namespace app\jobs;

use app\constants\Errors;
use app\models\Device;
use app\models\Token;
use app\models\User;
use app\modules\v1\models\Category;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Channel;
use app\modules\v1\models\Cultivation;
use app\modules\v1\models\Flavour;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaFavorites;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbFavorites;
use app\modules\v1\models\Nature;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Source;
use app\modules\v1\models\Species;
use yii\base\Object;
use yii\queue\Queue;
use yii\web\NotFoundHttpException;

class UserDeleteJob extends Object implements \yii\queue\Job
{
    public $userId;
    public $full = true;

    /**
     * Clean up user data
     *
     * @param Queue $queue which pushed and is handling the job
     *
     * @return void
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {

        $user = User::findOne(['id' => $this->userId]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $transaction = User::getDb()->beginTransaction();

        try {

            // Clean up user tokens

            Token::updateAll(['is_deleted' => 1, 'timeout' => time()], ['created_by' => $this->userId, 'is_deleted' => 0]);

            Formula::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Herb::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Category::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Caution::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Cultivation::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Channel::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Flavour::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Nature::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Preparation::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Source::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            Species::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);

            if($this->full) {
                Device::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
                HerbFavorites::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
                FormulaFavorites::updateAll(['is_deleted' => 1], ['created_by' => $this->userId, 'is_deleted' => 0]);
            }

            $transaction->commit();

        } catch (\Exception $ex) {

            $transaction->rollBack();

            \Yii::error($ex);

            throw $ex;
        }
    }
}
