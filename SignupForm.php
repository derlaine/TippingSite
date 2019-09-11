<?php
namespace frontend\models;

use common\models\User;
use app\models\userStats;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $website;
    public $twitter;
    public $instagram;
    public $tumblr;
    public $profilepic;
    public $firstname;
    public $lastname;
    public $dob;

    /**
     * @inheritdoc
     */
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

            ['website', 'default', 'value' => ''],
            ['twitter', 'default', 'value' => ''],
            ['instagram', 'default', 'value' => ''],
            ['tumblr', 'default', 'value' => ''],
            ['profilepic', 'default', 'value' => ''],
            ['firstname', 'default', 'value' => ''],
            ['lastname', 'default', 'value' => ''],
            ['dob', 'default', 'value' => 0],

        ];
    }

    /**
     * Signs user up.
     *****TO DO: add email verification T_____T
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if (!$user->save()) {
                 return null;               
            }
            
             //create user statistics for them. start everything at 0
            $userstat_db = new UserStats();
            $userstat_db->userid = $user->id;
           

            if(!$userstat_db->save()){
                throw new HttpException('Could not save to user stat db');
            }

            //part of the old code
            return $user;
        }


       

       
    }
}
