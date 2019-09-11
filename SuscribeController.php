<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 5/26/15
 * Time: 10:24 AM
 */

namespace frontend\controllers;

use Stripe\Stripe;
use yii\web\Controller;
use Yii;
use frontend\models\StripeAccounts;
use frontend\models\StripeCustomers;
use frontend\models\cancelSubscriptionForm;
use app\models\User;
use app\models\Tips;
use app\models\userStats;
use yii\web\HttpException;
use app\models\Subscriber;
use app\models\StripePlans;




class SuscribeController extends Controller{


    //customers (payer): people who pay for goods and services. they have credit cards
    //providers (payee) : they have bank accounts and Managed Stripe Accounts you pay out to.
    //Stripe Account ID : the provider/contractor/uber driver
    //Stripe Customer ID:  the person who wants to subscribe
    //Stripe API Key: My secret password to talk to Stripe
    public function actionSuscribe($userid){

        //setup variables
        $stripe_id = null;
        $stripeAccountID = null;
        $stripe_customer_id=null;
        $allPlans=null;
        $already=null;
        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $customerConnected = null;

        //get a list of all the  plans that the provider provides
                  //get the Stripe Account ID we saved earlier. 
                //you get the Stripe Account ID when the user requested to become a provider and we sent a 
                //Managed Account Creation call to Stripe.
                //see: Profiles > Enable Support

        //get the Stripe Account ID of the provider
        $stripeAccounts = StripeAccounts::find()->where(['userid' => $userid] )->one();
 
        if(!$stripeAccounts){
            throw new HttpException('You need a Stripe Account first');
        }

        $stripeAccountID = $stripeAccounts->stripe_id;

        $allPlans = \Stripe\Plan::all([],['stripe_account'=> $stripeAccountID]);

        if($_POST && $_POST['plan_info']){  
                $selected_plan = unserialize( base64_decode( $_POST['plan_info']));
        }

        //if user is already a subscriber, offer to change plans here
        //for now, just say "you are already subscribed"

        $sub = Subscriber::findOne(['userid'=>Yii::$app->user->identity->id , 'subscribe_to'=>$userid]);
        if($sub){
            $already = TRUE;
        }

        if(!$_POST){
            return $this->render('suscribe', ['success'=>null, 'allPlans'=>$allPlans['data'], 'already'=>$already , 
                'subscribe_to'=>$userid ]);
        }

        //look in our databases, see if the user already has a customer id with stripe
        $stripe_user = StripeCustomers::find()->where(['userid' => Yii::$app->user->identity->id] )->one();

        if($stripe_user){
             $stripe_customer_id = $stripe_user->stripe_customer_id;
        }


                //***edge case: the user has a row on our server, but no entry on the stripe side. What to do?
                //***ideally: delete our row and create from scratch

        //if the user does not have have a customer account, make a call to stripe to create it
        elseif(!$stripe_user)
        {
            try{
                  //create the customer on our platform
                $customer = \Stripe\Customer::create(array(
                          "source" => $_POST['stripeToken'], //Stripe Checkout talked to Stripe and came back with this token
                          "email" => Yii::$app->user->identity->email));
            } 

            catch (\Stripe\Error\ApiConnection $e) {
                // Network problem, perhaps try again.
            } catch (\Stripe\Error\InvalidRequest $e) {
                //sent bad data, need to validate on our end

                 //***if the customer does not exist on the Stripe server, but on ours; then we should 
                //***delete ours and then move on to make a new Stripe customer from scratch.
                $e->getMessage();

            } catch (\Stripe\Error\Api $e) {
                // Stripe's servers are down!
            } catch (\Stripe\Error\Card $e) {
                // Card was declined.
            } 

 
                    //***if this fails, how to maintain data integrity of duplicate users above?
                    //***if we run into problems in the future just delete and create again

            //save the new platform customer info onto our database
            $stripedb = new StripeCustomers();
            $stripedb->stripe_customer_id = $customer->id;
            $stripedb->userid = Yii::$app->user->identity->id;

            if(!$stripedb->save()){

                throw new HttpException('Could not save Stripe Account, please contact help ');
            }

            //finally we get a fresh new customer id
            $stripe_customer_id = $customer->id;
          
        } 


        //now that we have a customer_id (from our platform), let's subscribe the customer to the provider
        try{

                    //the customer only exists on our platform, it needs permission to talk to the 
                    //connected account. 

            //***if the customer exists on our database but not on Stripe,
            //***data is either corrupt, or we could not contact Stripe server
            //***the user should keep trying and if it continues failing, 
            //****they should contact us for customer support, and then we delete 
            //*** our copy and the user starts from scratch.

            //create a token for the customer and the connected account
            $cu_token = \Stripe\Token::create( array("customer" => $stripe_customer_id),
                                          array("stripe_account" =>  $stripeAccountID));


            //create the customer (again) on the connected account
              $customerConnected= \Stripe\Customer::create(array(
                      "source" => $cu_token, 
                      "email" => Yii::$app->user->identity->email,
                      "metadata" => array("original_customer_id" => $stripe_customer_id )),
                    array( "stripe_account" => $stripeAccountID)
                    );


             //add the provider's subscription plan to the customer that now also lives the connected account
             $subs = $customerConnected->subscriptions->create( array(
                        "plan" => $selected_plan['plan_id'],
                        "application_fee_percent" => 10,
                        ), array("stripe_account" => $stripeAccountID)
                );



            } catch(\Stripe\Error\Card $e) {
                    //there are a bunch of errors to catch here

            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new HttpException($e->getMessage());
            }


         //save our own record of who subscribed to whom
        //Stripe doesn't know what customers subscribe to, the Shared Customer is just a 
        //way to pass around credit card info 
        $subscriber_db = new Subscriber();
        $subscriber_db->userid=Yii::$app->user->identity->id;
        $subscriber_db->subscribe_to = $userid;
        $subscriber_db->amount = $selected_plan['amount'];
        $subscriber_db->interval_type = $selected_plan['interval'];
        $subscriber_db->interval_count = $selected_plan['interval_count'];
        $subscriber_db->plan_id = $selected_plan['plan_id'];
        $subscriber_db->connected_customer_id = $customerConnected->id;
        $subscriber_db->platform_customer_id = $stripe_customer_id;
        $subscriber_db->connected_stripe_id = $stripeAccountID;
        $subscriber_db->subscription_id = $subs->id;

        if(!$subscriber_db->save()){
            throw new HttpException('Could not add to subscriber table, please contact help ');
        }

        //increment user_stats table
        $userstat = UserStats::findOne(['userid'=>$userid]);

        if(!$userstat){
            throw new HttpException('User has no stats, contact support');
        } 

        $userstat->updateCounters(['total_subscribers' => 1]);

        //update the total number of subscribers on the plan 
        $sp_db = StripePlans::findOne(['provider_userid'=> $userid, 'stripe_plan_id'=>$selected_plan['plan_id']]);

        var_dump($sp_db);
        $sp_db->updateCounters(['num_of_plan_subscribers'=>1]);
        

        return $this->render('suscribe', ['success'=>true , 'allPlans'=>$allPlans['data'], 'subscribe_to'=>$userid]);

    } //end of function


    public function actionUpdateSubscription()
    {

        //retrieve the customer id from our db. 
        $cus = StripeCustomers::findOne(['userid'=> Yii::$app->user->identity->id ]);
        if(!$cus){
            throw new HttpException('No such customer');
        }

        //retrieve the plan info
        $subscriptions = Subscriber::findAll(['userid'=> Yii::$app->user->identity->id,
                                              'plan_id' => $_POST['plan_id']]);

        $subscription_id = $subscriptions->subscription_id;
        $customer_id = $subscriptions->connected_customer_id;
        $connectedStripeAccount = $subscriptions->connected_stripe_id;


        $cu = \Stripe\Customer::retrieve($customer_id , ['stripe_account'=> $connectedStripeAccount]);
        $subscription = $cu->subscriptions->retrieve($subscription_id);
        $subscription->plan = $newplan;
        $subscription->save();

    }

        //eventually should be changed to javascript AJAX 
    public function actionCancelSubscription($userid){

        //what i have
        //my userid
        //the provider's userid

        //what i need
        //connected_stripe_id of the provider
        //connected_customer_id of me 

        $form = new cancelSubscriptionForm();

         $user = User::find()->select('username,id')->where(['id'=>$userid])->one();

        //are you sure? yes no
        if (!$form->load(Yii::$app->request->post()))
        {
           

             return  $this->render('cancelSubscription', ['unsubscribe'=>true , 'formModel'=>$form,
                                        'username'=>$user->username, 'userid'=>$user->id, 'unsubscribe'=>FALSE]);
        } //end of if load form succesful

       
        //user confirmed yes to unsubscribe

        $delete_sub = Subscriber::findOne(['userid'=>Yii::$app->user->identity->id, 
                              'subscribe_to'=>$form->userid]);  

        if(!$delete_sub){
            throw new HttpException('No such subscription to delete, find help');
        }

        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);

        //retrieve the connected customer
        $customerConnected=\Stripe\Customer::retrieve($delete_sub->connected_customer_id
                            , array("stripe_account" => $delete_sub->connected_stripe_id));

        //delete on Stripe
        $customerConnected->subscriptions->retrieve($delete_sub->subscription_id)->cancel();

         //delete our database row       
        if(!$delete_sub->delete()){
            throw new HttpException('Could not delete subscription from database');
        }

         //decrement user stat
        $us = UserStats::findOne(['userid'=>$form->userid]);
        $us->updateCounters(['total_subscribers'=> -1]);

        //decrement total number of plan subscribers
         $sp = StripePlans::findOne(['provider_userid'=>$form->userid,  'stripe_plan_id'=>$delete_sub->plan_id]);
         $sp->updateCounters(['num_of_plan_subscribers'=> -1]);
         

        return  $this->render('cancelSubscription', ['unsubscribe'=>TRUE, 
                                            'username'=>$user->username, 'userid'=>$user->id,  ]);
    }





    public function actionMySubscribers(){

        //get list of people who tipped me
        $myTippers = Tips::findAll(['provider_id'=>Yii::$app->user->identity->id ]);

        //get list of people who subscribed to me
        $mySubs = Subscriber::findAll(['subscribe_to'=>Yii::$app->user->identity->id ]);

        return $this->render('mySubscribers', ['myTippers'=>$myTippers, 'mySubs'=>$mySubs]);

    } 

    public function actionRefund(){

        //get the chargeid

        //get the stripe account id

        //get the amount to refund

        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $ch = \Stripe\Charge::retrieve($chargeid, array('stripe_account' => $stripe_account));
        $ch->refunds->create(array('amount' => $amount));

        return $this->render();
    }
    
}

?>