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
use app\helpers\Security;
use app\models\User;
use app\modules\v1\models\Action;
use app\modules\v1\models\ActionCategory;
use app\modules\v1\models\Category;
use app\modules\v1\models\FormulaAction;
use app\modules\v1\models\FormulaSymptom;
use app\modules\v1\models\HerbAction;
use app\modules\v1\models\HerbSymptom;
use app\modules\v1\models\Symptom;
use app\modules\v1\models\SymptomCategory;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SymptomForm extends Model
{
    public $newRecord = false;

    public $id;

    public $name;
    public $simplified_chinese;
    public $traditional_chinese;
    public $color;

    public $categories;
    public $formulas;
    public $herbs;

    protected $_relations = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'required'],
            [['name', 'simplified_chinese', 'traditional_chinese'], 'string', 'max' => 500],
            [['color'], 'string', 'max' => 7],
            [['categories', 'formulas', 'herbs'], 'safe']
        ];
    }

    /**
     * Save symptom and create/update related records
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
        $model = new Symptom();

        $related = [];

        if ($this->id)
            $model = $this->loadSymptom($this->id, Actions::UPDATE);

        $transaction = Symptom::getDb()->beginTransaction();

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

                                $excluded = SymptomCategory::find()
                                    ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                    ->all();

                                if(sizeof($excluded) > 0) {

                                    /** @var SymptomCategory $record */
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
                                    $_model->type = Symptom::ITEM_TYPE;

                                    if ($_model->save()) {

                                        $ids[] = $_model->id;

                                        $_relation = new SymptomCategory();

                                        $_relation->symptom_id = $model->id;
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

                                    $_relation = SymptomCategory::findOne(['symptom_id' => $model->id, 'category_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->category_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new SymptomCategory();

                                        $_relation->symptom_id = $model->id;
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

                            $excluded = SymptomCategory::find()
                                ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'category_id', $ids])
                                ->all();

                            if(sizeof($excluded) > 0) {

                                /** @var SymptomCategory $record */
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

                                $excluded = FormulaSymptom::find()
                                    ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
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

                                // Create new formula

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign formula without ID');

                                } else {

                                    $_relation = FormulaSymptom::findOne(['symptom_id' => $model->id, 'formula_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->formula_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new FormulaSymptom();

                                        $_relation->symptom_id = $model->id;
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

                            $excluded = FormulaSymptom::find()
                                ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'formula_id', $ids])
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

                        case 'herbs':

                            $ids = [];
                            $relations = [];

                            if(!$model->isNewRecord && (!$value || sizeof($value) == 0)) {

                                $excluded = HerbSymptom::find()
                                    ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
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

                                // Create new herb

                                if (!isset($relation['id'])) {

                                    $this->addError($attribute, 'Cannot assign herb without ID');

                                } else {

                                    $_relation = HerbSymptom::findOne(['symptom_id' => $model->id, 'herb_id' => $relation['id'], 'is_deleted' => 0, 'created_by' => $model->created_by]);

                                    if ($_relation) {

                                        $ids[] = $_relation->herb_id;
                                        $_relation->update();

                                    } else {

                                        $_relation = new HerbSymptom();

                                        $_relation->symptom_id = $model->id;
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

                            $excluded = HerbSymptom::find()
                                ->where(['symptom_id' => $model->id, 'is_deleted' => 0, 'created_by' => $model->created_by])
                                ->andWhere(['not in', 'herb_id', $ids])
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
        return ['created_by', 'modified_by', 'created_time', 'modified_time', 'is_deleted'];
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
     * Load symptom model
     *
     * @param integer|null $id
     * @param string $action
     *
     * @return Symptom
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    private function loadSymptom($id, $action = Actions::VIEW)
    {
        $model = Symptom::findOne(['id' => $id, 'is_deleted' => 0]);

        if(!$model)
            throw new NotFoundHttpException('Symptom not found', Errors::SYMPTOM_NOT_FOUND);

        if($action == 'update' || $action == 'delete') {

            if($model->created_by != \Yii::$app->user->getId() && !Security::isAdmin())
                throw new ForbiddenHttpException('Symptom updating is forbidden', Errors::SYMPTOM_UPDATING_IS_FORBIDDEN);
        }

        if($action == 'view') {

            if($model->created_by != User::SYSTEM && $model->created_by != \Yii::$app->user->getId() && (\Yii::$app->user->isGuest || !Security::isAdmin()))
                throw new ForbiddenHttpException('Symptom viewing is forbidden', Errors::SYMPTOM_VIEWING_IS_FORBIDDEN);
        }

        return $model;
    }
}
