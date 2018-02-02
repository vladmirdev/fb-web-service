<?php

namespace app\modules\v1\controllers;

use app\constants\Errors;
use app\constants\Http;
use app\helpers\Converter;
use app\helpers\Security;
use app\models\Device;
use app\modules\v1\models\Action;
use app\modules\v1\models\Book;
use app\modules\v1\models\FormulaFavorites;
use app\modules\v1\models\HerbFavorites;
use app\modules\v1\models\Note;
use app\modules\v1\models\Symptom;
use app\modules\v1\models\SyncHistory;
use app\modules\v1\models\Formula;
use app\modules\v1\models\Herb;
use app\modules\v1\models\Category;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Cultivation;
use app\modules\v1\models\Channel;
use app\modules\v1\models\Flavour;
use app\modules\v1\models\Nature;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Source;
use app\modules\v1\models\Species;
use app\modules\v1\models\User;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class SyncController extends BaseController
{
    public $modelClass = 'api\modules\v1\models\SyncHistory';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['pushlastsync', 'pullchanges'],
                'roles' => ['@'],

                // @todo implement RBAC roles
            ]
        ];

        return $behaviors;
    }

    /**
     * Push data changes
     *
     * @return array
     */
    public function actionPushlastsync()
    {

        $device_uid = \Yii::$app->request->getBodyParam('device_uid');
        $token = \Yii::$app->request->getBodyParam('token');
        $time = \Yii::$app->request->getBodyParam('last_sync_time');

        $device = Device::findOne(['uid' => $device_uid, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

        if($device)
            $device_id = $device->id;
        else
            return $this->sendResponse('Unknown device UUID', Errors::SYNC_UNKNOWN_DEVICE, Http::BAD_REQUEST);

        if($token) {

            /** @var SyncHistory $sync */
            $sync = SyncHistory::findOne(['token' => $token, 'user_id' => \Yii::$app->user->getId(), 'device_id' => $device_id]);

            if(!$sync)
                return $this->sendResponse('Wrong sync token', Errors::SYNC_UNKNOWN_TOKEN, Http::BAD_REQUEST);

            if($sync->is_confirmed == 0) {
                $sync->is_confirmed = 1;
                $sync->confirm_time = new Expression('NOW()');
            }

            if($sync->save()) {

                $sync->refresh();

                return $this->sendResponse([
                    'message' => 'Push last sync successfully',
                    'last_sync_time' => $sync->last_sync_time,
                    'confirm_time' => $sync->confirm_time,
                    'is_confirmed' => $sync->is_confirmed
                ]);
            }

        } elseif($time) {

            /** @var SyncHistory $currentSync */
            $currentSync = new SyncHistory();

            $currentSync->token = 'initial';
            $currentSync->user_id = \Yii::$app->user->getId();
            $currentSync->device_id = $device_id;
            $currentSync->last_sync_time = $time;
            $currentSync->confirm_time = new Expression('NOW()');
            $currentSync->is_confirmed = 1;

            if($currentSync->save()) {

                $currentSync->refresh();

                return $this->sendResponse([
                    'message' => 'Push last sync successfully',
                    'last_sync_time' => $currentSync->last_sync_time,
                    'confirm_time' => $currentSync->confirm_time,
                    'is_confirmed' => $currentSync->is_confirmed
                ]);
            }
        }

        return $this->sendResponse('Synchronization error', Errors::SYNC_GENERAL_ERROR);
    }

    /**
     * Pull data changes
     *
     * @return array
     */
    public function actionPullchanges()
    {
        $device_uid = \Yii::$app->request->getBodyParam('device_uid');

        $device = Device::findOne(['uid' => $device_uid, 'created_by' => \Yii::$app->user->getId(), 'is_deleted' => 0]);

        if($device)
            $device_id = $device->id;
        else
            return $this->sendResponse('Unknown device UUID', Errors::SYNC_UNKNOWN_DEVICE, Http::BAD_REQUEST);

        /** @var SyncHistory $currentSync */
        $currentSync = new SyncHistory();

        $currentSync->token = Security::generateToken();
        $currentSync->user_id = \Yii::$app->user->getId();
        $currentSync->device_id = $device_id;
        $currentSync->last_sync_time = new Expression('NOW()');
        $currentSync->is_confirmed = 0;

        if(!$currentSync->save())
            return $this->sendResponse('Synchronization error', Errors::SYNC_GENERAL_ERROR);

        $currentSync->refresh();

        $condition = [
            'user_id' => \Yii::$app->user->getId(),
            'device_id' => $device_id,
            'is_confirmed' => 1
        ];

        /** @var SyncHistory $lastSync */
        $lastSync = SyncHistory::find()
            ->where($condition)
            ->orderBy(['last_sync_time' => SORT_DESC])
            ->one();

        $condition1 = ['created_by' => [User::SYSTEM, \Yii::$app->user->getId()]];
        $condition2 = [];

        $filter = \Yii::$app->request->get('filter', 'all');

        if($filter == 'self' || $filter == 'own')
            $condition1 = ['created_by' => \Yii::$app->user->getId()];

        if($filter == 'system')
            $condition1 = ['created_by' => User::SYSTEM];

        if($lastSync)
            $condition2 = ['OR', ['>', 'modified_time', $lastSync->last_sync_time], ['>', 'created_time', $lastSync->last_sync_time]];

        $formulas = Formula::find()
            ->with(['categories', 'herbs', 'actions', 'symptoms', 'sources', 'preparations', 'notes'])
            ->where($condition1)
            ->andWhere($condition2)
            ->asArray()
            ->all();

        $herbs = Herb::find()
            ->with(['actions', 'alternates', 'categories', 'cautions', 'channels', 'cultivations', 'englishCommons', 'formulas', 'flavours', 'latinNames', 'natures', 'species', 'sources', 'symptoms', 'preparations', 'notes'])
            ->where($condition1)
            ->andWhere($condition2)
            ->asArray()
            ->all();


        $actions = Action::find()->where($condition1)->andWhere($condition2)->all();
        $categories = Category::find()->where($condition1)->andWhere($condition2)->all();
        $cautions = Caution::find()->where($condition1)->andWhere($condition2)->all();
        $cultivations = Cultivation::find()->where($condition1)->andWhere($condition2)->all();
        $channels = Channel::find()->where($condition1)->andWhere($condition2)->all();
        $flavours = Flavour::find()->where($condition1)->andWhere($condition2)->all();
        $natures = Nature::find()->where($condition1)->andWhere($condition2)->all();
        $preparations = Preparation::find()->where($condition1)->andWhere($condition2)->all();
        $sources = Book::find()->where($condition1)->andWhere($condition2)->all();
        $species = Species::find()->where($condition1)->andWhere($condition2)->all();
        $symptoms = Symptom::find()->where($condition1)->andWhere($condition2)->all();

        $notes = Note::find()->where($condition1)->andWhere($condition2)->all();

        $favorites = [
            FormulaFavorites::find()->where($condition1)->andWhere($condition2)->all(),
            HerbFavorites::find()->where($condition1)->andWhere($condition2)->all()
        ];

        return [
            'meta' => [
                'token' => $currentSync->token,
                'time' => $currentSync->last_sync_time,
            ],
            'formulas' => $formulas,
            'herbs' => $herbs,
            'actions' => $actions,
            'categories' => $categories,
            'cautions' => $cautions,
            'cultivations' => $cultivations,
            'channels' => $channels,
            'flavours' => $flavours,
            'natures' => $natures,
            'preparations' => $preparations,
            'sources' => $sources,
            'species' => $species,
            'symptoms' => $symptoms,
            'notes' => $notes,
            'favorites' => $favorites
        ];
    }

}
