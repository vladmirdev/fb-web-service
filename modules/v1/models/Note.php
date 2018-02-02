<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 03.10.17
 * Time: 12:04
 */

namespace app\modules\v1\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "note".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property FormulaNote[] $formulaNotes
 * @property HerbNote[] $herbNotes
 */
class Note extends ActiveRecord
{
    const ITEM_TYPE = 'note';

    public $formula_note_id;
    public $herb_note_id;

    public $formula_id;
    public $herb_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note';
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
                'value' => \Yii::$app->user->getId()
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $action = \Yii::$app->controller->action->uniqueId;

        if($action == 'v1/formula/notes')
            return ['id', 'title', 'content', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'formula_note_id'];
        elseif($action == 'v1/herb/notes')
            return ['id', 'title', 'content', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'herb_note_id'];
        else
            return ['id', 'title', 'content', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'formula_id', 'herb_id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time', 'formula_id', 'herb_id'], 'safe'],
            [['title'], 'string', 'max' => 100],
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
            'title' => 'Title',
            'content' => 'Content',
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
    public function getFormulaNotes()
    {
        return $this->hasMany(FormulaNote::className(), ['note_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHerbNotes()
    {
        return $this->hasMany(HerbNote::className(), ['note_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        if($insert) {

            if ($this->formula_id) {

                $model = new FormulaNote();

                $model->formula_id = $this->formula_id;
                $model->note_id = $this->id;

                $model->save();

                $this->formula_note_id = $model->id;

            } elseif ($this->herb_id) {

                $model = new HerbNote();

                $model->herb_id = $this->herb_id;
                $model->note_id = $this->id;

                $model->save();

                $this->herb_note_id = $model->id;
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        if($parent = FormulaNote::findOne(['note_id' => $this->id, 'created_by' => $this->created_by, 'is_deleted' => 0]))
            $this->formula_id = $parent->formula_id;
        elseif($parent = HerbNote::findOne(['note_id' => $this->id, 'created_by' => $this->created_by, 'is_deleted' => 0]))
            $this->herb_id = $parent->herb_id;
    }
}
