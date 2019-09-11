<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "stripe_accounts".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $stripe_id
 * @property string $secret_key
 * @property string $publish_key
 */
class StripeAccounts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stripe_accounts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'stripe_id', 'secret_key', 'publish_key'], 'required'],
            [['userid'], 'integer'],
            [['stripe_id', 'secret_key', 'publish_key'], 'string', 'max' => 256],
            [['userid'], 'unique']
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
            'stripe_id' => 'Stripe ID',
            'secret_key' => 'Secret Key',
            'publish_key' => 'Publish Key',
        ];
    }
}
