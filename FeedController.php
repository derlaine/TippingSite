<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/29/15
 * Time: 12:58 PM
 */

namespace frontend\controllers;

use frontend\models\posts;
use frontend\models\following;
use frontend\models\tipForm;
use common\models\User;
use app\models\Subscriber;
use app\models\Tips;
use app\models\UserStats;
use frontend\models\StripeAccounts;


use yii\web\HttpException;

use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


class FeedController extends Controller
{
    public function actionHomeFeed()
    {


        //get list of users i follow. this needs to be scaled later. how do you pull posts from hundreds
        //of people you follow? pull it once and store it in Redis?
        $following = Following::find()->where(['userid'=> Yii::$app->user->identity->id ])->select('follows')->all();

        $following = ArrayHelper::getColumn($following, 'follows');

        //also get posts from myself
        array_push($following, Yii::$app->user->identity->id );

        //get posts, limit 20, orderby dateposted, by followed userids
        //need to scale this later so it is infinite scrolling, ajax loading for next twenty?

        //get the list of people we subscribe to. this should probably be stored in some site wide cache
         $subscribed_to_raw = Subscriber::findALL(['userid'=> Yii::$app->user->identity->id]);

         $plans_i_subscribe_to = [];
         $subscribed_to_userids = [];
         foreach($subscribed_to_raw as $s) {
             $plans_i_subscribe_to[]= $s->plan_id;
             $subscribed_to_userids[] = $s->subscribe_to;
         }



         //SELECT * FROM posts WHERE userid IN following
        //                       OR (userid IN subscribed_to_userids 
         //                          AND plan_id IN plans_i_subscribe_to)

         // // ['in', 'id', [1, 2, 3]]
         //    ['and', 'type=1', ['or', 'id=1', 'id=2']]

         $query= posts::find()->where(  ['or',   ['in','userid' , $following] ,
                                                  ['and', ['in','userid', $subscribed_to_userids], 
                                                          ['in','stripe_plan_id', $plans_i_subscribe_to ]]] 
                                         )->orderBy('timestamp DESC');


        //$query= posts::find()->where( ['userid' => $following ] )
        //    ->orderBy('timestamp DESC');


        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $posts = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        //get username and profile pic of all the people who posted
        $users = User::find( ['id'=> $following ]  )->select('id, username, profilepic')->all();

        $users = ArrayHelper::index($users, 'id');


        return $this->render('homefeed', ['posts'=>$posts, 'pages'=> $pages , 'users'=>$users ]);


    }



    public function actionTip(){


        $form = new tipForm(); 
        
        //manually validate and load variables because we could not assign a model in the view 
         $form->attributes = $_POST;
         $form->amount = $form->amount*100; //Stripe deals in cents not dollars

         //if ! validate , throw error
         if(!$form->validate()){
            throw new HttpException('Validation Error');
         }

        if( $form->attributes &&  $form->validate() )
        {

            //get the stripe account id of the provider
             $s = StripeAccounts::findOne(['userid'=>$form->provider_id]);

            if(!$s){
               throw new HttpException('No stripe account for that user, you should not be here');
            }


            \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);

        
            \Stripe\Charge::create(array(
                  "amount" => $form->amount,
                  "currency" => "usd",
                  "source" => $form->stripeToken, // obtained with Stripe.js
                  "description" => "Tip for ".$form->provider_id . ' from '.Yii::$app->user->identity->id,
                ), ["stripe_account"=> $s->stripe_id]);


            //update counter of total tips collected
            $u = UserStats::findOne(['userid'=> $form->provider_id]);
            $u->updateCounters(['tips_collected'=>1]);

            //update log of who tipped what amount
            $tits = new Tips();
            $tits->userid = Yii::$app->user->identity->id;
            $tits->provider_id = $form->provider_id;
            $tits->tip_amount = $form->amount;
            $tits->timestamp = time();

            if(!$tits->save()){
                throw new HttpException('Could not save log of tips, try again later or contact support ');
            }


        }

        return $this->redirect(Url::to(['feed/home-feed']));


    }

}