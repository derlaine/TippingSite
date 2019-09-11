<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stripe_plans".
 *
 * @property integer $id
 * @property integer $provider_userid
 * @property string $stripe_plan_id
 * @property integer $num_of_plan_subscribers
 * @property string $amount
 * @property string $interval_type
 * @property integer $interval_count
 * @property string $stripe_account_id
 */
class StripePlans extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stripe_plans';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_userid', 'stripe_plan_id', 'num_of_plan_subscribers', 'amount', 'interval_type', 'interval_count', 'stripe_account_id'], 'required'],
            [['provider_userid', 'num_of_plan_subscribers', 'interval_count', 'require_address'], 'integer'],
            [['amount'], 'number'],
            [['stripe_plan_id', 'stripe_account_id'], 'string', 'max' => 256],
            [['thankyou_text', 'description'], 'string', 'max'=>500],
            [['interval_type'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_userid' => 'Provider Userid',
            'stripe_plan_id' => 'Stripe Plan ID',
            'num_of_plan_subscribers' => 'Num Of Plan Subscribers',
            'amount' => 'Amount',
            'interval_type' => 'Interval Type',
            'interval_count' => 'Interval Count',
            'stripe_account_id' => 'Stripe Account ID',
            'require_address' => 'Require Shipping Address?',
            'description' => 'Description',
            'thankyou_text' => 'Thank You Message'
        ];
    }
}
