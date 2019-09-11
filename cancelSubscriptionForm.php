<?php

namespace frontend\models;

use yii\base\Model;

use Yii;


/**
 * This is the model class for table "stripe_plans".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $name
 * @property string $stripe_plan_id
 */
class cancelSubscriptionForm extends Model
{

    public $userid;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'userid'], 'required'],
            [[ 'userid'], 'integer'],
           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userid' => 'Userid',
        ];
    }
}
