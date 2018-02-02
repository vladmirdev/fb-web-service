<?php

namespace app\modules\v1\models;

use yii\data\ActiveDataProvider;

/**
 * Class FormulaSearch
 * @package api\modules\v1\models;
 */
class FormulaSearch extends Formula
{
    public $query;
    public $category = [];
    public $source = [];
    public $author = [];

    public $created;
    public $modified;
    public $fromDate;
    public $toDate;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_favorite', 'is_readonly', 'is_deleted'], 'integer'],
            [['name', 'pinyin', 'pinyin_ton', 'pinyin_code', 'english_name', 'simplified_chinese', 'traditional_chinese'], 'string'],
            [['created_by', 'modified_by'], 'safe'],
            [['query', 'category', 'source', 'author', 'created', 'modified', 'fromDate', 'toDate'], 'safe']
        ];
    }

    /**
     * Search by formulas
     *
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = parent::find();

        $this->load($params, '');

        if ($this->validate()) {

            // Id

            if (!empty($this->id)) {
                $query->andWhere(['formulas.id' => $this->id]);
            }

            // Created by single filter

            if (!empty($this->created_by)) {
                $query->andWhere(['formulas.created_by' => $this->created_by]);
            }

            // Un-deleted records

            $query->andWhere(['formulas.is_deleted' => $this->is_deleted]);

            // Name

            if (!empty($this->name)) {
                $query->andFilterWhere(['like', 'formulas.name', $this->name]);
            }

            // Query

            if (!empty($this->query)) {
                $query->andFilterWhere(['or',
                    ['like', 'formulas.name', $this->query],
                    ['like', 'formulas.english_name', $this->query],
                    ['like', 'formulas.pinyin', $this->query],
                    ['like', 'formulas.pinyin_ton', $this->query],
                    ['like', 'formulas.pinyin_code', $this->query]
                ]);
            }

            // Categories

            if(is_array($this->category) && sizeof($this->category) > 0)
                $this->category = array_filter($this->category);

            if(is_integer($this->category) || sizeof($this->category) > 0) {
                $query->leftJoin(FormulaCategory::tableName(), 'formula_category.formula_id = formulas.id');
                $query->andFilterWhere(['formula_category.category_id' => $this->category]);
                $query->andFilterWhere(['formula_category.is_deleted' => 0]);
            }

            // Source texts

            if(is_array($this->source) && sizeof($this->source) > 0)
                $this->source = array_filter($this->source);

            if(is_array($this->author) && sizeof($this->author) > 0)
                $this->author = array_filter($this->author);

            if((is_integer($this->source) || sizeof($this->source) > 0) || (is_string($this->author) || sizeof($this->author) > 0)) {

                $query->leftJoin(FormulaSource::tableName(), 'formula_source.formula_id = formulas.id');

                if(is_integer($this->source) || sizeof($this->source) > 0) {
                    $query->andFilterWhere(['formula_source.source_id' => $this->source]);
                    $query->andFilterWhere(['formula_source.is_deleted' => 0]);
                }

                if(is_string($this->author) || sizeof($this->author) > 0) {
                    $query->leftJoin(Source::tableName(), 'formula_source.source_id = source.id');
                    $query->andFilterWhere(['source.author' => $this->author]);
                    $query->andFilterWhere(['source.is_deleted' => 0]);
                }

            }

            // Created by

            if(is_integer($this->created) || sizeof($this->created) > 0) {
                $query->andFilterWhere(['formulas.created_by' => $this->created]);
            }

            // Modified by

            if(is_integer($this->modified) || sizeof($this->modified) > 0) {
                $query->andFilterWhere(['formulas.modified_by' => $this->modified]);
            }

            // From Date

            if (!empty($this->fromDate)) {
                $query->andFilterWhere(['>', 'formulas.created_time', $this->fromDate]);
            }

            // To Date

            if (!empty($this->toDate)) {
                $query->andFilterWhere(['<=', 'formulas.created_time', $this->toDate]);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'name',
                    'pinyin',
                    'created_time',
                    'modified_time',
                    'created_by'
                ],
                'defaultOrder' => [
                    'name' => SORT_ASC
                ]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);
    }
}
