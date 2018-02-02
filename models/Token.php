<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "token".
 *
 * @property integer $id
 * @property integer $platform_id
 * @property string $access_token
 * @property integer $user_id
 * @property integer $is_deleted
 * @property integer $is_active
 * @property integer $timeout
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property DevicePlatform $platform
 */
class Token extends ActiveRecord
{
    const ITEM_TYPE = 'token';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'token';
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
            ]
        ];
    }

    /**
     * @inheritdoc
     */
	public function rules()
    {
        return [
            [['access_token', 'user_id', 'timeout'], 'required'],
            [['platform_id', 'user_id', 'is_deleted', 'is_active', 'timeout', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['access_token'], 'string', 'max' => 200],
            [['access_token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
            [['platform_id'], 'exist', 'skipOnError' => true, 'targetClass' => DevicePlatform::className(), 'targetAttribute' => ['platform_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_id' => 'Platform ID',
            'access_token' => 'Access Token',
            'user_id' => 'User ID',
            'is_deleted' => 'Is Deleted',
            'is_active' => 'Is Active',
            'timeout' => 'Timeout',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(DevicePlatform::className(), ['id' => 'platform_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
    	return ['access_token'];
    }

}