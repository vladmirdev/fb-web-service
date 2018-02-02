<?php

namespace app\models;

use app\constants\Errors;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

// use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * This is the model class for table "template".
 *
 * @property integer $id
 * @property string $code
 * @property string $title
 * @property string $content
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property User $createdBy
 * @property User $modifiedBy
 */
class Template extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'template';
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
            [['code', 'title', 'content'], 'required'],
            [['content'], 'string'],
            [['is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['created_time', 'modified_time'], 'safe'],
            [['code'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 255],
            [['code'], 'unique'],
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
            'title' => 'Title',
            'content' => 'Content',
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
     * Parse template variables
     *
     * @param $code
     * @param array $variables
     *
     * @return self
     * @throws NotFoundHttpException
     */
    public static function parse($code, $variables = [])
    {
        $model = self::findOne(['code' => $code, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Template not found', Errors::TEMPLATE_NOT_FOUND);

        // $language = new ExpressionLanguage();

        // $model->title = $language->evaluate($model->title, $variables);
        // $model->content = $language->evaluate($model->content, $variables);

        $model->title = self::simpleReplace($model->title, $variables);
        $model->content = self::simpleReplace($model->content, $variables);

        return $model;
    }

    /**
     * Simple replace variables in string
     *
     * @param $string
     * @param array $variables
     *
     * @return mixed
     */
    public static function simpleReplace($string, $variables = [])
    {
        return str_replace(array_keys($variables), array_values($variables), $string);
    }
}
