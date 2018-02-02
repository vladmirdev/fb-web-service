<?php

namespace app\modules\v1\models;

use yii\data\ActiveDataProvider;

/**
 * Class UserSearch
 * @package api\modules\v1\models;
 */
class UserSearch extends User
{
    public $query;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'firstname', 'lastname', 'email', 'country_id', 'language_id', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'query'], 'safe']
        ];
    }

    /**
     * Search users
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
                $query->andFilterWhere(['id' => $this->id]);
            }

            if (!empty($this->firstname)) {
                $query->andFilterWhere(['like', 'firstname', $this->firstname]);
            }

            if (!empty($this->lastname)) {
                $query->andFilterWhere(['like', 'lastname', $this->lastname]);
            }

            if (!empty($this->email)) {
                $query->andFilterWhere(['like', 'email', $this->email]);
            }

            if (!empty($this->country_id)) {
                $query->andFilterWhere(['country_id' => $this->country_id]);
            }

            if (!empty($this->query)) {
                $query->andFilterWhere(['or',
                    ['like', 'firstname', $this->query],
                    ['like', 'lastname', $this->query],
                    ['like', 'email', $this->query],
                    ]);
            }

            if (!empty($this->is_deleted)) {
                $query->andWhere(['is_deleted' => $this->is_deleted]);
            }

            if (!empty($this->created_by)) {
                $query->andWhere(['created_by' => $this->created_by]);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC
                ]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);
    }
}
