<?php

namespace app\models;

use app\modules\v1\models\Book;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaActivity;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbActivity;
use app\modules\v1\models\Symptom;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $type
 * @property integer $obj_id
 * @property string $action
 * @property integer $created_by
 * @property string $created_time
 *
 * @property User $user
 */
class Activity extends ActiveRecord
{
    const ITEM_TYPE = 'activity';

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    public $message;
    public $relative_date;

    /**
     * @return string
     */
	public static function tableName()
	{
		return 'activity';
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'obj_id'], 'integer'],
            [['created_time'], 'safe'],
            [['action', 'type'], 'string', 'max' => 200],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return ['id', 'created_by', 'type', 'obj_id', 'action', 'created_time', 'message', 'relative_date'];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_time',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => null
            ],
        ];
    }

    /**
     * Store activity
     *
     * @param string $objectType
     * @param integer|array $objectId
     * @param string $action
     * @param integer|null $userId
     *
     * @return bool
     */
    public static function store($objectType, $objectId, $action, $userId = null)
    {
        $event = new Activity();
        $changes = [];

        if(is_int($objectId))
            $event->obj_id = $objectId;
        elseif(is_array($objectId)) {
            $event->obj_id = $objectId['id'];
            $changes = $objectId['changes'];
        } else
            return false;

        $event->type = $objectType;
        $event->action = $action;

        if($userId)
            $event->created_by = $userId;
        elseif(!\Yii::$app->user->isGuest)
            $event->created_by = \Yii::$app->user->identity->getId();

        $result = $event->save();

        switch ($event->type) {

            case Herb::ITEM_TYPE:

                $relatedEvent = new HerbActivity();

                $relatedEvent->activity_id = $event->id;
                $relatedEvent->herb_id = $event->obj_id;
                $relatedEvent->created_by = $event->created_by;

                // set other parameters
                // save
                // $relatedEvent->save();

                break;

            case Symptom::ITEM_TYPE:

                if(sizeof($changes) > 0) {
                    // @todo store events
                }

                break;

            case Formula::ITEM_TYPE:

                $relatedEvent = new FormulaActivity();

                $relatedEvent->activity_id = $event->id;
                $relatedEvent->formula_id = $event->obj_id;
                $relatedEvent->created_by = $event->created_by;

                // set other parameters
                // save
                $relatedEvent->save();

                break;
        }

        return $result;
    }


    /**
     * Get activity message
     *
     * @param Activity $activity
     * @param bool $own
     *
     * @return string
     */
    public static function message(Activity $activity, $own = false)
    {
        $message = '%s %s by %s';

        if($own)
            $message = '%s %s';

        $action = '';

        if($activity->action == self::ACTION_CREATE) {
            $action = 'created';
        } elseif($activity->action == self::ACTION_UPDATE) {
            $action = 'updated';
        } elseif($activity->action == self::ACTION_DELETE) {
            $action = 'deleted';
        }

        switch ($activity->type) {
            default:
                if($own)
                    $message = sprintf($message, mb_convert_case($activity->type, MB_CASE_TITLE), $action);
                else
                    $message = sprintf($message, mb_convert_case($activity->type, MB_CASE_TITLE), $action, ($activity->user ? ($activity->user->firstname . ' ' . $activity->user->lastname) : '-'));
                break;
        }

        return $message;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        $object = null;

        switch ($this->type) {
            case Herb::ITEM_TYPE:
                return $this->hasOne(Herb::className(), ['id' => 'obj_id']);
                break;
            case Formula::ITEM_TYPE:
                return $this->hasOne(Formula::className(), ['id' => 'obj_id']);
                break;
        }

        return $object;
    }

        /**
     * @return array
     */
    public function extraFields()
    {
        return ['user', 'object'];
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->message = self::message($this);
        $this->relative_date = \Yii::$app->formatter->asRelativeTime($this->created_time);
    }
}