<?php
namespace app\modules\v1\models;

use app\constants\Actions;
use app\helpers\Security;
use app\traits\Tracked;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "symptom".
 *
 * @property integer $id
 * @property string $name
 * @property string $simplified_chinese
 * @property string $traditional_chinese
 * @property string $color
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Category[] $categories
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Symptom extends ActiveRecord
{
    use Tracked;

    const ITEM_TYPE = 'symptom';

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'symptom';
	}

    /**
     * @return array
     */
    public function fields()
    {
    	return ['id', 'name', 'simplified_chinese', 'traditional_chinese', 'color', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['name', 'simplified_chinese', 'traditional_chinese'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 7],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
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
                'value' => Security::getAuthor()
            ]
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
            'simplified_chinese' => 'Simplified chinese',
            'traditional_chinese' => 'Traditional chinese',
            'color' => 'Color',
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
            'formulas',
            'herbs',
            'createdBy',
            'modifiedBy'
        ];
    }

    /**
     * Get tracked attributes list
     *
     * @param array|null $lookupAttributes
     *
     * @return array
     */
    public function attributesLookup($lookupAttributes = null)
    {
        if(is_array($lookupAttributes))
            return $lookupAttributes;

        return [
            'name'
        ];
    }

    /**
     * Get attribute related value
     *
     * @param $attribute
     * @param int|string|null $value
     *
     * @return mixed
     */
    public function attributesRelation($attribute, $value = null)
    {
        // TODO: Implement attributesRelation() method.
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {

            $changes = $this->diff($changedAttributes, $this->oldAttributes);

            if (!empty($changes)) {
                Activity::store(Symptom::ITEM_TYPE, ['id' => $this->id, 'changes' => $changes], $this->is_deleted ? Actions::DELETE : Actions::UPDATE);
            }

        } else {
            Activity::store(Symptom::ITEM_TYPE, $this->id, Actions::CREATE);
        }
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
    public function getCategories()
    {
        $condition = [
            'created_by' => [\app\models\User::SYSTEM, \Yii::$app->user->getId()],
            'is_deleted' => 0
        ];

        return $this->hasMany(Category::className(), ['id' => 'category_id'])
            ->andWhere($condition)
            ->viaTable('symptom_category sc', ['symptom_id' => 'id'],
                function($query) use ($condition) {
                    /** @var \yii\db\ActiveQuery $query */
                    $query->andWhere($condition);
                }
            );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormulas()
    {
        $condition = [
            'created_by' => [\app\models\User::SYSTEM, \Yii::$app->user->getId()],
            'is_deleted' => 0
        ];

        if(Security::isAdmin())
            $condition['created_by'] = \app\models\User::SYSTEM;

        return $this->hasMany(Formula::className(), ['id' => 'formula_id'])
            ->andWhere($condition)
            ->viaTable('formula_symptom ac', ['symptom_id' => 'id'],
                function($query) use ($condition) {
                    /** @var \yii\db\ActiveQuery $query */
                    $query->andWhere($condition);
                }
            )->select(['id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHerbs()
    {
        $condition = [
            'created_by' => [\app\models\User::SYSTEM, \Yii::$app->user->getId()],
            'is_deleted' => 0
        ];

        if(Security::isAdmin())
            $condition['created_by'] = \app\models\User::SYSTEM;

        return $this->hasMany(Herb::className(), ['id' => 'herb_id'])
            ->andWhere($condition)
            ->viaTable('herb_symptom ac', ['symptom_id' => 'id'],
                function($query) use ($condition) {
                    /** @var \yii\db\ActiveQuery $query */
                    $query->andWhere($condition);
                }
            )->select(['id']);
    }

}