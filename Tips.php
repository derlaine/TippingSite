<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tips".
 *
 * @property integer $id
 * @property integer $userid
 * @property integer $provider_id
 * @property integer $tip_amount
 * @property integer $timestamp
 */
class Tips extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tips';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'provider_id', 'tip_amount', 'timestamp'], 'required'],
            [['userid', 'provider_id', 'tip_amount', 'timestamp'], 'integer']
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
            'provider_id' => 'Provider ID',
            'tip_amount' => 'Tip Amount',
            'timestamp' => 'Timestamp',
        ];
    }
}
