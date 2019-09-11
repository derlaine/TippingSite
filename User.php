<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property string $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $website
 * @property string $facebook
 * @property string $instagram
 * @property string $tumblr
 * @property string $twitter
 * @property string $profilepic
 * @property string $profileblurb
 * @property string $profilebanner
 * @property string $first_name
 * @property string $last_name
 * @property integer $dob
 * @property integer $display_options
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at', 'display_options'], 'required'],
            [['status', 'created_at', 'updated_at', 'dob', 'display_options'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'website', 'facebook', 'instagram', 'tumblr', 'twitter', 'profilepic', 'first_name', 'last_name'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['profileblurb'], 'string', 'max' => 1000],
            [['profilebanner'], 'string', 'max' => 256],
            ['website', 'default', 'value' => ''],
            ['twitter', 'default', 'value' => ''],
            ['facebook', 'default', 'value' => ''],
            ['instagram', 'default', 'value' => ''],
            ['tumblr', 'default', 'value' => ''],
            ['profileblurb', 'default', 'value' => ''],

            ['profilepic', 'default', 'value' => ''],
            ['profilebanner', 'default', 'value' => ''],
            ['display_options', 'default', 'value' => 0],


        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'website' => 'Website',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tumblr' => 'Tumblr',
            'twitter' => 'Twitter',
            'profilepic' => 'Profilepic',
            'profileblurb' => 'Profileblurb',
            'profilebanner' => 'Profilebanner',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'dob' => 'Dob',
            'display_options' => 'Display Options',
        ];
    }
}
