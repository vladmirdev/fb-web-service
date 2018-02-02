<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "book_chapter".
 *
 * @property integer $id
 * @property integer $book_id
 * @property string $english_name
 * @property string $chinese_name
 * @property string $english
 * @property string $chinese
 * @property string $pinyin
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Book $book
 * @property BookPage[] $pages
 */
class BookChapter extends ActiveRecord
{
    const ITEM_TYPE = 'book_chapter';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'book_chapter';
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
    public function rules()
    {
        return [
            [['book_id'], 'required'],
            [['book_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['english_name', 'chinese_name'], 'string', 'max' => 255],
            [['english', 'chinese', 'pinyin'], 'string', 'max' => 1000],
            [['book_id'], 'exist', 'skipOnError' => false, 'targetClass' => Book::className(), 'targetAttribute' => ['book_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
    	return ['id', 'book_id', 'english_name', 'chinese_name', 'english', 'chinese', 'pinyin', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return ['pages'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'book_id' => 'Book ID',
            'english_name' => 'English Name',
            'chinese_name' => 'Chinese Name',
            'english' => 'English',
            'chinese' => 'Chinese',
            'pinyin' => 'Pinyin',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBook()
    {
        return $this->hasOne(Book::className(), ['id' => 'book_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(BookPage::className(), ['chapter_id' => 'id'])
                    ->where(['is_deleted' => 0]);
    }
}