<?php

namespace app\modules\v1\models;

use app\models\Country;
use yii\data\ActiveDataProvider;

/**
 * Class CountrySearch
 *
 * @package api\modules\v1\models;
 */
class CountrySearch extends Country
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'is_deleted', 'created_by', 'modified_by'], 'integer'],
            [['name', 'full_name', 'code', 'iso3', 'continent_code'], 'string'],
        ];
    }

    /**
     * Search
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

            if (!empty($this->id)) {
                $query->andWhere(['id' => $this->id]);
            }

            if (!empty($this->created_by)) {
                $query->andWhere(['created_by' => $this->created_by]);
            }

            if (!empty($this->is_deleted)) {
                $query->andWhere(['is_deleted' => $this->is_deleted]);
            }

            if (!empty($this->name)) {
                $query->andWhere(['like', 'name', $this->name]);
            }

            if (!empty($this->full_name)) {
                $query->andWhere(['like', 'name', $this->full_name]);
            }

            if (!empty($this->is_deleted)) {
                $query->andWhere(['code' => $this->code]);
            }

            if (!empty($this->iso3)) {
                $query->andWhere(['iso3' => $this->iso3]);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'display_order' => SORT_ASC
                ]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);
    }
}
