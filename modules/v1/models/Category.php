<?php

namespace app\modules\v1\models;

use app\constants\Types;
use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property integer $is_readonly
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Formula[] $formulas
 * @property Herb[] $herbs
 * @property Action[] $actions
 * @property Preparation[] $preparations
 * @property Symptom[] $symptoms
 * @property Caution[] $cautions
 *
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Category extends ActiveRecord
{
    const ITEM_TYPE = 'category';

    public $formula_category_id;
    public $herb_category_id;
    public $action_category_id;
    public $caution_category_id;
    public $symptom_category_id;
    public $preparation_category_id;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'category';
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
            [['name', 'type'], 'required'],
            [['created_time', 'modified_time'], 'safe'],
            [['is_readonly', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
            [['color'], 'string', 'max' => 7],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],

            [['type'], function ($attribute, $params) {

                if(!in_array($this->type, Types::TYPES))
                    $this->addError($attribute, sprintf('Unknown category type "%s"', $this->type));
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
            'name' => 'Name',
            'type' => 'Type',
            'color' => 'Color',
            'is_readonly' => 'Read Only',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $action = \Yii::$app->controller->action->uniqueId;
        
        if($action == 'v1/formula/categories')
            return ['id', 'name', 'color', 'type', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'formula_category_id'];
        elseif($action == 'v1/herb/categories')
            return ['id', 'name', 'color', 'type', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'herb_category_id'];
        elseif($action == 'v1/action/categories')
            return ['id', 'name', 'color', 'type','is_deleted','created_by','created_time', 'modified_by', 'modified_time', 'action_category_id'];
        elseif($action == 'v1/preparation/categories')
            return ['id', 'name', 'color', 'type','is_deleted','created_by', 'created_time', 'modified_by', 'modified_time', 'preparation_category_id'];
        elseif($action == 'v1/symptom/categories')
            return ['id', 'name', 'color', 'type','is_deleted','created_by', 'created_time', 'modified_by', 'modified_time', 'symptom_category_id'];
        elseif($action == 'v1/caution/categories')
            return ['id', 'name', 'color', 'type', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'caution_category_id'];
        else
            return ['id', 'name', 'color', 'type', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'formulas',
            'herbs',
            'actions',
            'preparations',
            'symptoms',
            'cautions',
            'createdBy',
            'modifiedBy'
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getFormulas()
    {
        return $this->hasMany(Formula::className(), ['id' => 'formula_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('formula_category', ['category_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getHerbs()
    {
        return $this->hasMany(Herb::className(), ['id' => 'herb_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('herb_category', ['category_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getActions()
    {
        return $this->hasMany(Action::className(), ['id' => 'action_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('action_category', ['category_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPreparations()
    {
        return $this->hasMany(Preparation::className(), ['id' => 'prep_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('preparation_category', ['category_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSymptoms()
    {
        return $this->hasMany(Symptom::className(), ['id' => 'symptom_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('symptom_category', ['category_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCautions()
    {
        return $this->hasMany(Caution::className(), ['id' => 'caution_id'])
            ->andWhere(['is_deleted' => 0])
            ->viaTable('caution_category', ['category_id' => 'id']);
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
