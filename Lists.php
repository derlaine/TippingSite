<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "lists".
 *
 * @property string $id
 * @property integer $userid
 * @property string $list
 * @property string $name
 */
class Lists extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'list', 'name'], 'required'],
            [['userid'], 'integer'],
            [['list'], 'string'],
            [['name'], 'string', 'max' => 255]
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
            'list' => 'List',
            'name' => 'Name',
        ];
    }
}
