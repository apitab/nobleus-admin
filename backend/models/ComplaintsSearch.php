<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Complaints;

/**
 * ComplaintsSearch represents the model behind the search form of `backend\models\Complaints`.
 */
class ComplaintsSearch extends Complaints
{
    /**
     * @var string Phone number for filtering
     */
    public $phone_number;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'order_id', 'status'], 'integer'],
            [['type', 'title', 'description', 'date_created', 'date_modified', 'phone_number'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Complaints::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Filter by type
        if (!empty($this->type)) {
            $query->andFilterWhere(['complaints.type' => $this->type]);
        }

        // Filter by phone number based on type
        if (!empty($this->phone_number)) {
            if ($this->type === Complaints::TYPE_CUSTOMERS) {
                // Join with customers table and filter by phone_number
                $query->joinWith(['customer']);
                $query->andFilterWhere(['like', 'customers.phone_number', $this->phone_number]);
            } elseif ($this->type === Complaints::TYPE_VENDORS) {
                // Join with vendors table and filter by mobile_number
                // customer_id is used as vendor_id when type is vendors
                $query->joinWith(['vendor']);
                $query->andFilterWhere(['like', 'vendors.mobile_number', $this->phone_number]);
            } else {
                // If no type specified but phone_number is provided, try both
                $query->joinWith(['customer']);
                $query->leftJoin('vendors', 'complaints.customer_id = vendors.id AND complaints.type = :vendorType', [':vendorType' => Complaints::TYPE_VENDORS]);
                $query->andFilterWhere([
                    'or',
                    ['like', 'customers.phone_number', $this->phone_number],
                    ['like', 'vendors.mobile_number', $this->phone_number]
                ]);
            }
        } else {
            // Always join both for display purposes
            $query->joinWith(['customer']);
            $query->leftJoin('vendors', 'complaints.customer_id = vendors.id AND complaints.type = :vendorType', [':vendorType' => Complaints::TYPE_VENDORS]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'complaints.id' => $this->id,
            'complaints.customer_id' => $this->customer_id,
            'complaints.status' => $this->status,
            'complaints.date_created' => $this->date_created,
            'complaints.date_modified' => $this->date_modified,
        ]);

        $query->andFilterWhere(['like', 'complaints.title', $this->title]);

        return $dataProvider;
    }
}
