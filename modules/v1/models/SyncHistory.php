<?php
namespace app\modules\v1\models;

use app\models\Device;
use \yii\db\ActiveRecord;

/**
 * This is the model class for table "sync_history".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $device_id
 * @property string $last_sync_time
 * @property string $confirm_time
 * @property string $token
 * @property integer $is_confirmed
 *
 * @property User $user
 * @property Device $device
 */
class SyncHistory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'sync_history';
	}

    /**
     * @inheritdoc
     */
	public function rules()
    {
        return [
            [['last_sync_time'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
    	return ['id', 'device_id', 'user_id', 'last_sync_time', 'confirm_time', 'token', 'is_confirmed'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'device_id' => 'Device ID',
            'last_sync_time' => 'Last Sync Time',
            'confirm_time' => 'Confirm Time',
            'token' => 'Token',
            'is_confirmed' => 'Is confirmed'
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
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

}