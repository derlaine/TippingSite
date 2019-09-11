<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/27/15
 * Time: 3:26 PM
 */

namespace frontend\models;

use common\models\User;
use yii\base\Model;
use yii\helpers\HtmlPurifier;
use Yii;

class editProfileForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $facebook;
    public $twitter;
    public $website;
    public $instagram;
    public $tumblr;
    public $profilepic;
    public $profilebanner;
    public $profileblurb;
    public $display_options;


    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

             [['profileblurb','website'],'filter','filter'=>'\yii\helpers\HtmlPurifier::process'],

            ['website', 'string', 'max' => 256],
            ['twitter', 'string', 'max' => 256],
            ['facebook', 'string', 'max' => 256],
            ['instagram', 'string', 'max' => 256],
            ['tumblr', 'string', 'max' => 256],
            ['profileblurb', 'string', 'max' => 1000],

            ['website', 'default', 'value' => ''],
            ['twitter', 'default', 'value' => ''],
            ['facebook', 'default', 'value' => ''],
            ['instagram', 'default', 'value' => ''],
            ['tumblr', 'default', 'value' => ''],
            ['profileblurb', 'default', 'value' => ''],

            ['profilepic', 'default', 'value' => ''],
            ['profilebanner', 'default', 'value' => ''],
            ['display_options', 'default', 'value' => 0],
            ['display_options', 'integer'],

            [['profilepic'], 'file' , 'skipOnEmpty' => true, 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png','maxSize'=> 100*1024],
            [['profilebanner'], 'file' , 'skipOnEmpty' => true, 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png','maxSize'=> 300*1024],
        ];
    }

    public function saveProfile()
    {

        //query by logged in id
        $user = User::findOne( Yii::$app->user->identity->id);
        $user->website= HtmlPurifier::process($this->website);
        $user->facebook= HtmlPurifier::process($this->facebook);
        $user->twitter= HtmlPurifier::process($this->twitter);
        $user->instagram= HtmlPurifier::process($this->instagram);
        $user->website= HtmlPurifier::process($this->website);
        $user->tumblr= HtmlPurifier::process($this->tumblr);
        $user->profileblurb=  HtmlPurifier::process($this->profileblurb);
        $user->display_options=  $this->display_options;

        if($this->profilepic) {
            $user->profilepic= $this->profilepic->name;
        }
        if($this->profilebanner) {
            $user->profilebanner= $this->profilebanner->name;
        }

        if(!$user->save()){
            return FALSE;
        }

        return TRUE;

    }

}