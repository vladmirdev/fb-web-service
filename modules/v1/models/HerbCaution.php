<?php

namespace app\modules\v1\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "herb_caution".
 *
 * @property integer $id
 * @property integer $herb_id
 * @property integer $caution_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Herb $herb
 * @property Caution $caution
 * @property User $createdBy
 * @property User $modifiedBy
 */
class HerbCaution extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'herb_caution';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['herb_id', 'caution_id'], 'required'],
            [['herb_id', 'caution_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['herb_id'], 'exist', 'skipOnError' => false, 'targetClass' => Herb::className(), 'targetAttribute' => ['herb_id' => 'id']],
            [['caution_id'], 'exist', 'skipOnError' => false, 'targetClass' => Caution::className(), 'targetAttribute' => ['caution_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],

            [['herb_id'], function ($attribute, $params) {

                $model = Herb::findOne(['id' => $this->herb_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted herb');
            }],

            [['caution_id'], function ($attribute, $params) {

                $model = Caution::findOne(['id' => $this->caution_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted caution');
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
            'herb_id' => 'Herb',
            'caution_id' => 'Caution',
            'is_deleted' => 'Deleted',
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
    public function getCaution()
    {
        return $this->hasOne(Caution::className(), ['id' => 'caution_id']);
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
     * @return array
     */
    public function fields()
    {
        return parent::fields();
        //return ['id','formula_id','category_id','is_deleted','created_by','modified_by'];
    }
}
