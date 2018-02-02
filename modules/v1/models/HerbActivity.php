<?php

namespace app\modules\v1\models;

use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "herb_activity".
 *
 * @property integer $id
 * @property integer $herb_id
 * @property integer $activity_id
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 */
class HerbActivity extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'herb_activity';
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['herb_id', 'activity_id', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
        ];
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
     * @return array
     */
    public function fields()
    {
        return parent::fields();
    }
}