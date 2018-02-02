<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "formula_symptom".
 *
 * @property integer $id
 * @property integer $formula_id
 * @property integer $symptom_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Formula $formula
 * @property Symptom $symptom
 * @property User $createdBy
 * @property User $modifiedBy
 */
class FormulaSymptom extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'formula_symptom';
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
            [['formula_id', 'symptom_id'], 'required'],
            [['formula_id', 'symptom_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['formula_id'], 'exist', 'skipOnError' => false, 'targetClass' => Formula::className(), 'targetAttribute' => ['formula_id' => 'id']],
            [['symptom_id'], 'exist', 'skipOnError' => false, 'targetClass' => Symptom::className(), 'targetAttribute' => ['symptom_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],

            [['formula_id'], function ($attribute, $params) {

                $model = Formula::findOne(['id' => $this->formula_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted formula');
            }],

            [['symptom_id'], function ($attribute, $params) {

                $model = Symptom::findOne(['id' => $this->symptom_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted symptom');
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'formula_id' => 'Formula',
            'symptom_id' => 'Symptom',
            'is_deleted' => 'Is deleted',
            'created_by' => 'Created by',
            'created_time' => 'Created time',
            'modified_by' => 'Modified by',
            'modified_time' => 'Modified time',
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
    public function getSymptom()
    {
        return $this->hasOne(Symptom::className(), ['id' => 'symptom_id']);
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
