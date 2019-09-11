<?php

use yii\helpers\Html;
use yii\helpers\Url;

use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 


?>

<div class="roundbox clearfix pad10">
	<h2>Manage Your Payment Methods</h2>

	<hr class="faintHR">

	<?php if(!$sources):?>
		You do not have a payment method on file. Start subscribing to people?

	<?php else: ?>
	
	<div class="clearfix">


		<!--not launch critical, add more payment methods later-->
		<!--
		<a class="btn btn-primary pull-right" href="">Add New</a>
		-->
		
		<h4>Credit Cards</h4>


		<ul class="list-group">
			 <?php foreach($sources as $c): ?>

			 	<li class="list-group-item clearfix">

			 			<span class="glyphicon glyphicon-credit-card"></span>

			 			<?= $c->brand ?>

			            <span class="offset-left-100px">
			            Card ending in: <?= $c->last4 ? $c->last4 :  ''; ?>
			            </span>

			            <span class="offset-left-100px">
			            Expires: 
			            <?php if($c->exp_month &&  $c->exp_year): ?>
			            	 <?= $c->exp_month ?>/<?= $c->exp_year ?>
			            <?php endif; ?>
			            </span>

			            <a class="btn btn-danger pull-right" href="">Delete</a>
				</li>
		           
		 	<?php endforeach; ?>
		 </ul>
	</div><!--end of credit card box-->
	<?php endif; ?>

	  <?= Html::a('Cancel', ['/profile/edit-profile', 'userid'=>Yii::$app->user->identity->id], 
	          ['class'=>'btn btn-default pull-right']) ?>

</div><!--end of roundbox
