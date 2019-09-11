<?php

namespace frontend\controllers;

use yii\web\Controller;
use Yii;

use app\models\User;
use app\models\userStats;
use yii\web\HttpException;

use frontend\models\Following;


use yii\helpers\Url;


class FollowController extends Controller{

	public function actionFollow($userid){

		//see if we are already following that user
		$isFollowing = Following::findOne(['userid'=>Yii::$app->user->identity->id, 'follows'=>$userid]);
		
		if($isFollowing){
			throw new HttpException('Already following that user, you should not be here');
		}

		//follow the user
		$f = new Following();
		$f->userid = Yii::$app->user->identity->id;
		$f->follows = $userid;
		if(!$f->save()) {
			throw new HttpException('Could not follow that user, please try again later');
		}

		//increment user_stats table
        $userstat = UserStats::findOne(['userid'=>$userid]);

        if(!$userstat){
        	//userstats are created at signup, so everyone should have them
            throw new HttpException('User has no stats, contact support');
        } 

        $userstat->updateCounters(['total_followers' => 1]);

        return $this->redirect(Url::to(['profile/user-profile', 'userid'=>$userid] ));

	}

	//basically duplicate of Follow...we could probably refactor it 
	public function actionUnfollow($userid){

		//see if we are already following that user
		$isFollowing = Following::findOne(['userid'=>Yii::$app->user->identity->id, 'follows'=>$userid]);
		
		if( !$isFollowing){
			throw new HttpException('Not following that user, you should not be here');
		}

		//unfollow the user
		$f =  Following::findOne(['userid'=>Yii::$app->user->identity->id, 'follows'=>$userid ]);
		if(!$f->delete()) {
			throw new HttpException('Could not follow that user, please try again later');
		}

		//decrement user_stats table
        $userstat = UserStats::findOne(['userid'=>$userid]);

        if(!$userstat){
            throw new HttpException('User has no stats, contact support');
        } 

        $userstat->updateCounters(['total_followers' => -1]);

        return $this->redirect(Url::to(['profile/user-profile', 'userid'=>$userid]));

	}


}

?>