<?php

namespace app\modules\v1\models;

use yii\data\ActiveDataProvider;

/**
 * Class HerbSearch
 * @package api\modules\v1\models;
 */
class HerbSearch extends Herb
{
    public $query;
    public $category = [];
    public $specie = [];
    public $flavour = [];
    public $nature = [];

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
            [['is_deleted'], 'integer'],
            [['name', 'pinyin', 'pinyin_code', 'english_name', 'simplified_chinese', 'traditional_chinese', 'latin_name', 'english_common'], 'string'],
            [['created_by', 'modified_by'], 'safe'],
            [['query', 'category', 'specie', 'flavour', 'nature', 'created', 'modified', 'fromDate', 'toDate'], 'safe']
        ];
    }

    /**
     * Search by herbs
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
                $query->andWhere(['herbs.id' => $this->id]);
            }

            // Created by single filter

            if (!empty($this->created_by)) {
                $query->andWhere(['herbs.created_by' => $this->created_by]);
            }

            // Un-deleted records

            $query->andWhere(['herbs.is_deleted' => $this->is_deleted]);

            // Name

            if (!empty($this->name)) {
                $query->andFilterWhere(['like', 'herbs.name', $this->name]);
            }

            // Query

            if (!empty($this->query)) {
                $query->andFilterWhere(['or',
                    ['like', 'herbs.name', $this->query],
                    ['like', 'herbs.english_name', $this->query],
                    ['like', 'herbs.english_common', $this->query],
                    ['like', 'herbs.pinyin', $this->query],
                    ['like', 'herbs.pinyin_code', $this->query],
                    ['like', 'herbs.latin_name', $this->query]
                ]);
            }

            // Categories

            if(is_array($this->category) && sizeof($this->category) > 0)
                $this->category = array_filter($this->category);

            if(is_integer($this->category) || sizeof($this->category) > 0) {
                $query->leftJoin(HerbCategory::tableName(), 'herb_category.herb_id = herbs.id');
                $query->andFilterWhere(['herb_category.category_id' => $this->category]);
                $query->andFilterWhere(['herb_category.is_deleted' => 0]);
            }

            // Species

            if(is_array($this->specie) && sizeof($this->specie) > 0)
                $this->specie = array_filter($this->specie);

            if((is_integer($this->specie) || sizeof($this->specie) > 0)) {

                $query->leftJoin(HerbSpecies::tableName(), 'herb_species.herb_id = herbs.id');

                $query->andFilterWhere(['herb_species.species_id' => $this->specie]);
                $query->andFilterWhere(['herb_species.is_deleted' => 0]);

            }

            // Natures

            if(is_array($this->nature) && sizeof($this->nature) > 0)
                $this->nature = array_filter($this->nature);

            if((is_integer($this->nature) || sizeof($this->nature) > 0)) {

                $query->leftJoin(HerbNature::tableName(), 'herb_nature.herb_id = herbs.id');

                $query->andFilterWhere(['herb_nature.nature_id' => $this->nature]);
                $query->andFilterWhere(['herb_nature.is_deleted' => 0]);

            }

            // Flavours

            if(is_array($this->flavour) && sizeof($this->flavour) > 0)
                $this->flavour = array_filter($this->flavour);

            if((is_integer($this->flavour) || sizeof($this->flavour) > 0)) {

                $query->leftJoin(HerbFlavour::tableName(), 'herb_flavour.herb_id = herbs.id');

                $query->andFilterWhere(['herb_flavour.flavour_id' => $this->flavour]);
                $query->andFilterWhere(['herb_flavour.is_deleted' => 0]);

            }

            // Created by

            if(is_integer($this->created) || sizeof($this->created) > 0) {
                $query->andFilterWhere(['herbs.created_by' => $this->created]);
            }

            // Modified by

            if(is_integer($this->modified) || sizeof($this->modified) > 0) {
                $query->andFilterWhere(['herbs.modified_by' => $this->modified]);
            }

            // From Date

            if (!empty($this->fromDate)) {
                $query->andFilterWhere(['>', 'herbs.created_time', $this->fromDate]);
            }

            // To Date

            if (!empty($this->toDate)) {
                $query->andFilterWhere(['<=', 'herbs.created_time', $this->toDate]);
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
