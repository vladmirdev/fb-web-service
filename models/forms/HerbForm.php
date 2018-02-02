<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 27.11.17
 * Time: 14:37
 */

namespace app\models\forms;

use app\constants\Actions;
use app\constants\Errors;
use app\constants\Roles;
use app\helpers\Security;
use app\models\User;
use app\modules\v1\models\Action;
use app\modules\v1\models\Book;
use app\modules\v1\models\Category;
use app\modules\v1\models\Caution;
use app\modules\v1\models\Channel;
use app\modules\v1\models\Cultivation;
use app\modules\v1\models\EnglishCommon;
use app\modules\v1\models\Flavour;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaAction;
use app\modules\v1\models\FormulaCategory;
use app\modules\v1\models\FormulaHerb;
use app\modules\v1\models\FormulaNote;
use app\modules\v1\models\FormulaPreparation;
use app\modules\v1\models\FormulaSource;
use app\modules\v1\models\FormulaSymptom;
use app\modules\v1\models\Herb;
use app\modules\v1\models\HerbAction;
use app\modules\v1\models\HerbAlternate;
use app\modules\v1\models\HerbCategory;
use app\modules\v1\models\HerbCaution;
use app\modules\v1\models\HerbChannel;
use app\modules\v1\models\HerbCultivation;
use app\modules\v1\models\HerbEnglishcommon;
use app\modules\v1\models\HerbFlavour;
use app\modules\v1\models\HerbLatinname;
use app\modules\v1\models\HerbNature;
use app\modules\v1\models\HerbNote;
use app\modules\v1\models\HerbPreparation;
use app\modules\v1\models\HerbSource;
use app\modules\v1\models\HerbSpecies;
use app\modules\v1\models\HerbSymptom;
use app\modules\v1\models\LatinName;
use app\modules\v1\models\Nature;
use app\modules\v1\models\Note;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Source;
use app\modules\v1\models\Species;
use app\modules\v1\models\Symptom;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class HerbForm extends Model
{
    public $newRecord = false;

    public $id;

    public $name;
    public $pinyin;
    public $pinyin_code;
    public $pinyin_ton;
    public $english_name;
    public $latin_name;
    public $english_common;
    public $simplified_chinese;
    public $traditional_chinese;

    public $actions;
    public $alternates;
    public $categories;
    public $cautions;
    public $channels;
    public $cultivations;
    public $englishCommons;
    public $formulas;
    public $flavours;
    public $latinNames;
    public $natures;
    public $species;
    public $sources;
    public $symptoms;
    public $preparations;

    public $notes;

    protected $_relations = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'pinyin', 'pinyin_code', 'pinyin_ton', 'english_name', 'latin_name', 'english_common', 'simplified_chinese', 'traditional_chinese'], 'string', 'max' => 500],
            [['actions', 'alternates', 'categories', 'cautions', 'channels', 'cultivations', 'englishCommons', 'formulas', 'flavours', 'latinNames', 'natures', 'species', 'sources', 'symptoms', 'preparations', 'notes'], 'safe']
        ];
    }

    /**
     * Save herb and create/update related records
     *
     * @return bool
     *
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $model = new Herb();

        $related = [];

        if ($this->id)
            $model = $this->loadHerb($this->id, Actions::UPDATE);

        $transaction = Herb::getDb()->beginTransaction();

        try {

            foreach ($this->attributes as $attribute => $value) {

                if (!$model->hasProperty($attribute) && !in_array($attribute, $model->extraFields()))
                    continue;

                if(in_array($attribute, $model->extraFields())) {

                    $related[$attribute] = $value;
                    $this->_relations[] = $attribute;

                } else {

                    if(!in_array($attribute, $this->getUnsafeAttributes()))
                        $model->$attribute = $value;
                }
            }

            $this->newRecord = (boolean) !$model->id;

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->id = $model->id;

            if (sizeof($related) > 0) {

                foreach ($related as $attribute => $value) {

                    switch ($attribute) {

                        case 'actions':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbAction::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbAction $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new action

                                if (!isset($relation['id'])) {

                                    $_model = new Action();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbAction();

                                        $_relation->herb_id = $model->id;
                                        $_relation->action_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbAction::findOne(['herb_id' => $model->id, 'action_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->action_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbAction();

                                        $_relation->herb_id = $model->id;
                                        $_relation->action_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->action_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbAction::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'action_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaAction $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'alternates':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbAlternate::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbAlternate $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new herb

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign alternate herb without ID');

                                } else {

                                    $_relation = HerbAlternate::findOne(['herb_id' => $model->id, 'alternate_herb_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->herb_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbAlternate();

                                        $_relation->herb_id = $model->id;
                                        $_relation->alternate_herb_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->herb_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbAlternate::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'alternate_herb_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbAlternate $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'categories':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbCategory::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbCategory $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new category

                                if (!isset($relation['id'])) {

                                    $_model = new Category();

                                    $_model->load($relation, '');
                                    $_model->type = Herb::ITEM_TYPE;

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbCategory();

                                        $_relation->herb_id = $model->id;
                                        $_relation->category_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbCategory::findOne(['herb_id' => $model->id, 'category_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->category_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbCategory();

                                        $_relation->herb_id = $model->id;
                                        $_relation->category_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->category_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbCategory::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'category_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbCategory $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'cautions':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbCaution::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbCaution $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new caution

                                if (!isset($relation['id'])) {

                                    $_model = new Caution();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbCaution();

                                        $_relation->herb_id = $model->id;
                                        $_relation->caution_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbCaution::findOne(['herb_id' => $model->id, 'caution_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->caution_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbCaution();

                                        $_relation->herb_id = $model->id;
                                        $_relation->caution_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->caution_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbCaution::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'caution_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbCaution $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'channels':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbChannel::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbChannel $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new channel

                                if (!isset($relation['id'])) {

                                    $_model = new Channel();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbChannel();

                                        $_relation->herb_id = $model->id;
                                        $_relation->channel_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbChannel::findOne(['herb_id' => $model->id, 'channel_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->channel_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbChannel();

                                        $_relation->herb_id = $model->id;
                                        $_relation->channel_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->channel_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbChannel::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'channel_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbChannel $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'cultivations':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbCultivation::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbCultivation $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new cultivation

                                if (!isset($relation['id'])) {

                                    $_model = new Cultivation();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbCultivation();

                                        $_relation->herb_id = $model->id;
                                        $_relation->cultivation_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbCultivation::findOne(['herb_id' => $model->id, 'cultivation_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->cultivation_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbCultivation();

                                        $_relation->herb_id = $model->id;
                                        $_relation->cultivation_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->cultivation_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbCultivation::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'cultivation_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbCultivation $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'englishCommons':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbEnglishcommon::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbEnglishcommon $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new English Common

                                if (!isset($relation['id'])) {

                                    $_model = new EnglishCommon();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbEnglishcommon();

                                        $_relation->herb_id = $model->id;
                                        $_relation->english_common_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbEnglishcommon::findOne(['herb_id' => $model->id, 'english_common_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->english_common_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbEnglishcommon();

                                        $_relation->herb_id = $model->id;
                                        $_relation->english_common_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->english_common_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbEnglishcommon::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'english_common_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbEnglishcommon $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'formulas':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = FormulaHerb::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaHerb $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new formula herb relation

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign formula without ID');

                                } else {

                                    $_relation = FormulaHerb::findOne(['herb_id' => $model->id, 'formula_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->formula_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaHerb();

                                        $_relation->herb_id = $model->id;
                                        $_relation->formula_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->formula_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = FormulaHerb::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'formula_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaHerb $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'flavours':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbFlavour::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbFlavour $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new flavour

                                if (!isset($relation['id'])) {

                                    $_model = new Flavour();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbFlavour();

                                        $_relation->herb_id = $model->id;
                                        $_relation->flavour_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbFlavour::findOne(['herb_id' => $model->id, 'flavour_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->flavour_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbFlavour();

                                        $_relation->herb_id = $model->id;
                                        $_relation->flavour_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->flavour_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbFlavour::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'flavour_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbFlavour $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'latinNames':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbLatinname::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbLatinname $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new Latin Name

                                if (!isset($relation['id'])) {

                                    $_model = new LatinName();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbLatinname();

                                        $_relation->herb_id = $model->id;
                                        $_relation->latin_name_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbLatinname::findOne(['herb_id' => $model->id, 'latin_name_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->latin_name_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbLatinname();

                                        $_relation->herb_id = $model->id;
                                        $_relation->latin_name_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->latin_name_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbLatinname::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'category_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbLatinname $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'natures':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbNature::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbNature $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new nature

                                if (!isset($relation['id'])) {

                                    $_model = new Nature();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbNature();

                                        $_relation->herb_id = $model->id;
                                        $_relation->nature_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbNature::findOne(['herb_id' => $model->id, 'nature_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->nature_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbNature();

                                        $_relation->herb_id = $model->id;
                                        $_relation->nature_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->nature_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbNature::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'nature_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbNature $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'species':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbSpecies::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbSpecies $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new specie

                                if (!isset($relation['id'])) {

                                    $_model = new Species();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbSpecies();

                                        $_relation->herb_id = $model->id;
                                        $_relation->species_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbSpecies::findOne(['herb_id' => $model->id, 'species_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->species_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbSpecies();

                                        $_relation->herb_id = $model->id;
                                        $_relation->species_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->species_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbSpecies::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'species_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbSpecies $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'sources':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbSource::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbSource $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new source

                                if (!isset($relation['id'])) {

                                    $_model = new Book();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbSource();

                                        $_relation->herb_id = $model->id;
                                        $_relation->source_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbSource::findOne(['herb_id' => $model->id, 'source_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->source_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbSource();

                                        $_relation->herb_id = $model->id;
                                        $_relation->source_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $relation['id'];
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbSource::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'source_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbSource $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();
                                }
                            }

                            break;

                        case 'symptoms':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbSymptom::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbSymptom $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new symptom

                                if (!isset($relation['id'])) {

                                    $_model = new Symptom();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbSymptom();

                                        $_relation->herb_id = $model->id;
                                        $_relation->symptom_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbSymptom::findOne(['herb_id' => $model->id, 'symptom_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->symptom_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbSymptom();

                                        $_relation->herb_id = $model->id;
                                        $_relation->symptom_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->symptom_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbSymptom::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'symptom_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbSymptom $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'preparations':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbPreparation::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbPreparation $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = $model->modified_by;

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new preparation

                                if (!isset($relation['id'])) {

                                    $_model = new Preparation();

                                    $_model->load($relation, '');

                                    $_model->type = Herb::ITEM_TYPE;

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbPreparation();

                                        $_relation->herb_id = $model->id;
                                        $_relation->prep_id = $_model->id;

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbPreparation::findOne(['herb_id' => $model->id, 'prep_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->prep_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbPreparation();

                                        $_relation->herb_id = $model->id;
                                        $_relation->prep_id = $relation['id'];

                                        $_relation->created_by = $model->created_by;
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->prep_id;

                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbPreparation::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'prep_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbPreparation $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'notes':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbNote::find()
                                    ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var HerbNote $record */
                                    foreach ($excluded as $record) {

                                        $record->is_deleted = 1;

                                        $record->modified_time = $model->modified_time;
                                        $record->modified_by = \Yii::$app->user->getId();

                                        $record->save();

                                    }
                                }

                                continue;
                            }

                            if (!isset($value[0]))
                                $relations[] = $value;
                            else
                                $relations = $value;

                            foreach ($relations as $relation) {

                                // Clean unsafe attributes

                                $relation = $this->cleanUnsafeAttributes($relation);

                                // Create new action

                                if (!isset($relation['id'])) {

                                    $_model = new Note();

                                    $_model->load($relation, '');

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new HerbNote();

                                        $_relation->herb_id = $model->id;
                                        $_relation->note_id = $_model->id;

                                        $_relation->created_by = \Yii::$app->user->getId();
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                    } else {
                                        $this->addErrors([$attribute => array_values($_model->getErrors())]);
                                    }

                                } else {

                                    $_relation = HerbNote::findOne(['herb_id' => $model->id, 'note_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                                    if ($_relation) {

                                        $ids[] = $_relation->note_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbNote();

                                        $_relation->herb_id = $model->id;
                                        $_relation->note_id = $relation['id'];

                                        $_relation->created_by = \Yii::$app->user->getId();
                                        $_relation->created_time = $model->created_time;

                                        if(!$_relation->save()) {
                                            $this->addErrors([$attribute => array_values($_relation->getFirstErrors())]);
                                        }

                                        $ids[] = $_relation->note_id;
                                    }
                                }
                            }

                            if($model->isNewRecord) continue;

                            $excluded = HerbNote::find()
                                ->where(['herb_id' => $model->id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()])
                                ->andWhere(['not in', 'note_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var HerbNote $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = \Yii::$app->user->getId();

                                    $record->save();

                                }
                            }

                            break;
                    }
                }
            }

            if($this->hasErrors()) {
                $transaction->rollBack();
                return false;
            }

            $model->modified_time = new Expression('NOW()');
            $model->save(false);

            $transaction->commit();
            return true;

        } catch (Exception $exception) {

            $transaction->rollBack();
            $this->addError('id', $exception->getMessage());
            return false;
        }

    }

    /**
     * Get model relations
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->_relations;
    }

    /**
     * Get unsafe attributes
     *
     * @return array
     */
    public function getUnsafeAttributes()
    {
        return ['created_by', 'modified_by', 'created_time', 'modified_time', 'is_deleted', 'is_favorite'];
    }

    /**
     * Clean unsafe attributes
     *
     * @param array $data
     *
     * @return array
     */
    private function cleanUnsafeAttributes($data)
    {
        foreach ($data as $key => $value) {
            if(in_array($key, $this->getUnsafeAttributes()))
                unset($data[$key]);
        }

        return $data;
    }

    /**
     * Load herb model
     *
     * @param integer|null $id
     * @param string $action
     *
     * @return Herb
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadHerb($id, $action = 'view')
    {
        $model = Herb::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Herb not found', Errors::HERB_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !Security::isAdmin())
                throw new ForbiddenHttpException('Herb updating is forbidden', Errors::HERB_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && (\Yii::$app->user->isGuest || !Security::isAdmin()))
                throw new ForbiddenHttpException('Herb viewing is forbidden', Errors::HERB_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
}
