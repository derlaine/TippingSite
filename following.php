<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "following".
 *
 * @property string $id
 * @property string $userid
 * @property string $follows
 */
class following extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'following';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'follows'], 'required'],
            [['userid', 'follows'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userid' => 'Userid',
            'follows' => 'Follows',
        ];
    }
}
