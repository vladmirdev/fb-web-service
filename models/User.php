<?php

namespace app\models;

use app\constants\Errors;
use app\constants\Roles;
use app\modules\v1\models\SyncHistory;
use app\models\Token;
use app\modules\v1\models\VerifyCode;
use \yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $role
 * @property string $salt
 * @property string $password
 * @property string $verify_phone
 * @property string $verify_method
 * @property integer $status
 * @property integer $country_id
 * @property integer $language_id
 * @property integer $is_paid
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property Activity[] $activities
 * @property SyncHistory[] $syncHistories
 * @property Token[] $tokens
 * @property Device[] $devices
 * @property VerifyCode[] $verifyCodes
 * @property Country $country
 * @property Language $language
 * @property LoginHistory[] $loginHistories
 * @property User|null $createdBy
 * @property User|null $modifiedBy
 * @property LoginHistory $lastLogin
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password2;
    public $token;

    public $country_code;
    public $language_code;
    public $full_name;

    const ITEM_TYPE = 'user';
    const SYSTEM = 0;

    public $isAdmin = false;

    /**
     * @inheritdoc
     */
	public static function tableName()
	{
		return 'user';
	}

    /**
     * @inheritdoc
     */
	public function rules()
    {
        return [
            [['email', 'password'], 'required', 'when' => function() {
                return $this->isNewRecord;
            }],
            [['email'], 'email'],
            [['email', 'salt', 'password'], 'string', 'max' => 100],
            [['role', 'verify_method'], 'string', 'max' => 20],
            [['status', 'country_id', 'language_id', 'is_paid', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['country_code', 'language_code'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['modified_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_by' => 'id']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_id' => 'id']],

            [['email'], function($attribute, $params) {

                $existing = self::findOne(['email' => $this->$attribute, 'is_deleted' => 0]);

                if($this->isNewRecord) {

                    if($existing)
                        $this->addError($attribute, 'Email has already been taken');

                } else {

                    // if($existing && $existing->id != $this->id)
                    //    $this->addError($attribute, 'Email has already been taken');
                }
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return ['id', 'firstname', 'lastname', 'email', 'role', 'country_id', 'language_id', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'email' => 'Email',
            'role' => 'Role',
            'salt' => 'Salt',
            'password' => 'Password',
            'verify_phone' => 'Verify Phone',
            'verify_method' => 'Verify Method',
            'status' => 'Status',
            'country_id' => 'Country',
            'language_id' => 'Language',
            'is_paid' => 'Paid',
            'is_deleted' => 'Deleted',
            'created_by' => 'Created By',
            'created_time' => 'Created Time',
            'modified_by' => 'Modified By',
            'modified_time' => 'Modified Time',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'deleted' => 0]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        \Yii::info('Try to auth with token: ' . $token);

        $userToken = Token::findOne(['access_token' => $token, 'is_deleted' => 0]);

        if($userToken) {

            $user = self::findOne(['id' => $userToken->user_id, 'is_deleted' => 0]);

            if(!$user)
                return null;

            // Check token expiration

            if($userToken->timeout < time()) {
                LoginHistory::store(LoginHistory::ACTION_LOGOUT, $user->id);
                return null;
            }

            $user->token = $token;

            if($userToken->is_active == 0) {

                $userToken->is_active = 1;
                $userToken->save();

                LoginHistory::store(LoginHistory::ACTION_LOGIN, $user->id);
            }

            if($user->hasRole(Roles::ADMINISTRATOR))
                $user->isAdmin = true;

            return $user;
        }

        return null;
    }

    /**
     * Finds user by email
     *
     * @param string $email
     *
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'deleted' => 0]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generate auth key (token)
     *
     * @param int $length
     *
     * @return string
     */
    public function generateAuthKey($length = 32)
    {
        return \Yii::$app->security->generateRandomString($length);
    }

    /**
     * Check user role
     *
     * @param $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return \Yii::$app->authManager->checkAccess($this->id, $role);
    }

    /**
     * Get user primary role
     *
     * @return mixed
     */
    public function getPrimaryRole()
    {
        $roles = \Yii::$app->authManager->getRolesByUser($this->id);

        return $roles ? array_keys(\Yii::$app->authManager->getRolesByUser($this->id))[0] : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivities()
    {
        return $this->hasMany(Activity::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSyncHistories()
    {
        return $this->hasMany(SyncHistory::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoginHistories()
    {
        return $this->hasMany(LoginHistory::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevices()
    {
        return $this->hasMany(Device::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Token::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVerifyCodes()
    {
        return $this->hasMany(VerifyCode::className(), ['user_id' => 'id']);
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
    public function getLastLogin()
    {
        return $this->hasOne(LoginHistory::className(), ['user_id' => 'id'])->where(['action' => LoginHistory::ACTION_LOGIN])->orderBy(['id' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['id' => 'language_id']);
    }

    /**
     * After find event catcher
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->full_name = $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return ['createdBy', 'modifiedBy', 'lastLogin'];
    }

}
