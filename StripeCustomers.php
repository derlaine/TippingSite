<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "stripe_customers".
 *
 * @property string $userid
 * @property string $stripe_customer_id
 */
class StripeCustomers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stripe_customers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'stripe_customer_id'], 'required'],
            [['userid'], 'integer'],
            [['stripe_customer_id'], 'string', 'max' => 256],
            [['userid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userid' => 'Userid',
            'stripe_customer_id' => 'Stripe Customer ID',
        ];
    }
}
