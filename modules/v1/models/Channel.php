<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "channel".
 *
 * @property integer $id
 * @property string $icon_name
 * @property string $chinese_name
 * @property string $english_name
 * @property integer $is_readonly
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Channel extends ActiveRecord
{
    const ITEM_TYPE = 'channel';

	public $herb_channel_id;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'channel';
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
                'updatedAtAttribute' => 'modified_time',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'modified_by',
                'value' => Security::getAuthor()
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $action = \Yii::$app->controller->action->uniqueId;

    	if($action == 'v1/herb/channels')
        	return ['id','icon_name','english_name','chinese_name','is_deleted','created_by','created_time','modified_by','modified_time','herb_channel_id'];
    	else
    		return ['id','icon_name','english_name','chinese_name','is_deleted','created_by','created_time','modified_by','modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chinese_name', 'english_name'], 'required'],
            [['is_readonly', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['icon_name'], 'string', 'max' => 50],
            [['chinese_name', 'english_name'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'icon_name' => 'Icon Name',
            'chinese_name' => 'Chinese Name',
            'english_name' => 'English Name',
            'is_readonly' => 'Read Only',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'createdBy',
            'modifiedBy'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'modified_by']);
    }
}