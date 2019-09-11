<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\View;

$intervals = array('day'=>'Day', 'week'=>'Week', 'month' => 'Month', 'year'=>'Year');
$interval_count = array('1'=>'1', '2' => '2', '3'=>'3');


//ghetto hack Yii to display inline form elements
$this->registerCss(".help-block { display: inline; }"); 

//edit name can be added later, not a critical feature


use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 


?>



<div class="roundbox pad10 center-content">


<?php if($success): ?>
	<div class="alert alert-success">
	Successfully added new plan; please wait a few minutes to see it.
	</div>
	<hr class="faintHR" />
<?php endif; ?>

<h3>My Reward Tiers</h3>

<hr class="faintHR" />


<ul class="list-group ">
<?php foreach($stripePlans as $plan): ?>
		
		<li class="list-group-item clearfix">
		<strong><?= $plan['stripe_plan_id']; ?> </strong>
		<?= '$'.number_format( $plan['amount']/100, 2) ?> every <?= $plan['interval_type'] ?>
		<p class="greytext" style="width:400px; margin:0;"><?= $plan['description'] ?>

		<br/>
		<small><?= $plan['require_address'] ? '&#176; Requires Shipping Address' : '' ?></small>

		</p>
		
		 <a class="btn btn-danger pull-right" href="<?= Url::to(['profile/manage-stripe-plans', 'action'=>'delete' ,'planid'=> $plan['id']])?>" >Delete</a> 
		</li>

		

<?php endforeach; ?>
</ul>

<span class="greytext">You are allowed a maximum of 20 plans</span>



</div> <!--end of roundbox -->

<?php if(!$maxPlans): ?>

<div class="roundbox pad10 center-content marginTop10">


<div class="addPlan clearfix">

<h3>Add Reward Tier </h3>
<hr class="faintHR" />


<?php
		$form = ActiveForm::begin([ 'id' =>'plans-form', 'action'=> ['profile/manage-stripe-plans'],
                            'options'=>  ['enctype' => 'multipart/form-data']
                            ]);


	?>

	<div class="inputRow">
	<h5>
		<label for="">Name</label>
	</h5>

	

	<?= Html::activeTextInput($formModel,'name',['class'=>'form-control input-planName', 
												'placeholder'=>'eg. "Gold Plan", "Silver Plan"']); ?>


	
	</div> <!--end of inputRow-->

	<hr class="faintHR" />


	<div class="inputRow">
	<h5>
		<label for="">How Much</label>
	</h5>

	Every 

	<?= $form->field($formModel, 'interval_count', ['options'=> ['style'=>'display:inline;']])->dropDownList( $interval_count, array('prompt'=>'Select One', 'style'=>'width:120px; display:inline;', ))->label('') ?> 

	<?= $form->field($formModel, 'interval', ['options'=> ['style'=>'display:inline;']])->dropDownList( $intervals, array('prompt'=>'Select One', 'style'=>'width:120px; display:inline;', 'class'=>'form-control interval-select'))->label('') ?> 

	charge $

	<?= $form->field($formModel, 'amount',['options'=> ['style'=>'display:inline;']])->textInput(['style'=>'width:120px;
	display:inline;'])->label('') ?>
	</div> <!--end of inputRow-->

<hr class="faintHR" />


	<div class="inputRow">
	<h5>
		<label for="">Description </label>
	</h5>

	<?= Html::activeTextArea($formModel,'description', ['class'=>'form-control input-planDescription', 
											  'placeholder'=> 'A description of what your supporter would get for this reward tier' ])?>


	

	<h5><label></label></h5>

	<?= $form->field($formModel, 'require_address')->checkbox()->hint('Do you need subscribers to give you their shipping address?'); ?>
	</div> <!--end of inputrow-->
	
<hr class="faintHR" />


	<div class="inputRow">
	<h5>
		<label for="">Thank You Message</label>
	</h5>

	<?= Html::activeTextArea( $formModel ,'thankyou_text', ['class'=>'form-control input-planDescription', 
											  'placeholder'=> 'Thank your supporters for their help!' ])?>


	</div> <!--end of inputRow-->

<hr class="faintHR" />

		<p>
		<br />
		

		<?= Html::submitButton('Add Reward Tier' , ['class' => 'btn btn-primary pull-right marginLeft10']) ?>

		<?= Html::a('Cancel', ['/profile/edit-profile', 'userid'=>Yii::$app->user->identity->id], 
					['class'=>'btn btn-default pull-right']) ?>
		</p>

		<?php ActiveForm::end() ?>
<?php endif; ?>


</div><!--end of addPlan-->


</div> <!--end of round box-->
