<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "source".
 *
 * @property integer $id
 * @property string $date
 * @property string $author
 * @property string $english_name
 * @property string $chinese_name
 * @property string $type
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Source extends ActiveRecord
{
    const ITEM_TYPE = 'source';

	public $formula_source_id;
	public $herb_source_id;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'source';
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
    	$action = \Yii::$app->controller->action->uniqueId;

    	if($action == 'v1/formula/sources')
        	return ['id','date','author','english_name','chinese_name','is_deleted','created_by','created_time','modified_by','modified_time','formula_source_id'];
        elseif($action == 'v1/herb/sources')
        	return ['id','date','author','english_name','chinese_name','is_deleted','created_by','created_time','modified_by','modified_time','herb_source_id'];
        else
        	return ['id','date','author','english_name','chinese_name','is_deleted','created_by','created_time','modified_by','modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['date', 'author'], 'string', 'max' => 50],
            [['english_name', 'chinese_name'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 20],
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
            'date' => 'Date',
            'author' => 'Author',
            'english_name' => 'English Name',
            'chinese_name' => 'Chinese Name',
            'type' => 'Type',
            'is_deleted' => 'Is Deleted',
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
