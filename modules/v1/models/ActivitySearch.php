<?php

namespace app\modules\v1\models;

use yii\data\ActiveDataProvider;

/**
 * Class ActivitySearch
 * @package api\modules\v1\models;
 */
class ActivitySearch extends Activity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'obj_id'], 'integer'],
            [['type', 'action', 'created_time'], 'safe'],
        ];
    }

    /**
     * Search
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = parent::find();

        $this->load($params, '');

        if ($this->validate()) {

            if (!empty($this->created_by)) {
                $query->andWhere(['created_by' => $this->created_by]);
            }

            if (!empty($this->obj_id)) {
                $query->andWhere(['obj_id' => $this->obj_id]);
            }

            if (!empty($this->type)) {
                $query->andWhere(['type' => $this->type]);
            }

            if (!empty($this->action)) {
                $query->andWhere(['action' => $this->action]);
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' =>
                    [
                        'created_time' => SORT_DESC
                    ]
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => \Yii::$app->params['pageSizeLimit']
            ]
        ]);
    }
}
