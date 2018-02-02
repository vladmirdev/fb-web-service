<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "login_history".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $location
 * @property string $action
 * @property string $os
 * @property string $useragent
 * @property string $browser
 * @property string $ip_address
 * @property integer $is_deleted
 * @property integer $created_by
 * @property string $created_time
 * @property integer $modified_by
 * @property string $modified_time
 *
 * @property User $user
 * @property User $createdBy
 * @property User $modifiedBy
 */
class LoginHistory extends \yii\db\ActiveRecord
{

    const ACTION_LOGIN  = 'login';
    const ACTION_LOGOUT = 'logout';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_history';
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
            [['user_id', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['user_id', 'action', 'useragent', 'ip_address'], 'required'],
            [['created_time', 'modified_time'], 'safe'],
            [['useragent'], 'string', 'max' => 500],
            [['location', 'action', 'os', 'useragent', 'ip_address', 'browser'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'location' => 'Location',
            'action' => 'Action',
            'os' => 'Os',
            'useragent' => 'Useragent',
            'browser' => 'Browser',
            'ip_address' => 'Ip Address',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
     * Store user login history events
     *
     * @param string $action
     *
     * @param integer|null $user
     *
     * @return bool
     */
    public static function store($action = self::ACTION_LOGIN, $user = null)
    {
        if (Yii::$app->user->isGuest && !$user)
            return false;

        $model = new self();

        $model->action = $action;

        $model->user_id = $user ? $user : Yii::$app->user->getId();
        $model->ip_address = Yii::$app->getRequest()->getUserIP();
        $model->useragent = Yii::$app->getRequest()->getUserAgent();
        $model->os = self::_getOS($model->useragent);
        $model->browser = self::_getBrowser($model->useragent);

        if(function_exists('geoip_country_name_by_name')) {
            $model->location = geoip_country_name_by_name($model->ip_address);
        }

        return $model->save();
    }


    /**
     * Get operating system from userAgent information
     *
     * @param $userAgent
     *
     * @return mixed|string
     */
    public static function _getOS($userAgent)
    {

        $os_platform = "Unknown OS Platform";

        $os_array = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        ];

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $userAgent)) {
                $os_platform = $value;
            }

        }

        return $os_platform;

    }

    /**
     * Get browser from userAgent information
     *
     * @param $userAgent
     *
     * @return mixed|string
     */
    public static function _getBrowser($userAgent)
    {

        $browser = "Unknown Browser";

        $browser_array = [
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        ];

        foreach ($browser_array as $regex => $value) {

            if (preg_match($regex, $userAgent)) {
                $browser = $value;
            }

        }

        return $browser;

    }
}
