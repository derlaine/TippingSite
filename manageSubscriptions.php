<?php

use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="roundbox profile-right pad10">
<h2>Manage Subscriptions</h2>

<?php if(!$mySubs): ?>
	<div class="alert alert-info">
	 You do not have any subscriptions. Browse artists?
	</div>

<?php else: ?>

	<ul class="list-group">
	<?php foreach($mySubs as $plan_id => $s):?>

			<li class="list-group-item clearfix">
			<!--profile pic here -->

			<strong><a href="link to their profile"><?= $providerNames[ $s['subscribe_to']] ?></a></strong>
			<?=  '$'.number_format(  $s['amount']/100, 2)  ?> every  <?= $s['interval_count']?> <?= $s['interval_type']?>

			<?php
			$form = ActiveForm::begin([ 'id' =>'delete-form',
                            'options'=>  ['enctype' => 'multipart/form-data'],        
                            ]);
			?>

			<input type="hidden" name="providerid" value="<?= $s['subscribe_to']?>" />
			<input type="hidden" name="todo" value="delete" />

			<?= Html::submitButton('Delete', ['class' => 'btn btn-danger pull-right']) ?>

			

			<?php ActiveForm::end() ?>


			</li>


	<?php endforeach; ?>

	</ul>

<?php endif; ?>


<?= Html::a('Cancel', ['/profile/edit-profile', 'userid'=>Yii::$app->user->identity->id], 
					['class'=>'btn btn-default pull-right']) ?>


</div> <!--end of roundbox -->