<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use app\modules\v1\models\BookChapter;
use yii\db\Expression;

/**
 * This is the model class for table "book".
 *
 * @property integer $id
 * @property string $english_name
 * @property string $chinese_name
 * @property string $author
 * @property string $chinese_author
 * @property string $year
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property BookChapter[] $chapters
 * @property BookPage[] $pages
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Book extends ActiveRecord
{
    const ITEM_TYPE = 'book';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'book';
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
    	return ['id', 'english_name','chinese_name','author', 'chinese_author', 'year','is_deleted','created_by','created_time','modified_by','modified_time'];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'chapters',
            'createdBy',
            'modifiedBy'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['english_name', 'chinese_name', 'author', 'chinese_author'], 'string', 'max' => 255],
            [['year'], 'string', 'max' => 4],
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
            'english_name' => 'English Name',
            'chinese_name' => 'Chinese Name',
            'author' => 'Author',
            'chinese_author' => 'Chinese author',
            'year' => 'Year',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getChapters()
    {
        return $this->hasMany(BookChapter::className(), ['book_id' => 'id'])
                    ->where(['is_deleted' => 0]);
    }

    /**
     * @return ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(BookPage::className(), ['book_id' => 'id'])
            ->where(['is_deleted' => 0]);
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
