<?php

namespace frontend\models;
use yii\base\Model;
use Yii;

class manageSubscriptionsForm extends Model{

	public $providerid;
	public $todo;

	/*
	public function rules()
	{
		return [ [['providerid'], 'integer'],
				 [['todo'], 'string'   ],
				  ['todo', 'default', 'value' => ''],
				  ['providerid', 'default', 'value' => ''],
				];
	}
	*/


	 public function rules()
    {
        return [
            ['todo', 'string', 'message'=>'this is a mistake'],
            ['providerid', 'integer', 'message'=>'why dont you work'],
            ['todo', 'required']
        ];
    }



}

?>