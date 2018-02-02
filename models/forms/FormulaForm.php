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
use app\models\User;
use app\modules\v1\models\Action;
use app\modules\v1\models\Book;
use app\modules\v1\models\Category;
use app\modules\v1\models\Formula;
use app\modules\v1\models\FormulaAction;
use app\modules\v1\models\FormulaCategory;
use app\modules\v1\models\FormulaHerb;
use app\modules\v1\models\FormulaNote;
use app\modules\v1\models\FormulaPreparation;
use app\modules\v1\models\FormulaSource;
use app\modules\v1\models\FormulaSymptom;
use app\modules\v1\models\Note;
use app\modules\v1\models\Preparation;
use app\modules\v1\models\Source;
use app\modules\v1\models\Symptom;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class FormulaForm extends Model
{
    public $newRecord = false;

    public $id;

    public $name;
    public $pinyin;
    public $pinyin_code;
    public $pinyin_ton;
    public $english_name;
    public $simplified_chinese;
    public $traditional_chinese;

    public $categories;
    public $herbs;
    public $actions;
    public $symptoms;
    public $sources;
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
            [['name', 'pinyin', 'pinyin_code', 'pinyin_ton', 'english_name', 'simplified_chinese', 'traditional_chinese'], 'string', 'max' => 500],
            [['categories', 'herbs', 'actions', 'symptoms', 'sources', 'preparations', 'notes'], 'safe']
        ];
    }

    /**
     * Save formula and create/update related records
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
        $model = new Formula();

        $related = [];

        if ($this->id)
            $model = $this->loadFormula($this->id, Actions::UPDATE);

        $transaction = Formula::getDb()->beginTransaction();

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

                        case 'categories':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = FormulaCategory::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaCategory $record */
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
                                    $_model->type = Formula::ITEM_TYPE;

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new FormulaCategory();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaCategory::findOne(['formula_id' => $model->id, 'category_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->category_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaCategory();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaCategory::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'category_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaCategory $record */
                                foreach ($excluded as $record) {

                                    $record->is_deleted = 1;

                                    $record->modified_time = $model->modified_time;
                                    $record->modified_by = $model->modified_by;

                                    $record->save();

                                }
                            }

                            break;

                        case 'actions':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = FormulaAction::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
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

                                        $_relation = new FormulaAction();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaAction::findOne(['formula_id' => $model->id, 'action_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->action_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaAction();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaAction::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
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

                        case 'herbs':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = FormulaHerb::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
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

                                // Create new herb

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign herb without ID');

                                } else {

                                    $_relation = FormulaHerb::findOne(['formula_id' => $model->id, 'herb_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->herb_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaHerb();

                                        $_relation->formula_id = $model->id;
                                        $_relation->herb_id = $relation['id'];

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

                            $excluded = FormulaHerb::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'herb_id', $ids])
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

                        case 'preparations':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = FormulaPreparation::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaPreparation $record */
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

                                    $_model->type = Formula::ITEM_TYPE;

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new FormulaPreparation();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaPreparation::findOne(['formula_id' => $model->id, 'prep_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->prep_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaPreparation();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaPreparation::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'prep_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaPreparation $record */
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

                                $excluded = FormulaSymptom::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaSymptom $record */
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

                                        $_relation = new FormulaSymptom();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaSymptom::findOne(['formula_id' => $model->id, 'symptom_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->symptom_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaSymptom();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaSymptom::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'symptom_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaSymptom $record */
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

                                $excluded = FormulaSource::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaSource $record */
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

                                        $_relation = new FormulaSource();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaSource::findOne(['formula_id' => $model->id, 'source_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->source_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaSource();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaSource::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'source_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaSource $record */
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

                                $excluded = FormulaNote::find()
                                    ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var FormulaNote $record */
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

                                        $_relation = new FormulaNote();

                                        $_relation->formula_id = $model->id;
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

                                    $_relation = FormulaNote::findOne(['formula_id' => $model->id, 'note_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()]);

                                    if ($_relation) {

                                        $ids[] = $_relation->note_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaNote();

                                        $_relation->formula_id = $model->id;
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

                            $excluded = FormulaNote::find()
                                ->where(['formula_id' => $model->id, 'is_deleted' => 0, 'created_by' => \Yii::$app->user->getId()])
                                ->andWhere(['not in', 'note_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var FormulaNote $record */
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
     * Load formula model
     *
     * @param integer|null $id
     * @param string $action
     *
     * @return Formula
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadFormula($id, $action = 'view')
    {
        $model = Formula::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Formula not found', Errors::FORMULA_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR))
                throw new ForbiddenHttpException('Formula updating is forbidden', Errors::FORMULA_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && (\Yii::$app->user->isGuest || !\Yii::$app->user->identity->hasRole(Roles::ADMINISTRATOR)))
                throw new ForbiddenHttpException('Formula viewing is forbidden', Errors::FORMULA_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
}
