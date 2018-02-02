<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\constants\Roles;
use app\helpers\Security;
use app\models\Country;
use app\models\Device;
use app\models\DevicePlatform;
use app\models\Language;
use app\models\LoginHistory;
use app\modules\v1\models\UserSearch;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\User;
use app\models\Token;
use app\modules\v1\models\VerifyCode;

class UserController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $guestActions = array_merge(['create', 'login', 'sendresetpasswordemail', 'verifyresetpassword', 'resetpassword'], parent::guestActions());

        $behaviors['authenticator']['except'] = $guestActions;
        $behaviors['access']['except'] = $guestActions;
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['view', 'update', 'changepassword', 'verifyaccount', 'sendverifyemail', 'logout', 'activity', 'login-history', 'devices'],
                'roles' => ['@'],
            ],
            [
                'allow' => true,
                'actions' => ['index', 'delete', 'search', 'data'],
                'roles' => [Roles::ADMINISTRATOR],
            ]
        ];

        return $behaviors;
    }

    /**
     * List users
     *
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => User::find()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
                'attributes' => [
                    'id',
                    'status',
                    'created_time',
                    'modified_time',
                ],
            ]
        ]);

        return $activeData;
    }

    /**
     * Search users
     *
     * @return ActiveDataProvider
     */
    public function actionSearch()
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $dataProvider;
    }

    /**
     * Get user by ID
     *
     * @param $id
     *
     * @return ActiveRecord
     * @throws ForbiddenHttpException
     */
    public function actionView($id)
    {
        $model = $this->loadUser($id);

        if($model->id != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {
            throw new ForbiddenHttpException(\Yii::$app->user->getId());
        }

        return $model;
    }

    /**
     * Create new user
     *
     * @return User|array
     *
     * @throws \Exception
     */
    public function actionCreate()
    {
        $user = new User();

        $transaction = User::getDb()->beginTransaction();

        $user->salt = Security::generateSalt();

        $user->firstname = \Yii::$app->request->getBodyParam('firstname');
        $user->lastname = \Yii::$app->request->getBodyParam('lastname');
        $user->email = \Yii::$app->request->getBodyParam('email');
        $user->password = \Yii::$app->request->getBodyParam('password');

        // Set country

        $country_code = \Yii::$app->request->getBodyParam('country_code');

        if($country_code) {

            $country = Country::findOne(['code' => $country_code, 'is_deleted' => 0]);

            if($country)
                $user->country_id = $country->id;
        } else {
            $user->country_id = \Yii::$app->request->getBodyParam('country_id');
        }

        // Set language

        $language_code = \Yii::$app->request->getBodyParam('language_code');

        if($language_code) {

            $language = Language::findOne(['code' => $language_code, 'is_deleted' => 0]);

            if($language)
                $user->language_id = $language->id;
        } else {
            $user->language_id = \Yii::$app->request->getBodyParam('language_id');
        }

        $auth = \Yii::$app->authManager;

        $definedRole = $auth->getRole(\Yii::$app->request->getBodyParam('role'));

        if($user->validate()) {

            try {

                $user->password = Security::generatePassword($user->password, $user->salt);
                $user->save(false);

                $user->refresh();

                $token = null;

                if(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR) && $definedRole) {

                    // Assign defined role

                    $auth->assign($definedRole, $user->id);

                } else {

                    // Assign default role

                    $role = $auth->getRole(Roles::USER);
                    $auth->assign($role, $user->id);

                    // Generate token

                    $created_time = time();

                    $token = new Token();

                    $token->platform_id = DevicePlatform::PLATFORM_WEB;
                    $token->access_token = Security::generateToken();
                    $token->user_id = $user->id;
                    $token->timeout = $created_time + \Yii::$app->params['access_token_timeout'];

                    $token->created_by = $user->id;
                    $token->created_time = new Expression('NOW()');

                    $token->save();

                    \Yii::info($token->getErrors(), 'validation');
                }

                $job = \Yii::$app->queue->push(new \app\jobs\VerifyEmailJob([
                    'userId' => $user->id
                ]));

                \Yii::info('Pushed new job: ' . $job, 'queue');

                $transaction->commit();

            } catch (\Exception $ex) {

                $transaction->rollBack();

                \Yii::error($ex->getMessage());

                throw $ex;
            }

            // Store device information ABOVE migration

            $device_uid = \Yii::$app->request->getBodyParam('device_uid');

            if($device_uid) {

                $device = Device::findOne(['uid' => $device_uid]);

                if(!$device) {

                    $device = new Device();

                    $device->uid = \Yii::$app->request->getBodyParam('device_uid');
                    $device->model = \Yii::$app->request->getBodyParam('device_model');
                    $device->name = \Yii::$app->request->getBodyParam('device_name');
                    $device->vendor = \Yii::$app->request->getBodyParam('device_vendor');
                    $device->platform_id = \Yii::$app->request->getBodyParam('device_platform', DevicePlatform::PLATFORM_ANDROID);
                    $device->os_version = \Yii::$app->request->getBodyParam('os_version');
                    $device->app_version = \Yii::$app->request->getBodyParam('app_version');

                    $device->created_by = $user->id;
                    $device->created_time = new Expression('NOW()');

                    $device->save();

                }
            }

            return $this->sendResponse(
                    [
                        'access_token' => $token ? $token->access_token : null,
                        'user_id' => $user->id,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status
                    ], null, Http::CREATED);
        } else {

            foreach($user->getErrors() as $field => $messages) {

                foreach($messages as $message) {
                    if(preg_match('/Email cannot be blank/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_EMPTY_EMAIL);
                    } elseif(preg_match('/Password cannot be blank/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_EMPTY_PASSWORD);
                    } elseif(preg_match('/has already been taken/i', $message)) {
                        return $this->sendResponse('Email has already been taken.', Errors::USER_NOT_UNIQUE_EMAIL);
                    } elseif(preg_match('/Email is not a valid email address/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_INVALID_EMAIL);
                    }
                }
            }
        }
        
        return $user;
    }

    /**
     * Update existing user
     *
     * @param $id
     *
     * @return array|ActiveRecord
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $user = $this->loadUser($id);

        if($user->id != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {
            throw new ForbiddenHttpException();
        }

        $firstname = \Yii::$app->request->getBodyParam('firstname');

        if(!empty($firstname)) {
            $user->firstname = $firstname;
        }

        $lastname = \Yii::$app->request->getBodyParam('lastname');

        if(!empty($lastname)) {
            $user->lastname = $lastname;
        }

        $email = \Yii::$app->request->getBodyParam('email');

        if(!empty($email)) {
            $user->email = $email;
        }

        $country = \Yii::$app->request->getBodyParam('country_id');

        if(!empty($country)) {
            $user->country_id = $country;
        }

        $language = \Yii::$app->request->getBodyParam('language_id');

        if(!empty($language)) {
            $user->language_id = $language;
        }

        // Admin rights to change user status

        if(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)) {

            $status = \Yii::$app->request->getBodyParam('status');

            if($status)
                $user->status = $status;
        }

        $user->is_deleted = 0;

        $definedRole = \Yii::$app->request->getBodyParam('role');

        if($user->validate()) {

            $user->save(false);

            if(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR) && $definedRole) {

                $auth = \Yii::$app->authManager;

                $role = $auth->getRole($definedRole);

                if($role) {

                    // Clean up existing role
                    $auth->revokeAll($user->id);

                    // Assign defined role
                    $auth->assign($role, $user->id);
                }
            }

        } else {

            foreach($user->getErrors() as $field => $messages) {

                foreach($messages as $message) {
                    if(preg_match('/Email cannot be blank/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_EMPTY_EMAIL);
                    } elseif(preg_match('/Password cannot be blank/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_EMPTY_PASSWORD);
                    } elseif(preg_match('/has already been taken/i', $message)) {
                        return $this->sendResponse('Email has already been taken.', Errors::USER_NOT_UNIQUE_EMAIL);
                    } elseif(preg_match('/Email is not a valid email address/i', $message)) {
                        return $this->sendResponse($message, Errors::USER_INVALID_EMAIL);
                    }
                }
            }
        }
        
        return $user;
    }

    /**
     * Delete user
     *
     * @param $id
     *
     * @return array|User
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = $this->loadUser($id);

        $user->is_deleted = 1;

        $user->modified_by = \Yii::$app->user->getId();
        $user->modified_time = new Expression('NOW()');

        if($user->save()) {

            $job = \Yii::$app->queue->push(new \app\jobs\UserDeleteJob([
                'userId' => $user->id
            ]));

            \Yii::info('Pushed new job: ' . $job, 'queue');

            return $this->sendResponse('Deletion success');
        }

        return $this->sendValidationResult($user);
    }

    /**
     * Delete user data
     *
     * @param $id
     *
     * @return array|User
     * @throws NotFoundHttpException
     */
    public function actionData($id)
    {
        $user = $this->loadUser($id);

        $job = \Yii::$app->queue->push(new \app\jobs\UserDeleteJob([
            'userId' => $user->id
        ]));

        \Yii::info('Pushed new job: ' . $job, 'queue');

        return $this->sendResponse('Data deletion job pushed to queue');
    }

    /**
     * Login
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionLogin()
    {
        $email = \Yii::$app->request->getBodyParam('email');
        $password = \Yii::$app->request->getBodyParam('password');

        $user = $this->loadUser($email, 'email');

        $success = false;

        if($user) {

            /**
             * Update old password hashed with MD5
             */
            if($user->password == md5($password) && !$user->salt) {

                $user->salt = Security::generateSalt();
                $user->password = Security::generatePassword($password, $user->salt);

                $user->save();

                \Yii::info('Update old MD5 password', 'security');

                $success = true;

            } else {

                $success = Security::checkPassword($password, $user->salt, $user->password);
            }
        }
        
        if(!empty($user) && $success) {

            $token = new Token();

            $created_time = time();

            $token->platform_id = DevicePlatform::PLATFORM_WEB;
            $token->access_token = Security::generateToken();
            $token->user_id = $user->id;
            $token->timeout = $created_time + \Yii::$app->params['access_token_timeout'];
            
            $token->created_by = $user->id;
            $token->created_time = new Expression('NOW()');
            
            $token->save();

            // Store device information

            $device_uid = \Yii::$app->request->getBodyParam('device_uid');

            if($device_uid) {

                $device = Device::findOne(['uid' => $device_uid, 'created_by' => $user->id, 'is_deleted' => 0]);

                if(!$device) {

                    $device = new Device();

                    $device->uid = \Yii::$app->request->getBodyParam('device_uid');
                    $device->model = \Yii::$app->request->getBodyParam('device_model');
                    $device->vendor = \Yii::$app->request->getBodyParam('device_vendor');
                    $device->platform_id = \Yii::$app->request->getBodyParam('device_platform', DevicePlatform::PLATFORM_ANDROID);
                    $device->os_version = \Yii::$app->request->getBodyParam('os_version');
                    $device->app_version = \Yii::$app->request->getBodyParam('app_version');

                    $device->created_by = $user->id;
                    $device->created_time = new Expression('NOW()');

                    $device->save();
                }
            }

            $this->login($user);

            LoginHistory::store();

            return $this->sendResponse(
                [
                    'access_token' => $token->access_token,
                    'user_id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ]);

        }

        return $this->sendResponse('Email or password incorrect', Errors::USER_INVALID_CREDENTIALS);
    }

    /**
     * @return mixed
     */
    public function actionLogout()
    {
        $access_token = \Yii::$app->user->identity->getAuthKey();
        $token = Token::findOne(['access_token' => $access_token, 'is_deleted' => 0]);

        if(empty($token)) {
            return $this->sendResponse('Invalid access token', Errors::USER_WRONG_TOKEN);
        } else {

            $token->is_deleted = 1;
            $token->modified_by = $token->user_id;
            $token->modified_time = new Expression('NOW()');

            $token->save();

            LoginHistory::store(LoginHistory::ACTION_LOGOUT);

            \Yii::$app->user->logout();

            return $this->sendResponse('Logout successfully');
        }
    }

    /**
     * Send reset password email code
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function actionSendresetpasswordemail()
    {
        $email = \Yii::$app->request->post('email');

        $user = $this->loadUser($email, 'email');

        $job = \Yii::$app->queue->push(new \app\jobs\ResetPasswordJob([
            'userId' => $user->id
        ]));

        \Yii::info('Pushed new job: ' . $job, 'queue');

        return $this->sendResponse('Email sent successfully');
    }

    /**
     * Verify reset password
     *
     * @return mixed
     */
    public function actionVerifyresetpassword()
    {
        $email = \Yii::$app->request->getBodyParam('email');

        $user = $this->loadUser($email, 'email');

        $verify_code = \Yii::$app->request->getBodyParam('verify_code');

        $vf = VerifyCode::findOne(['verify_code' => $verify_code, 'user_id' => $user->id]);

        if(empty($vf)) {
            return $this->sendResponse('Invalid verify code', Errors::USER_INVALID_VERIFY_CODE);
        }

        if($vf->is_used) {
            return $this->sendResponse('Verify code used', Errors::USER_VERIFY_CODE_USED);
        }

        if(time() <= strtotime($vf->expired_time)) {
            return $this->sendResponse('Reset password verified successfully');
        }

        // Return $this->sendResponse('Verify code expired', Errors::USER_VERIFY_CODE_EXPIRED);
        $this->sendResponseByCode(Errors::USER_VERIFY_CODE_EXPIRED);
    }

    /**
     * Reset password
     *
     * @return array
     */
    public function actionResetpassword()
    {
        $email = \Yii::$app->request->post('email');

        $user = $this->loadUser($email, 'email');

        $password = \Yii::$app->request->post('password');
        $password2 = \Yii::$app->request->post('password2');

        if(empty($password)) {
            return $this->sendResponse('New password cannot be blank', Errors::USER_EMPTY_PASSWORD);
        } elseif($password == $password2) {

            $user->salt = Security::generateSalt();
            $user->password = Security::generatePassword($password, $user->salt);

            $user->save(false);

            return $this->sendResponse('Password reset successfully');
        } else {
            return $this->sendResponse("Password doesn't match", Errors::USER_WRONG_PASSWORD);
        }
    }

    /**
     * Change password
     *
     * @return array
     */
    public function actionChangepassword()
    {
        $user = $this->loadUser(\Yii::$app->user->getId());

        $new_password = \Yii::$app->request->post('new_password');
        $new_password2 = \Yii::$app->request->post('new_password2');

        if(!empty($user)) {

            if(empty($new_password)) {

                return $this->sendResponse('New password cannot be blank', Errors::USER_EMPTY_PASSWORD);

            } elseif($new_password == $new_password2) {

                $user->salt = Security::generateSalt();
                $user->password = Security::generatePassword($new_password, $user->salt);

                $user->save(false);

                return $this->sendResponse('Password changed successfully');

            } else {
                return $this->sendResponse("Password doesn't match", Errors::USER_WRONG_PASSWORD);
            }
        } else {

            return $this->sendResponse('User not found', Errors::USER_NOT_EXISTS);
        }
    }

    /**
     * Send verification code
     *
     * @return array
     *
     * @throws \Exception
     */
    public function actionSendverifyemail()
    {
        $user = $this->loadUser(\Yii::$app->user->getId());

        $job = \Yii::$app->queue->push(new \app\jobs\VerifyEmailJob([
            'userId' => $user->id
        ]));

        \Yii::info('Pushed new job: ' . $job, 'queue');

        return $this->sendResponse('Email sent successfully');
    }

    /**
     * Verify account
     *
     * @return array
     */
    public function actionVerifyaccount()
    {

        $user = $this->loadUser(\Yii::$app->user->getId());

        $verify_code = \Yii::$app->request->getBodyParam('verify_code');

        $vf = VerifyCode::findOne(['verify_code' => $verify_code, 'user_id' => $user->id]);

        if(empty($vf)) {
            return $this->sendResponse('Invalid verify code', Errors::USER_INVALID_VERIFY_CODE);        }
        if($vf->is_used) {
            return $this->sendResponse('Verify code used', Errors::USER_VERIFY_CODE_USED);
        }

        if(time() <= strtotime($vf->expired_time)) {

            $user->status = 1;
            $user->save();

            return $this->sendResponse('Account verified successfully');

        } else {

            return $this->sendResponse('Verify code expired', Errors::USER_VERIFY_CODE_EXPIRED);
        }
    }

    /**
     * Get user login history
     *
     * @param $id
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionLoginHistory($id)
    {
        $user = $this->loadUser($id);

        if(!\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR) && $user->id != \Yii::$app->user->getId()) {
            throw new ForbiddenHttpException();
        }

        $activeData = new ActiveDataProvider([
            'query' => $user->getLoginHistories(),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        return $activeData;
    }

    /**
     * Get user devices
     *
     * @param $id
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDevices($id)
    {
        $user = $this->loadUser($id);

        if(!\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR) && $user->id != \Yii::$app->user->getId()) {
            throw new ForbiddenHttpException();
        }

        $activeData = new ActiveDataProvider([
            'query' => $user->getDevices()->where(['is_deleted' => 0]),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        return $activeData;
    }

    /**
     * Get user activity
     *
     * @param $id
     *
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionActivity($id)
    {
        $user = $this->loadUser($id);

        if(!\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR) && $user->id != \Yii::$app->user->getId()) {
            throw new ForbiddenHttpException();
        }

        $activeData = new ActiveDataProvider([
            'query' => $user->getActivities(),
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        return $activeData;
    }

    /**
     * Load user model
     *
     * @param int|string $id
     *
     * @param string $field
     *
     * @return User
     * @throws NotFoundHttpException
     */
    private function loadUser($id, $field = 'id')
    {
        $model = User::findOne([$field => $id, 'is_deleted' => 0]);

        if(!$model) {
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_EXISTS);
        }

        return $model;
    }
}
