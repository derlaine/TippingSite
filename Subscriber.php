<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subscriber".
 *
 * @property integer $id
 * @property string $userid
 * @property string $subscribe_to
 * @property string $amount
 * @property string $connected_customer_id
 * @property string $platform_customer_id
 * @property string $interval_type
 * @property integer $interval_count
 * @property string $connected_stripe_id
 * @property string $plan_id
 * @property string $subscription_id
 */
class Subscriber extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriber';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'subscribe_to', 'amount', 'connected_customer_id', 'platform_customer_id', 'interval_type', 'interval_count', 'connected_stripe_id', 'plan_id', 'subscription_id'], 'required'],
            [['userid', 'subscribe_to', 'interval_count'], 'integer'],
            [['amount'], 'number'],
            [['connected_customer_id', 'platform_customer_id', 'interval_type', 'connected_stripe_id', 'plan_id', 'subscription_id'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userid' => 'Userid',
            'subscribe_to' => 'Subscribe To',
            'amount' => 'Amount',
            'connected_customer_id' => 'Connected Customer ID',
            'platform_customer_id' => 'Platform Customer ID',
            'interval_type' => 'Interval Type',
            'interval_count' => 'Interval Count',
            'connected_stripe_id' => 'Connected Stripe ID',
            'plan_id' => 'Plan ID',
            'subscription_id' => 'Subscription ID',
        ];
    }
}
