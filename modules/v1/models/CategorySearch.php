<?php

namespace app\modules\v1\models;

use yii\data\ActiveDataProvider;

/**
 * Class CategorySearch
 * @package api\modules\v1\models;
 */
class CategorySearch extends Category
{
    public $query;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'color', 'type', 'is_deleted', 'created_by', 'created_time', 'modified_by', 'modified_time', 'query'], 'safe']
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
                $query->andWhere(['id' => $this->id]);
            }

            if (!empty($this->name)) {
                $query->andFilterWhere(['like', 'name', $this->name]);
            }

            if (!empty($this->query)) {
                $query->andFilterWhere(['like', 'name', $this->query]);
            }

            if (!empty($this->type)) {
                $query->andWhere(['type' => $this->type]);
            }

            if (!empty($this->created_by)) {
                $query->andWhere(['created_by' => $this->created_by]);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
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
