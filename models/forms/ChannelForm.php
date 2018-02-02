<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 27.11.17
 * Time: 14:37
 */

namespace app\models\forms;

use app\constants\Actions;
use app\constants\Errors;
use app\helpers\Security;
use app\models\User;
use app\modules\v1\models\Channel;
use app\modules\v1\models\HerbChannel;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ChannelForm extends Model
{
    public $newRecord = false;

    public $id;

    public $english_name;
    public $chinese_name;

    public $herbs;

    protected $_relations = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['english_name', 'chinese_name'], 'required'],
            [['english_name', 'chinese_name'], 'string', 'max' => 500],
            [['herbs'], 'safe']
        ];
    }

    /**
     * Save channel and create/update related records
     *
     * @return bool
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $model = new Channel();

        $related = [];

        if ($this->id)
            $model = $this->loadChannel($this->id, Actions::UPDATE);

        $transaction = Channel::getDb()->beginTransaction();

        try {

            foreach ($this->attributes as $attribute => $value) {

                if (!$model->hasProperty($attribute) && !in_array($attribute, $model->extraFields()))
                    continue;

                if(in_array($attribute, $model->extraFields())) {

                    $related[$attribute] = $value;
                    $this->_relations[] = $attribute;

                } else {

                    if(!in_array($attribute, $this->getUnsafeAttributes()))
                        $model->$attribute = $value;
                }
            }

            $this->newRecord = (boolean) !$model->id;

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->id = $model->id;

            if (sizeof($related) > 0) {

                foreach ($related as $attribute => $value) {

                    switch ($attribute) {

                        case 'herbs':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbChannel::find()
                                    ->where(['channel_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbChannel $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();
                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new herb

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign herb without ID');

                                } else {

                                    $_relation = HerbChannel::findOne(['channel_id' => $model->id, 'herb_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->herb_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbChannel();

                                        $_relation->channel_id = $model->id;
                                        $_relation->herb_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->herb_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbChannel::find()
                                ->where(['channel_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'herb_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbChannel $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();
                                }
                            }

                            break;
                    }
                }
            }

            if($this->hasErrors()) {
                $transaction->rollBack();
                return false;
            }

            $model->modified_time = new Expression('NOW()');
            $model->save(false);

            $transaction->commit();
            return true;

        } catch (Exception $exception) {

            $transaction->rollBack();
            $this->addError('id', $exception->getMessage());
            return false;
        }

    }

    /**
     * Get model relations
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->_relations;
    }

    /**
     * Get unsafe attributes
     *
     * @return array
     */
    public function getUnsafeAttributes()
    {
        return ['created_by', 'modified_by', 'created_time', 'modified_time', 'is_deleted'];
    }

    /**
     * Clean unsafe attributes
     *
     * @param array $data
     *
     * @return array
     */
    private function cleanUnsafeAttributes($data)
    {
        foreach ($data as $key => $value) {
            if(in_array($key, $this->getUnsafeAttributes()))
                unset($data[$key]);
        }

        return $data;
    }

    /**
     * Load channel model
     *
     * @param integer|null $id
     * @param string $action
     *
     * @return Channel
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadChannel($id, $action = Actions::VIEW)
    {
        $model = Channel::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Channel not found', Errors::CHANNEL_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !Security::isAdmin())
                throw new ForbiddenHttpException('Caution updating is forbidden', Errors::CHANNEL_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && (\Yii::$app->user->isGuest || !Security::isAdmin()))
                throw new ForbiddenHttpException('Caution viewing is forbidden', Errors::CHANNEL_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
}
