<?php 

namespace frontend\controllers;
use common\models\User;

use yii\web\Controller;
use Yii;

class BrowseController extends Controller
{
    public function actionIndex()
    {

    	//find the largest userid
    	$max = User::find()->select('id')->orderBy('id DESC')->one();

    	//generate random userids
    	$userids = range(1, $max->id);
    	shuffle($userids);
    	$userids = array_slice($userids,0,15);

    	//get random users
    	$users = User::findAll(['id'=>$userids]);

    	return $this->render('index', ['users'=>$users]);
    }

}

?>