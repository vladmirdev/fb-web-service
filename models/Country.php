<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "countries".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $full_name
 * @property string $iso3
 * @property integer $number
 * @property string $continent_code
 * @property integer $display_order
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Continent $continent
 * @property User $createdBy
 * @property User $modifiedBy
 * @property User[] $users
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'countries';
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
            [['code', 'name', 'full_name', 'iso3', 'number', 'continent_code'], 'required'],
            [['number', 'display_order', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['code', 'continent_code'], 'string', 'max' => 2],
            [['name'], 'string', 'max' => 64],
            [['full_name'], 'string', 'max' => 128],
            [['iso3'], 'string', 'max' => 3],
            [['code'], 'unique'],
            [['continent_code'], 'exist', 'skipOnError' => true, 'targetClass' => Continent::className(), 'targetAttribute' => ['continent_code' => 'code']],
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
            'code' => 'Code',
            'name' => 'Name',
            'full_name' => 'Full Name',
            'iso3' => 'Iso3',
            'number' => 'Number',
            'continent_code' => 'Continent Code',
            'display_order' => 'Display Order',
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
    public function getContinent()
    {
        return $this->hasOne(Continent::className(), ['code' => 'continent_code']);
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
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['country_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return ['id', 'code', 'name', 'full_name', 'number', 'iso3', 'continent_code', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }
}
