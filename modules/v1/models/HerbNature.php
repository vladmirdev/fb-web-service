<?php

namespace app\modules\v1\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "herb_nature".
 *
 * @property integer $id
 * @property integer $herb_id
 * @property integer $nature_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Herb $herb
 * @property Nature $nature
 * @property User $createdBy
 * @property User $modifiedBy
 */
class HerbNature extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'herb_nature';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['herb_id', 'nature_id'], 'required'],
            [['herb_id', 'nature_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['herb_id'], 'exist', 'skipOnError' => false, 'targetClass' => Herb::className(), 'targetAttribute' => ['herb_id' => 'id']],
            [['nature_id'], 'exist', 'skipOnError' => false, 'targetClass' => Nature::className(), 'targetAttribute' => ['nature_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],

            [['herb_id'], function ($attribute, $params) {

                $model = Herb::findOne(['id' => $this->herb_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted herb');
            }],

            [['nature_id'], function ($attribute, $params) {

                $model = Nature::findOne(['id' => $this->nature_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted nature');
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
            'herb_id' => 'Herb ID',
            'nature_id' => 'Nature ID',
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
    public function getHerb()
    {
        return $this->hasOne(Herb::className(), ['id' => 'herb_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNature()
    {
        return $this->hasOne(Nature::className(), ['id' => 'nature_id']);
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
     * @inheritdoc
     */
    public function fields()
    {
        return parent::fields();
        //return ['id','formula_id','category_id','is_deleted','created_by','modified_by'];
    }
}