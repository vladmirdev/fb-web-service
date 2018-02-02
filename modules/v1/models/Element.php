<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "element".
 *
 * @property integer $id
 * @property string $syndrome
 * @property string $chinese_simplified
 * @property string $chinese_traditional
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 */
class Element extends ActiveRecord
{
    const ITEM_TYPE = 'element';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'element';
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
     * @return array
     */
    public function fields()
    {
    	return ['id', 'syndrome', 'chinese_simplified', 'chinese_traditional', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['syndrome', 'chinese_simplified', 'chinese_traditional'], 'string', 'max' => 255],
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
            'syndrome' => 'Syndrome',
            'chinese_simplified' => 'Chinese Simplified',
            'chinese_traditional' => 'Chinese Traditional',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }
}
