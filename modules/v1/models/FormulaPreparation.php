<?php

namespace app\modules\v1\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "formula_preparation".
 *
 * @property integer $id
 * @property integer $formula_id
 * @property integer $prep_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Formula $formula
 * @property Preparation $prep
 */
class FormulaPreparation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'formula_preparation';
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['formula_id', 'prep_id'], 'required'],
            [['formula_id', 'prep_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['formula_id'], 'exist', 'skipOnError' => false, 'targetClass' => Formula::className(), 'targetAttribute' => ['formula_id' => 'id']],
            [['prep_id'], 'exist', 'skipOnError' => false, 'targetClass' => Preparation::className(), 'targetAttribute' => ['prep_id' => 'id']],

            [['formula_id'], function ($attribute, $params) {

                $model = Formula::findOne(['id' => $this->formula_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted formula');
            }],

            [['prep_id'], function ($attribute, $params) {

                $model = Preparation::findOne(['id' => $this->prep_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted preparation method');

                //if($model->type !== Formula::ITEM_TYPE)
                //    $this->addError($attribute, sprintf('Cannot assign preparation method with type "%s" for formulas', $model->type));
            }]
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
    public function fields()
    {
        return ['id','formula_id','prep_id','is_deleted'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'formula_id' => 'Formula ID',
            'prep_id' => 'Prep ID',
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
    public function getPrep()
    {
        return $this->hasOne(Preparation::className(), ['id' => 'prep_id']);
    }
}