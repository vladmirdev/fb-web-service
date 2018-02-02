<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "book_page".
 *
 * @property integer $id
 * @property integer $book_id
 * @property integer $chapter_id
 * @property integer $page
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
 * @property BookChapter $chapter
 */
class BookPage extends ActiveRecord
{
    const ITEM_TYPE = 'book_page';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'book_page';
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
            [['book_id', 'chapter_id'], 'required'],
            [['book_id', 'chapter_id', 'page', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['english', 'chinese', 'pinyin'], 'string'],
            [['created_time', 'modified_time'], 'safe'],
            [['book_id'], 'exist', 'skipOnError' => true, 'targetClass' => Book::className(), 'targetAttribute' => ['book_id' => 'id']],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => BookChapter::className(), 'targetAttribute' => ['chapter_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
    	return ['id','book_id', 'chapter_id','english','chinese','pinyin','page','is_deleted','created_by','created_time','modified_by','modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'book_id' => 'Book',
            'chapter_id' => 'Chapter',
            'page' => 'Page',
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
}