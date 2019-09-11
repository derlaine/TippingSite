<?php

namespace frontend\models;

use yii\base\Model;
use common\models\User;
use frontend\models\StripeAccounts;

use Yii;

//a Stripe Account is for saving bank account info so we can transfer funds to a provider (of services/art/etc)
//EDGE CASES to check for: what if we already have a stripe id 
//updating bank account info
// updating the stripe id? will this ever happen?


class addStripeAccountForm extends Model
{
   
    public $userid;
    public $first_name;
    public $last_name;
    public $dob;
    public $legal_entity_type; //1 = individual , 2 = company
    public $tos; 
    public $tos_acceptance_date; 
    public $tos_acceptance_ip; 

    public $state; 
    public $city; 
    public $postal_code; 
    public $line1; //address line 1

    public $ssn_last_4; 
    public $external_account; 
    public $stripeToken; 
 
    //how to collect the bank account number and routing number without me touching them?????? need to read more Stripe documents

    public function addStripeAccount()
    {
        //TODO LATER:have to somehow sanitize date of birth so it's valid Date value
        //read the Yii docs for some built in way to sanitize date input
        $dob = strtotime($this->dob);
        list($dob_month, $dob_day, $dob_year) = explode('/', $this->dob);

        $user = User::findOne( Yii::$app->user->identity->id);

        if(!$user){
            return FALSE;
        }

        $user->first_name= $this->first_name;
        $user->last_name= $this->last_name;
        $user->dob= $dob;
        if(!$user->save()){
            return FALSE;
        }

        //check if we already have a userid in the table
        $stripeacc = StripeAccounts::findOne( ['userid'=>Yii::$app->user->identity->id]);
  
        //already has account, update stripe instead of making a new one
        if ($stripeacc ){

            \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);

            $stacc = \Stripe\Account::retrieve($stripeacc->stripe_id);
            $stacc->external_account = $this->stripeToken;
            $stacc->legal_entity['first_name'] = $this->first_name;
            $stacc->legal_entity['last_name'] = $this->last_name;
            $stacc->legal_entity['type'] = $this->legal_entity_type;
            $stacc->legal_entity['ssn_last_4'] = $this->ssn_last_4;
            $stacc->legal_entity['dob']['month'] = $dob_month;
            $stacc->legal_entity['dob']['day'] = $dob_day;
            $stacc->legal_entity['dob']['year'] = $dob_year;
            $stacc->legal_entity['address']['city'] = $this->city;
            $stacc->legal_entity['address']['state'] = $this->state;
            $stacc->legal_entity['address']['postal_code'] = $this->postal_code;
            $stacc->legal_entity['address']['line1'] = $this->line1;
            $stacc->save();

            return TRUE;

        }
        

        //tell Stripe to make a new Stripe Account
        //we must get the Stripe Account ID and Keys to save into our own database
        $stripeAccount = null;
         try {
            //set platform secret key
             \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);


            //if no legal entity or tos, throw exception

             $stripeAccount =  \Stripe\Account::create(
                          array(
                            "country" => "US",
                            "managed" => true,
                            "legal_entity[first_name]" => $this->first_name,
                            "legal_entity[last_name]" => $this->last_name,
                            "legal_entity[dob][month]" => $dob_month,
                            "legal_entity[dob][day]" => $dob_day,
                            "legal_entity[dob][year]" => $dob_year,
                            "legal_entity[type]" => $this->legal_entity_type, 
                            "tos_acceptance[date]" => time(), 
                            "tos_acceptance[ip]" => $_SERVER['REMOTE_ADDR'], 
                            "legal_entity[address][city]" => $this->city,
                            "legal_entity[address][state]" => $this->state,
                            "legal_entity[address][postal_code]" => $this->postal_code,
                            "legal_entity[address][line1]" => $this->line1,
                            "legal_entity[ssn_last_4]" => $this->ssn_last_4,
                            "external_account" => $this->stripeToken,

                          )
                        );

        }catch(\Stripe\Error\Card $e) {
            return FALSE;
        }

     

        try{
             //let's save the Stripe Account ID and Keys into our own database. These cannot be retrieved if we don't save it now.
            $stripedb = new StripeAccounts();
            $stripedb->stripe_id = $stripeAccount->id;
            $stripedb->userid = Yii::$app->user->identity->id;
            $stripedb->secret_key = $stripeAccount->keys->secret;
            $stripedb->publish_key = $stripeAccount->keys->publishable;
            
            if(!$stripedb->save()){
                var_dump($stripedb->getErrors());
            }

        }

        catch (Exception $e){

            var_dump($e->getMessage());
        }
       
        return TRUE;

    }

    public static function tableName()
    {
        return 'stripe_accounts';
    }

    /**
     * @inheritdoc
     Legal Entity Type has to be a string because Stripe only accepts strings for it.
     */
    public function rules()
    {
        return [
            [['userid', 'stripe_id', 'secret_key', 'publish_key'], 'required'],
            [['userid', ], 'integer'], 
            [['stripe_id', 'secret_key', 'publish_key', 'first_name', 
                'last_name', 'dob', 'legal_entity_type', 'city', 'state', 
                'line1', 'postal_code', 'ssn_last_4', 'stripeToken'], 'string', 'max' => 256],
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
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'dob' => 'Date of Birth (month/day/year)',
            'legal_entity_type' => 'Legal Entity Type',
            'tos' => 'I have read and agreed to the Terms of Service',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => 'Postal Code',
            'line1' => 'Address Line 1',
            'ssn_last_4' => 'Last 4 Numbers of SSN',
            'stripeToken' => 'Stripe Token'
        ];
    }
}
