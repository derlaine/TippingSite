<?php


namespace frontend\models;

use yii\base\Model;
use common\models\User;
use frontend\models\StripeAccounts;

use Yii;


class tipForm extends Model
{
   
    public $provider_id;
    public $stripeToken; 
    public $amount; 

    public function rules()
    {
        return [
        /*
            [['provider_id', 'secret_key', 'publish_key'], 'required'],
            [['provider_id', ], 'integer'], 
            [[ 'secret_key', 'publish_key', 'stripeToken'], 'string', 'max' => 256],
            [['provider_id'], 'unique']
            */

              [['provider_id', 'stripeToken'], 'required'],
            [['provider_id', 'amount'], 'integer', 'min'=>1], 
            [[ 'stripeToken'], 'string', 'max' => 256],
           
        ];
    }



 }
 

?>