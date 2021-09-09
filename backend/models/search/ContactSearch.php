<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Contact;

/**
 * ContactSearch represents the model behind the search form about `common\models\Contact`.
 */
class ContactSearch extends Contact
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pollguru', 'buzz', 'learning_arcade', 'training_pipeline', 'leadership_edge'], 'integer'],
            [['firstname', 'lastname', 'email', 'company', 'website','created_by', 'mobile_number', 'birthday', 'updated_at', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Contact::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize'=> isset(Yii::$app->params['defaultPageSize']) ? Yii::$app->params['defaultPageSize'] : 10,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->joinWith(['createdBy']);

        $query->andFilterWhere([
            'id' => $this->id,
            'birthday' => $this->birthday,
            'pollguru' => $this->pollguru,
            'buzz' => $this->buzz,
            'learning_arcade' => $this->learning_arcade,
            'training_pipeline' => $this->training_pipeline,
            'leadership_edge' => $this->leadership_edge,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'firstname', $this->firstname])
            ->andFilterWhere(['like', 'lastname', $this->lastname])
            ->andFilterWhere(['like', 'tbl_contact.email', $this->email])
            ->andFilterWhere(['like', 'company', $this->company])
            ->andFilterWhere(['like', 'website', $this->website])
            ->andFilterWhere(['like', 'mobile_number', $this->mobile_number])
            ->andFilterWhere(['like', 'tbl_user.username', $this->created_by]);
        return $dataProvider;
    }
}
