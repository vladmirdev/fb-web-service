<?php

namespace app\modules\v1\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "verify_code".
 *
 * @property integer $id
 * @property string $verify_code
 * @property integer $user_id
 * @property integer $is_used
 * @property string $expired_time
 *
 * @property \app\models\User $user
 */
class VerifyCode extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'verify_code';
	}

    /**
     * @return array
     */
	public function rules()
    {
        return [
            [['verify_code', 'user_id'], 'required'],
            [['user_id', 'is_used'], 'integer'],
            [['expired_time'], 'safe'],
            [['verify_code'], 'string', 'max' => 10],
            //[['verify_code'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return parent::fields();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verify_code' => 'Verify Code',
            'user_id' => 'User ID',
            'is_used' => 'Used',
            'expired_time' => 'Expired Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
