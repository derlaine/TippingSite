<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "posts".
 *
 * @property string $id
 * @property string $title
 * @property string $text
 * @property string $imgpath
 * @property integer $timestamp
 * @property string $userid
 * @property string $permission
 * @property string $plan_id
 */
class Posts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            //text or image must be present
            
          

            [[ 'timestamp', 'userid'], 'required'],
            [['text'], 'string'],
            [['timestamp', 'userid','accept_tips'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['stripe_plan_id'], 'string', 'max' => 255],

            ['imgpath', 'default', 'value' => ''],
            ['text', 'default', 'value' => ''],
            ['title', 'default', 'value' => ''],
            ['accept_tips', 'default', 'value' => 0],
            ['stripe_plan_id', 'default', 'value' => ''],
            [['imgpath'], 'file']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'text' => 'Text',
            'imgpath' => 'Imgpath',
            'timestamp' => 'Timestamp',
            'userid' => 'Userid',
            'plan_id' => 'Who can see your post?'
        ];
    }


    //i dont think this is used...legacy?
    public function createPost()
    {

        $post = new Posts();
        $post->text = $this->text;
        $post->title = $this->title;
        $post->timestamp =time();
        $post->userid = Yii::$app->user->identity->id;
        $post->plan_id = $this->plan_id;

        if($this->imgpath) {
            $post->imgpath = $this->imgpath->name;
        }
        else{
            $this->imgpath='';
        }


        if(!$post->save()){
            return FALSE;
        }

        return TRUE;

    }

}
