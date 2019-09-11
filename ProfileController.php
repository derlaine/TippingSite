<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/28/15
 * Time: 3:26 PM
 */

namespace frontend\controllers;

use common\models\User;
use app\models\UserStats;
use frontend\models\StripeAccounts;
use frontend\models\StripeCustomers;
use app\models\StripePlans;
use Yii;
use yii\web\Controller;
use frontend\models\editProfileForm;
use frontend\models\manageStripePlansForm;
use frontend\models\addStripeAccountForm;
use frontend\models\manageSubscriptionsForm;
use app\models\Subscriber;
use frontend\models\Following;
use frontend\models\posts;
use yii\data\Pagination;



use yii\web\HttpException;
use yii\web\UploadedFile;

use yii\helpers\Url;
use yii\helpers\BaseFileHelper;

use yii\imagine\Image;



class ProfileController extends Controller
{

    public function actionUserProfile( $userid )
    {

        if ( !$userid) {
            throw new HttpException('404, no user id, i cannot look up nothing');
        }

        $user = User::findOne($userid);

        if(!$user){
            throw new HttpException('404, no such user, are you thinking of someone else?');
        }

        $userStats = UserStats::findOne($userid);


         //am I following this user?
        $isFollowing = Following::findOne(['userid'=> Yii::$app->user->identity->id, 'follows'=>$userid]);




        //see if the logged in user is already subscribed to the profile
        //i should keep a table of users who subscribe to a provider
        //in the future , store this in cache so i don't keep querying db every time i look at profile
        $subscribed_to_raw = Subscriber::findOne(['userid'=> Yii::$app->user->identity->id, 
                                                  'subscribe_to' => $userid ]);

        $subscribed_to = $subscribed_to_raw? TRUE : FALSE;

        //if i'm subscribed to this user, which plan am I subscribed to?
        $sub_to_plan = $subscribed_to_raw ? [$subscribed_to_raw->plan_id, '' ] : '';

        //if the user did not subscribe to anything, select posts userid=userid AND no sub required
        //if user subscribed, select posts userid=userid OR plan=sapphire

        //select * FROM posts WHERE userid=userid 
        //select * FROM posts WHERE userid=userid AND plan_id = ''
        //select * FROM posts WHERE userid=userid AND plan_id = sapphire
        //select * FROM posts WHERE userid=userid AND  (plan_id = sapphire OR plan_id = '')
        //select * FROM posts WHERE userid=userid AND  plan_id IN (sapphire,'')

      
        
        //see if the user is a provider/has entered bank info and can receive subscriptions
        //do not display subscribe button if the user has not entered bank info
        $has_account = null;
        if( ! $subscribed_to) {
            $stripeAcc = StripeAccounts::findOne(['userid'=>$userid]);
            $has_account = $stripeAcc? TRUE : FALSE;
        }

        //get the income to display
        //$income_string = '$1,234 a year';
        $income_string = $this->calculateIncome($userid, $user['display_options']);
        $income_string = $income_string['display_string'];

     
        //no subscription, show public posts only
        //select * FROM posts WHERE userid=userid AND plan_id = ''

        if( !$subscribed_to){
                $query= posts::find()->where( ['and', 'userid='.$userid , 'stripe_plan_id=\'\''] )
            ->orderBy('timestamp DESC');

        }
        //has subscription, show public posts and subscription posts 
        //select * FROM posts WHERE userid=userid AND  plan_id IN (sapphire,'')
        // ['in', 'id', [1, 2, 3]]
        else{
              $query= posts::find()->where( [ 'and' , 'userid='.$userid , ['in','stripe_plan_id',$sub_to_plan] ])
            ->orderBy('timestamp DESC');
        }
      
        
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $posts = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        //if the user is not subscribed to the provider, do not display the post

        return $this->render('UserProfile' , ['user'=> $user, 'userStats'=> $userStats, 'subscribed_to'=>$subscribed_to,
                                               'has_account' => $has_account, 'posts'=>$posts, 
                                               'pages'=>$pages, 'income_string'=>$income_string, 
                                               'isFollowing'=>$isFollowing   ]);
    }

    public function actionEditProfile()
    {
        $userid = Yii::$app->user->identity->id;

        $form = new EditProfileForm();

        //this has to be here first because the load and post() yii functions have profilepic as empty string
       $tempfile = UploadedFile::getInstance($form, 'profilepic');
       $tempBanner = UploadedFile::getInstance($form, 'profilebanner');


        if ($form->load(Yii::$app->request->post()))
        {
            //candidate for rewriting =P
            if($tempfile || $tempBanner){
                if($tempfile) { $form->profilepic = $tempfile; } 
                if($tempBanner) { $form->profilebanner = $tempBanner; }

                $dir = Yii::$app->basePath .'/web/uploads/'. Yii::$app->user->identity->username.'/';
                $displayWidth = Yii::$app->params['PROFILE_THUMB_SIZE'];


                //make a directory if it doesn't already exist
                if(! file_exists($dir)){
                    BaseFileHelper::createDirectory($dir, 755);
                }


                if($form->profilepic) {
                    //basepath gives an absolute path without ~username, url::to gives ~localhost which is unusable here
                    //$form->profilepic->saveAs(Yii::$app->basePath .'/web/uploads/' . $form->profilepic->name);
                    $form->profilepic->saveAs($dir . $form->profilepic->name);

                     //save out the 128x128 version
                    Image::thumbnail($dir . $form->profilepic->name, $displayWidth, $displayWidth)
                        ->save(Yii::getAlias($dir . $displayWidth. '_'.$form->profilepic->name), ['quality' => 100]);
                }

                if($form->profilebanner){
                    $form->profilebanner->saveAs($dir . $form->profilebanner->name);
                }

            }


            //save the rest of the profile
            if($form->saveProfile()){

                Yii::$app->session->setFlash('success', 'Profile Saved');
                return $this->redirect( ['/profile/user-profile', 'userid'=> Yii::$app->user->identity->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Could not save your profile, please try again later');
            }

            return $this->refresh();
        }

        //no POST data, display form
        $user = User::findOne($userid);
        $form->website = $user->website; //prepopulate the form with existing values
        $form->twitter = $user->twitter; 
        $form->facebook = $user->facebook; 
        $form->instagram = $user->instagram; 
        $form->tumblr = $user->tumblr; 
        $form->profilepic = $user->profilepic; 
        $form->profilebanner= $user->profilebanner; 
        $form->profileblurb= $user->profileblurb; 
        $form->display_options = $user->display_options;

         //does the user have a stripe account/submitted bank info?
        $has_account = $this->hasStripeAccount() ? TRUE : FALSE;

        //calculate income
        $income = $this->calculateIncome($userid,0, TRUE);

        return $this->render('editProfile', ['user'=> $user, 'formModel' => $form, 'has_account'=>$has_account,
                            'income'=>$income  ]);

    }

    //see if the provided userid has a Stripe Account. default is logged in user.
    private function hasStripeAccount($userid=null){

        if(!$userid){
           $userid = Yii::$app->user->identity->id;
        }

        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $stripeacc = StripeAccounts::findOne( ['userid'=>$userid]);

        if(!$stripeacc){
            return FALSE;
        }

        return TRUE;

    }

    public function actionAddStripeAccount($action=null){

        $userid = Yii::$app->user->identity->id;
        $form = new AddStripeAccountForm(); //have to include the model in the top of this code
        $stripe_account=null;
        $sacc = null;

        $user = User::findOne($userid);

        //if user already has a stripe account, do not show "add account form" 
        $sacc = StripeAccounts::findOne(['userid'=>Yii::$app->user->identity->id]);
        if($sacc){

            \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
            $stripe_account = \Stripe\Account::retrieve($sacc->stripe_id);
        }

       

        //user does not have a stripe account, they are adding one
        if ($form->load(Yii::$app->request->post()))
        {

             if(!$form->addStripeAccount()){
                throw new HttpException('Could not save Stripe Account, please contact help ');
             }

                Yii::$app->getSession()->setFlash('success', 'Successfully updated. Please reload in a few minutes to see your changes.');

        }
        
      

        
   

        return $this->render('addStripeAccount', ['user'=> $user, 'formModel' => $form, 
                                                  'stripe_account'=>$stripe_account]);

        
    }

    public function actionManageStripePlans($action=null, $planid=null)
    {
        $form = new manageStripePlansForm();
        $success=NULL;

        //find our record of the stripe id (means user has  entered bank account info)
        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $stripeacc = StripeAccounts::findOne( ['userid'=>Yii::$app->user->identity->id]);

        if(!$stripeacc){
            throw new HttpException('You need a Stripe Account first');
        }


        //delete the stripe plan....ghetto style. should be in ajax
        if($action=='delete' && $planid){
             $plan = \Stripe\Plan::retrieve($planid, ['stripe_account'=> $stripeacc->stripe_id]);
             $plan->delete();

             //delete on our server
             $sp = StripePlans::find()->where(['userid'=> Yii::$app->user->identity->id, 'plan_id'=>$planid ])->one();
             $sp->delete();
             //if deletion fails, so be it; we can't repeat it anyway because of Stripe call above
             //we'll just handle the discrepency later by deleting our end if Stripe don't have the record
             //if Stripe has the record and we don't , we also delete Stripe's copy and start from scratch
             
        }

        //load any existing stripe plans the user has
        $allPlans = \Stripe\Plan::all([],['stripe_account'=> $stripeacc->stripe_id]);

        //TODO: limit number to plans per provider 
        $maxPlans = FALSE;
        if( count($allPlans['data']) >= 20){
            $maxPlans = TRUE;
        }

        //get metadata like description, thnk you message, require adddress, etc
        $stripePlans = StripePlans::find()->where(['provider_userid'=> Yii::$app->user->identity->id ])->all();


        //add a Stripe plan
        if ($form->load(Yii::$app->request->post()) && !$maxPlans)
        {
            $form->savePlan($stripeacc);
            $success=TRUE;

            //flash a message it'll take a few minutes for the new plan to show up
        }


        return $this->render('manageStripePlans', ['formModel' => $form, 'allPlans'=> $allPlans['data'], 
                            'maxPlans'=> $maxPlans, 'success'=>$success, 'stripePlans'=>$stripePlans] );
    }

   
    //list and deleting any subscriptions we have
    public function actionManageSubscriptions(){

        $form = new manageSubscriptionsForm();
        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);


        //should be load() but for some reason it's not working, so manually loading attributes
        //and manual validation. Someday figure this out why $form->load(post) is not working
        //i think i have all the rules i place in the model form, but it just isn't loading
        $form->attributes = $_POST;


        if( $form->attributes &&  $form->todo == 'delete' && $form->validate()  )
        {
            //get info
            $delete_sub = Subscriber::findOne(['userid'=>Yii::$app->user->identity->id, 
                                  'subscribe_to'=>$form->providerid]);  

            if(!$delete_sub){
                throw new HttpException('No such subscription to delete, find help');
            }

            //retrieve the connected customer
            $customerConnected=\Stripe\Customer::retrieve($delete_sub->connected_customer_id
                                , array("stripe_account" => $delete_sub->connected_stripe_id));

            //delete on Stripe
            $customerConnected->subscriptions->retrieve($delete_sub->subscription_id)->cancel();

            //delete our database row       
            $delete_sub->delete();

            //decrement user stat
            $us = UserStat::findOne(['userid'=>$form->providerid]);
            $us->updateCounters(['total_subscribers'=> -1]);

            //decrement total number of plan subscribers
             $sp = StripePlans::findOne(['provider_userid'=>$form->providerid,  'stripe_plan_id'=>$delete_sub->plan_id]);
             $sp->updateCounters(['num_of_plan_subscribers'=> -1]);
        }


        //get userids of all the people we subscribe to 
          $subscribed_to_raw = Subscriber::findAll(['userid'=> Yii::$app->user->identity->id]);
        var_dump('I am Subscribed to: ');

        $subscribed_to_userids = array();
        foreach($subscribed_to_raw as $s)
        {
             $subscribed_to_userids[] = $s->subscribe_to;
        }

        if(!$subscribed_to_raw){
            return $this->render('manageSubscriptions', [ 'providerNames' => null, 'mySubs'=>null]);
        }

        var_dump($subscribed_to_userids);


        //now get usernames of all the userids we subscribed to
        $subscribed_to_users =  User::find()->where(['id'=>$subscribed_to_userids])->select(['id','username'])->all();

        $providerNames = array();
        foreach($subscribed_to_users as $p){
            $providerNames[$p->id] =$p->username;
        }


        //get my customer id, so we can get detailed subscription info from Stripe
        $c = StripeCustomers::findOne(['userid'=> Yii::$app->user->identity->id]);
        if(!$c){
            throw new HttpException("Could not find Stripe Customer Id");
        }
        $customerid = $c->stripe_customer_id;

        //get subscription info (amount, interval) of all the subscriptions we have
        $subscriptions = Subscriber::findAll(['userid'=>  Yii::$app->user->identity->id ]);


        $mySubs=array();
        foreach($subscriptions as $s){
            $mySubs[$s->plan_id]['amount'] = $s->amount;
            $mySubs[$s->plan_id]['interval_type'] = $s->interval_type;
            $mySubs[$s->plan_id]['interval_count'] = $s->interval_count;
            $mySubs[$s->plan_id]['subscribe_to'] = $s->subscribe_to;

 

        }

        return $this->render('manageSubscriptions', [ 'providerNames' => $providerNames, 'mySubs'=>$mySubs]);

    }

    public function actionUploadProfile()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->file && $model->validate()) {
                $model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
            }
        }

        return $this->render('upload', ['model' => $model]);
    }

    public function actionManageCreditCard(){

        $customer = StripeCustomers::findOne(['userid'=>  Yii::$app->user->identity->id]);

        if(!$customer){
           return $this->render('manageCreditCard', ['sources'=>null] );
        }

        //retrieve from Stripe
        \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $cus = \Stripe\Customer::retrieve($customer->stripe_customer_id);


         return $this->render('manageCreditCard', ['sources'=>$cus->sources->data] );
    }

    /*
        ——display options ———
    ▪   Digits: 6 digits a year
    ▪   Yearly Range: 50,000 to 80,000 a year
    ▪   Monthly Straight: $1,234 a month
    ▪   Private/Hidden 
    ACCEPTS: Provider UserId we want to calculate income for,
    $display_option : what kind of display string to generate
    $calculateall : for edit profile we want to show the user what each of the display options look like
    so we have to calculate all the possible strings
    RETURNS: An array of displayed income and the real amount
    */
    private function calculateIncome($userid, $display_option=0, $calculateAll = FALSE){

        $monthly_total = 0;
        $yearly_total = 0;
        $display_amount = 0;
        $display_string = '';
        $yearly_range = '';


         $stripeAcc = StripeAccounts::findOne(['userid'=>$userid]);
         if(!$stripeAcc){
            return ['display_string'=>$display_string, 'real_monthly_total'=>$monthly_total, 'yearly_total'=>$yearly_total ,
                'yearly_range'=>$yearly_range ];

         }

        //get all the plans
        $allPlans = StripePlans::findAll(['provider_userid'=>$userid]);


        //calculate how much you make a month
        foreach($allPlans as $plan ){

            if ($plan['interval_type'] == 'day') {
                if($plan['interval_count'] == 1)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 30; 
                if($plan['interval_count'] == 2)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 15;
                if($plan['interval_count'] == 3)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 10;
                } //end of if interval==day

            if($plan['interval_type'] == 'week'){
                if( $plan['interval_count'] == 1)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 4;
                if($plan['interval_count'] == 2)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 2;
                if($plan['interval_count'] == 3)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 1.25;
            } //end of if interval == week

            if($plan['interval_type'] == 'month'){
                if($plan['interval_count'] == 1)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) ;
                if($plan['interval_count'] == 2)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 0.5;
                if($plan['interval_count'] == 3)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * 0.33;
            } //end of if interval == month

            if($plan['interval_type'] == 'year')
                if($plan['interval_count'] == 1)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * (1/12);
                if($plan['interval_count'] == 2)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * (1/24);
                if($plan['interval_count'] == 3)
                    $monthly_total += $plan['num_of_plan_subscribers'] * ($plan['amount']/100) * (1/36);
        } //end foreach

        //default = monthly 
        if ( !$display_option ) {
            $display_string =  '$'.number_format( $monthly_total, 0)  . ' a month';
        }

        //2 = "6 digits a year"
        if($display_option == 2 || $calculateAll) {
            $yearly_total =$monthly_total * 12;
            $digits = strlen((string)$yearly_total);
            $display_string = $digits . ' digits a year';
        }

        //3 = "40,000 to 80,000 a year"
        if($display_option == 3 || $calculateAll){

            $yearly_total =$monthly_total * 12;

             
                 if( $yearly_total >= 0 && $yearly_total <= 30000){
                    $display_string = '$0 to $30,000 a year';
                 }

                 elseif( $yearly_total >30000 && $yearly_total <= 50000){
                    $display_string = '$30,000 to $50,000 a year';
                 }

                elseif( $yearly_total >50000 && $yearly_total <= 80000){
                    $display_string = '$50,000 to $80,000 a year';
                }

                 elseif( $yearly_total >80000 && $yearly_total <= 100000){
                    $display_string = '$80,000 to $100,000 a year';
                 }

                 elseif( $yearly_total >100000 ){
                    $display_string = '$100,000+ a year';
                 }


            $yearly_range = $display_string;
        } //end of if = 3 salary range display option


        //4 = private, do not display what you earn
        if($display_option == 4 ){
                 $display_string = '';
        }

        //real_monthly_total, yearly_total and yearly_range are for edit Profile when the user wants to see
        //all their information
        return ['display_string'=>$display_string, 'real_monthly_total'=>$monthly_total, 'yearly_total'=>$yearly_total ,
                'yearly_range'=>$yearly_range];

    }
}