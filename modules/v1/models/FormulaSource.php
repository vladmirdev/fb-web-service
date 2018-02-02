<?php

namespace app\modules\v1\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "formula_source".
 *
 * @property integer $id
 * @property integer $formula_id
 * @property integer $source_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Formula $formula
 * @property Source $source
 */
class FormulaSource extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'formula_source';
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
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['formula_id', 'source_id'], 'required'],
            [['formula_id', 'source_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
            [['formula_id'], 'exist', 'skipOnError' => false, 'targetClass' => Formula::className(), 'targetAttribute' => ['formula_id' => 'id']],
            [['source_id'], 'exist', 'skipOnError' => false, 'targetClass' => Book::className(), 'targetAttribute' => ['source_id' => 'id']],

            [['formula_id'], function ($attribute, $params) {

                $model = Formula::findOne(['id' => $this->formula_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted formula');
            }],

            [['source_id'], function ($attribute, $params) {

                $model = Book::findOne(['id' => $this->source_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted source');
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return ['id', 'formula_id', 'source_id', 'is_deleted'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'formula_id' => 'Formula ID',
            'source_id' => 'Source ID',
            'is_deleted' => 'Is Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormula()
    {
        return $this->hasOne(Formula::className(), ['id' => 'formula_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Book::className(), ['id' => 'source_id']);
    }
}