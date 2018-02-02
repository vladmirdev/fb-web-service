<?php

namespace app\modules\v1\models;

use app\helpers\Converter;
use app\helpers\Security;
use app\traits\Filtered;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "herbs".
 *
 * @property integer $id
 * @property string $name
 * @property string $pinyin
 * @property string $pinyin_ton
 * @property string $pinyin_code
 * @property string $english_name
 * @property string $simplified_chinese
 * @property string $traditional_chinese
 * @property string $latin_name
 * @property string $english_common
 * @property string $photo
 * @property integer $is_favorite
 * @property integer $is_readonly
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Herb[] $alternates
 * @property Category[] $categories
 * @property Caution[] $cautions
 * @property Channel[] $channels
 * @property Cultivation[] $cultivations
 * @property EnglishCommon[] $englishCommons
 * @property Flavour[] $flavours
 * @property Formula[] $formulas
 * @property LatinName[] $latinNames
 * @property Nature[] $natures
 * @property Preparation[] $preparations
 * @property Source[] $sources
 * @property Species[] $species
 * @property Activity[] $activities
 * @property Note[] $notes
 * @property Action[] $actions
 * @property Symptom[] $symptoms
 *
 * @property \app\models\User $createdBy
 * @property \app\models\User $modifiedBy
 */
class Herb extends ActiveRecord
{
    public $formula_herb_id;

    public $is_favorite = 0;
    public $is_readonly = 1;

    const ITEM_TYPE = 'herb';

    use Filtered;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'herbs';
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pinyin'], 'required'],
            [['is_favorite', 'is_readonly', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['name', 'pinyin', 'pinyin_ton', 'pinyin_code', 'english_name', 'simplified_chinese', 'traditional_chinese', 'latin_name', 'english_common', 'photo'], 'string', 'max' => 255],
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
            ],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $action = \Yii::$app->controller->action->uniqueId;

        if($action == 'v1/formula/herbs')
            return ['id', 'name', 'english_name', 'latin_name', 'pinyin', 'pinyin_ton', 'pinyin_code', 'traditional_chinese', 'simplified_chinese', 'formula_herb_id', 'created_by', 'created_time', 'modified_by', 'modified_time', 'is_favorite', 'is_readonly', 'is_deleted'];
        elseif($action == 'v1/formula/index')
            return ['id'];
        elseif($action == 'v1/category/search' || $action == 'v1/category/index')
            return ['id'];
        elseif($action == 'v1/sync/pullchanges') {
            return [
                'id',
                'name',
                'english_name',
                'latin_name',
                'pinyin',
                'pinyin_ton',
                'pinyin_code',
                'traditional_chinese',
                'simplified_chinese',
                'created_by',
                'created_time',
                'modified_by',
                'modified_time',
                'is_deleted',
                'is_favorite' => function($model) {

                    $favorite = HerbFavorites::findOne(['herb_id' => $model->id, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

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
                'english_name',
                'latin_name',
                'pinyin',
                'pinyin_ton',
                'pinyin_code',
                'traditional_chinese',
                'simplified_chinese',
                'created_by',
                'created_time',
                'modified_by',
                'modified_time',
                'is_deleted',
                'is_favorite',
                'is_readonly',
                'is_deleted'
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
            'latin_name' => 'Latin Name',
            'english_common' => 'English Common',
            'photo' => 'Photo',
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
            'alternates',
            'categories',
            'cautions',
            'channels',
            'cultivations',
            'englishCommons',
            'flavours',
            'latinNames',
            'natures',
            'preparations',
            'sources',
            'species',
            'formulas',
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
    public function getAlternates()
    {
        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Herb::className(), ['id' => 'alternate_herb_id'])
            ->viaTable('herb_alternate ha', ['herb_id' => 'id'], function($query) {
                $query->andWhere(['ha.is_deleted' => 0]);
            })->select($fields);
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
            ->viaTable('herb_category hc', ['herb_id' => 'id'],
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

        return $this->hasMany(Note::className(), ['id' => 'note_id'])
            ->andWhere($condition)
            ->viaTable(HerbNote::tableName(), ['herb_id' => 'id'],
                function($query) use ($condition) {
                    /** @var ActiveQuery $query */
                    $query->andWhere($condition);
                }
            )->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getCautions()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Caution::className(), ['id' => 'caution_id'])
            ->andWhere($conditions)
            ->viaTable('herb_caution hc', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getChannels()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Channel::className(), ['id' => 'channel_id'])
            ->andWhere($conditions)
            ->viaTable('herb_channel hc', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getCultivations()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Cultivation::className(), ['id' => 'cultivation_id'])
            ->andWhere($conditions)
            ->viaTable('herb_cultivation hc', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getEnglishCommons()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        return $this->hasMany(EnglishCommon::className(), ['id' => 'english_common_id'])
            ->andWhere($conditions)
            ->viaTable('herb_english_common', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select(['id', 'name']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFlavours()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Flavour::className(), ['id' => 'flavour_id'])
            ->andWhere($conditions)
            ->viaTable('herb_flavour hf', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getFormulas()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = ['id', 'name', 'simplified_chinese', 'traditional_chinese'];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Formula::className(), ['id' => 'formula_id'])
            ->andWhere($conditions)
            ->viaTable('formula_herb fh', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })
            ->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getLatinNames()
    {
        $conditions = [
            'is_deleted' => 0
        ];

        return $this->hasMany(LatinName::className(), ['id' => 'latin_name_id'])
            ->andWhere($conditions)
            ->viaTable('herb_latin_name', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select(['id', 'name']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNatures()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Nature::className(), ['id' => 'nature_id'])
            ->andWhere($conditions)
            ->viaTable('herb_nature hn', ['herb_id' => 'id'], function($query) use ($conditions) {
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
            ->viaTable('herb_preparation hp', ['herb_id' => 'id'], function($query) use ($conditions) {
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
            ->viaTable('herb_source hs', ['herb_id' => 'id'], function($query) use ($conditions) {
                /** @var ActiveQuery $query */
                $query->andWhere($conditions);
            })->select($fields);
    }

    /**
     * @return ActiveQuery
     */
    public function getSpecies()
    {
        $conditions = $this->prepareRelationFilter();

        $action = \Yii::$app->controller->action->uniqueId;
        $fields = [];

        if($action == 'v1/sync/pullchanges')
            $fields = ['id'];

        return $this->hasMany(Species::className(), ['id' => 'species_id'])
            ->andWhere($conditions)
            ->viaTable('herb_species hs', ['herb_id' => 'id'], function($query) use ($conditions) {
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
            ->viaTable(HerbActivity::tableName(), ['herb_id' => 'id'], function($query) use ($conditions) {
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
            ->viaTable('herb_action hea', ['herb_id' => 'id'], function($query) use ($conditions) {
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
            ->viaTable('herb_symptom hes', ['herb_id' => 'id'], function($query) use ($conditions) {
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

        $favorite = HerbFavorites::findOne(['herb_id' => $this->id, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

        if($favorite)
            $this->is_favorite = 1;

        $this->is_readonly = 1;

        if($this->created_by == \Yii::$app->user->getId() || Security::isAdmin())
            $this->is_readonly = 0;
    }
}
