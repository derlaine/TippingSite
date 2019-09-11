<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/27/15
 * Time: 3:26 PM
 */

namespace frontend\models;

use yii\base\Model;
use Yii;

class createPostForm extends Model
{

    public $imgpath;
    public $title;
    public $text;
    public $timestamp;
    public $userid;
    public $stripe_plan_id;
    public $accept_tips;


    public function rules()
    {
        return [

              ['imgpath', 'required', 'message'=>'Must have either text or image' ,'when' => function($model) {
                                            return empty($model->text);}, 

                                             'whenClient' => "function (attribute, value) {
                                     return $('#createpostform-text').val() == '';}" , 

                                            ],

            ['text', 'required', 'message'=>'Must have either text or image', 'when' => function($model) {
                                            return empty($model->imgpath);} ,  

                                            'whenClient' => "function (attribute, value) {
                                     return $('#createpostform-imgpath').val() == '';}" , 

                                            ],
            


            [[ 'timestamp', 'userid'], 'required'],
            [['text'], 'string'],
            [['timestamp', 'userid','accept_tips'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['stripe_plan_id'], 'string', 'max' => 255],

            ['imgpath', 'default', 'value' => ''],
            ['title', 'default', 'value' => ''],
            ['text', 'default', 'value' => ''],
            ['accept_tips', 'default', 'value' => 0],
            ['stripe_plan_id', 'default', 'value' => ''],
            [['imgpath'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png', 'maxSize'=> 1024*1024]
        ];
    }

    public function createPost()
    {
        $post = new Posts();
        $post->text = $this->text;
        $post->title = $this->title;
        $post->timestamp =time();
        $post->userid = Yii::$app->user->identity->id;
        $post->stripe_plan_id = $this->stripe_plan_id;
        $post->accept_tips = $this->accept_tips;

        if($this->imgpath) {
            $post->imgpath = $this->imgpath->name;
        }
        else{
            $this->imgpath='';
        }


        if(!$post->save()){
            return FALSE;
        }


        //ghetto, return types are not same lol :p
        return $post->id;

    }

}