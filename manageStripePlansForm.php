<?php

namespace frontend\models;

use yii\base\Model;
use common\models\User;
use app\models\StripePlans;

use Yii;


/**
 * This is the model class for table "stripe_plans".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $name
 * @property string $stripe_plan_id
 */
class manageStripePlansForm extends Model
{

    public $name;
    public $amount;
    public $interval;
    public $interval_count;
    public $description;
    public $require_address;
    public $thankyou_text;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stripe_plans';
    }


    public function savePlan($stripeacc=null)
    {

        //make sure this user has a Stripe account. If they don't have one, fail out.
        if(!$stripeacc){
            throw new Exception('You need to have a Stripe account and enable Stripe payments before you can manage your plans');
        }

        $id = $this->name;

        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);


        //create the plan on the Stripe server
         $planplan = \Stripe\Plan::create(array(
          'amount' => $this->amount*100,
          'name' => $this->name,
          'currency' => "usd",
          'interval' => $this->interval,
          'interval_count'=> $this->interval_count,
          'id' => $id
        ), array('stripe_account' => $stripeacc->stripe_id));


         //let's save the plans onto our own db, esp for num_of_plan_subscribers
        $sp_db = new StripePlans();
        $sp_db->provider_userid = $stripeacc->userid;
        $sp_db->amount = $this->amount*100;
        $sp_db->interval_type = $this->interval;
        $sp_db->interval_count = $this->interval_count;
        $sp_db->stripe_plan_id = $id;
        $sp_db->num_of_plan_subscribers = 0;
        $sp_db->stripe_account_id = $stripeacc->stripe_id;

        $sp_db->description = $this->description;
        $sp_db->require_address = $this->require_address;
        $sp_db->thankyou_text = $this->thankyou_text;

        if(!$sp_db->save()){
            var_dump($sp_db->getErrors());
        }

        return TRUE;
    }



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'userid', 'name', 'stripe_plan_id','interval_count', 'interval', 'amount'], 'required'],
            [[ 'userid', 'interval_count','require_address'], 'integer'],
            [['amount'], 'double'],
            [['name', 'stripe_plan_id', 'interval'], 'string', 'max' => 256],
             [['thankyou_text', 'description'], 'string', 'max'=>500],

            //Stripe cannot accept interval_count of more than 1 when it is 'year'
            [ ['interval_count'], 'integer', 'max'=>1,  'when'=> function($model){ return $model->interval == 'year'; }, 
                'whenClient' => "function (attribute, value) {
                                     return $('.interval-select').val() == 'year';}" , 
                'message'=>'Cannot be more than 1 year',
            ]
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
            'name' => '',
            'stripe_plan_id' => 'Stripe Plan ID',
            'amount' => 'Amount',
            'interval' => 'Interval',
            'interval_count' => '',
             'require_address' => 'Require Shipping Address?',
            'description' => '',
            'thankyou_text' => ''
        ];
    }
}
