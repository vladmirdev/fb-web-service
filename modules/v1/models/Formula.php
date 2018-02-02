<?php

namespace app\modules\v1\models;

use app\constants\Roles;
use app\helpers\Converter;
use app\helpers\Security;
use app\traits\Filtered;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\rbac\Role;

/**
 * This is the model class for table "formulas".
 *
 * @property integer $id
 * @property string $name
 * @property string $pinyin
 * @property string $pinyin_ton
 * @property string $pinyin_code
 * @property string $english_name
 * @property string $simplified_chinese
 * @property string $traditional_chinese
 * @property integer $is_favorite
 * @property integer $is_readonly
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Category[] $categories
 * @property Herb[] $herbs
 * @property Source[] $sources
 * @property Preparation[] $preparations
 * @property Activity[] $activities
 * @property Note[] $notes
 * @property Action[] $actions
 * @property Symptom[] $symptoms
 *
 * @property \app\models\User $createdBy
 * @property \app\models\User $modifiedBy
 */
class Formula extends ActiveRecord
{
    const ITEM_TYPE = 'formula';

    public $herb_formula_id;

    public $is_favorite = 0;
    public $is_readonly = 1;

    use Filtered;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'formulas';
	}

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pinyin'], 'required'],
            [['is_favorite', 'is_readonly', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time', 'modified_by'], 'safe'],
            [['name', 'pinyin', 'pinyin_ton', 'pinyin_code', 'english_name', 'simplified_chinese', 'traditional_chinese'], 'string', 'max' => 255],
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
            ],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $action = \Yii::$app->controller->action->uniqueId;

        if($action == 'v1/herb/formulas')
            return ['id', 'name', 'pinyin', 'pinyin_ton', 'pinyin_code', 'english_name', 'simplified_chinese', 'traditional_chinese', 'created_by', 'created_time', 'modified_by', 'modified_time', 'herb_formula_id', 'is_favorite', 'is_readonly', 'is_deleted'];
        elseif($action == 'v1/category/search' || $action == 'v1/category/index')
            return ['id'];
        elseif($action == 'v1/sync/pullchanges') {

            return [
                'id',
                'name',
                'pinyin',
                'pinyin_ton',
                'pinyin_code',
                'english_name',
                'simplified_chinese',
                'traditional_chinese',
                'created_by',
                'created_time',
                'modified_by',
                'modified_time',
                'is_favorite' => function($model) {

                    $favorite = FormulaFavorites::findOne(['formula_id' => $model->id, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

                    if($favorite)
                        return 1;

                    return 0;
                },
                'is_readonly' => function($model) {

                    if($model->created_by == \Yii::$app->user->getId() || Security::isAdmin())
                        return 0;

                    return 1;
                },
                'is_deleted'
            ];
        }
        else
            return [
                'id',
                'name',
                'pinyin',
                'pinyin_ton',
                'pinyin_code',
                'english_name',
                'simplified_chinese',
                'traditional_chinese',
                'created_by',
                'created_time',
                'modified_by',
                'modified_time',
                'is_favorite',
                'is_readonly',
                'is_deleted',
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
            'pinyin' => 'Pinyin',
            'pinyin_ton' => 'Pinyin Ton',
            'pinyin_code' => 'Pinyin Code',
            'english_name' => 'English Name',
            'simplified_chinese' => 'Simplified Chinese',
            'traditional_chinese' => 'Traditional Chinese',
            'is_favorite' => 'Is Favorite',
            'is_readonly' => 'Is Readonly',
            'is_deleted' => 'Is Deleted',
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
            'herbs',
            'preparations',
            'sources',
            'activities',
            'notes',
            'actions',
            'symptoms',
            'createdBy',
            'modifiedBy'
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCategories()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Category::className(), ['id' => 'category_id'])
            ->andWhere($conditions)
            ->viaTable('formula_category fc', ['formula_id' => 'id'],
                function($query) use ($conditions) {
                    /** @var ActiveQuery $query */
                    $query->andWhere($conditions);
                }
            )->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getNotes()
    {
        $condition = [
            'created_by' => \Yii::$app->user->getId(),
            'is_deleted' => 0
        ];

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Note::className(), ['id' => 'note_id'])->andWhere($condition)
            ->viaTable('formula_note', ['formula_id' => 'id'], function($query) use ($condition) {
                /** @var ActiveQuery $query */
                $query->andWhere($condition);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getHerbs()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Herb::className(), ['id' => 'herb_id'])
            ->andWhere($conditions)
            ->viaTable('formula_herb fh', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }
    /**
     * @return ActiveQuery
     */
    public function getSources()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Book::className(), ['id' => 'source_id'])
            ->andWhere($conditions)
            ->viaTable('formula_source fs', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getPreparations()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Preparation::className(), ['id' => 'prep_id'])
            ->andWhere($conditions)
            ->viaTable('formula_preparation fp', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getActivities()
    {
        $conditions = [
            'created_by' => \Yii::$app->user->getId(),
        ];

        if(Security::isAdmin())
            unset($conditions['created_by']);

        return $this->hasMany(Activity::className(), ['id' => 'activity_id'])
            ->andWhere($conditions)
            ->viaTable('formula_activity fa', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
        });
    }

    /**
     * @return ActiveQuery
     */
    public function getActions()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Action::className(), ['id' => 'action_id'])
            ->andWhere($conditions)
            ->viaTable('formula_action foa', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getSymptoms()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Symptom::className(), ['id' => 'symptom_id'])
            ->andWhere($conditions)
            ->viaTable('formula_symptom fos', ['formula_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
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
     * @param bool $insert
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        if($this->pinyin) {
            $this->name = mb_convert_case($this->pinyin, MB_CASE_TITLE, 'utf-8');
            $this->pinyin_code = Converter::toPinyinCode($this->pinyin);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        $favorite = FormulaFavorites::findOne(['formula_id' => $this->id, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

        if($favorite)
            $this->is_favorite = 1;

        $this->is_readonly = 1;

        if($this->created_by == \Yii::$app->user->getId() || Security::isAdmin())
            $this->is_readonly = 0;
    }
}
