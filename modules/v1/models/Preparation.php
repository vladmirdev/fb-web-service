<?php

namespace app\modules\v1\models;

use app\helpers\Security;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "preparation".
 *
 * @property integer $id
 * @property string $name
 * @property string $alternate_name
 * @property string $type
 * @property string $method
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property FormulaPreparation[] $formulaPreparations
 * @property Category[] $categories
 *
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Preparation extends ActiveRecord
{
    const ITEM_TYPE = 'preparation';

	public $formula_prep_id;
	public $herb_prep_id;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'preparation';
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
    	$action = \Yii::$app->controller->action->uniqueId;

    	if($action == 'v1/formula/preparations')
        	return ['id', 'name', 'alternate_name', 'method', 'type', 'is_deleted','created_by','created_time','modified_by','modified_time','formula_prep_id'];
        elseif($action == 'v1/herb/preparations')
        	return ['id', 'name', 'alternate_name', 'method', 'type', 'is_deleted','created_by','created_time','modified_by','modified_time','herb_prep_id'];
        else
        	return ['id', 'name', 'alternate_name', 'method', 'type', 'is_deleted','created_by','created_time','modified_by','modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['method'], 'string'],
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['name', 'alternate_name'], 'string', 'max' => 100],
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
            'name' => 'Name',
            'alternate_name' => 'Alternate Name',
            'type' => 'Type',
            'method' => 'Method',
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
    public function extraFields()
    {
        return [
            'categories',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormulaPreparations()
    {
        return $this->hasMany(FormulaPreparation::className(), ['prep_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        $condition = [
            'created_by' => [\app\models\User::SYSTEM, \Yii::$app->user->getId()],
            'is_deleted' => 0
        ];

        return $this->hasMany(Category::className(), ['id' => 'category_id'])
            ->andWhere($condition)
            ->viaTable('preparation_category pc', ['prep_id' => 'id'],
                function($query) use ($condition) {
                    /** @var \yii\db\ActiveQuery $query */
                    $query->andWhere($condition);
                }
            );
    }
}