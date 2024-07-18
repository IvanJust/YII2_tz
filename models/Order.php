<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property int $seller_id
 * @property string $date_sold
 * @property int $qty
 * @property int $order_sum
 *
 * @property Seller $seller
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seller_id', 'date_sold', 'qty', 'order_sum'], 'required'],
            [['seller_id', 'qty', 'order_sum'], 'integer'],
            [['date_sold'], 'safe'],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => Seller::className(), 'targetAttribute' => ['seller_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'seller_id' => 'ID продавца',
            'date_sold' => 'Дата продажи',
            'qty' => 'Количество',
            'order_sum' => 'Сумма заказа',
        ];
    }

    /**
     * Gets query for [[Seller]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(Seller::className(), ['id' => 'seller_id']);
    }
}