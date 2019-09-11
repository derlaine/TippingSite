<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_stats".
 *
 * @property string $userid
 * @property integer $total_followers
 * @property integer $total_subscribers
 * @property integer $total_earned
 */
class Userstats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_stats';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid'], 'required'],
            [['userid', 'total_followers', 'total_subscribers', 'total_earned'], 'integer'],
            [['userid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userid' => 'Userid',
            'total_followers' => 'Total Followers',
            'total_subscribers' => 'Total Subscribers',
            'total_earned' => 'Total Earned',
        ];
    }
}
