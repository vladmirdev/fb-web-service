<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "herb_alternate".
 *
 * @property integer $id
 * @property integer $herb_id
 * @property integer $alternate_herb_id
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Herb $herb
 * @property Herb $alternateHerb
 * @property User $createdBy
 * @property User $modifiedBy
 */
class HerbAlternate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'herb_alternate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['herb_id', 'alternate_herb_id'], 'required'],
            [['herb_id', 'alternate_herb_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['herb_id'], 'exist', 'skipOnError' => false, 'targetClass' => Herb::className(), 'targetAttribute' => ['herb_id' => 'id']],
            [['alternate_herb_id'], 'exist', 'skipOnError' => false, 'targetClass' => Herb::className(), 'targetAttribute' => ['alternate_herb_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],

            [['herb_id'], function ($attribute, $params) {

                $model = Herb::findOne(['id' => $this->herb_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted herb');
            }],

            [['alternate_herb_id'], function ($attribute, $params) {

                $model = Herb::findOne(['id' => $this->alternate_herb_id]);

                if($model->is_deleted && !$this->is_deleted)
                    $this->addError($attribute, 'Cannot assign deleted alternate herb');
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
            'alternate_herb_id' => 'Alternate Herb ID',
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
    public function getAlternateHerb()
    {
        return $this->hasOne(Herb::className(), ['id' => 'alternate_herb_id']);
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
