<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/26/15
 * Time: 5:58 PM
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\View;


    $js_code='
    Stripe.setPublishableKey(\'pk_test_61SFBKmDJEMyvQGvzP6DKm9T\');

   var stripeResponseHandler = function(status, response) {
     var $form = $(\'#bank-form\');

     if (response.error) {
       // Show the errors on the form
       $form.find(\'.payment-errors\').text(response.error.message);
       $form.find(\'button\').prop(\'disabled\', false);
     } else {
       var token = response.id;

       alert(token);
       
       var $form1 = $(\'#baccount-form\');


       // Insert the token into the form so it gets submitted to the server
       $form1.append($(\'<input type="hidden" name="addStripeAccountForm[stripeToken]" />\').val(token));

       // and re-submit
       $form1.get(0).submit();
     }
   };

   jQuery(function($) {
     $(\'#bank-form\').submit(function(e) {
       var $form = $(this);

       // Disable the submit button to prevent repeated clicks
       $form.find(\'button\').prop(\'disabled\', true);

       Stripe.bankAccount.createToken($form, stripeResponseHandler);

       // Prevent the form from submitting with the default action
       return false;
     });
   });';

$legalentities = array('individual'=>'Individual', 'company'=>'Company');

$this->registerJsFile('https://js.stripe.com/v2/', array('position'=>View::POS_HEAD));
$this->registerJsFile('https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js', array('position'=>View::POS_HEAD));

$this->registerJs( $js_code, View::POS_HEAD);



use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 


?>

<!--can you have more than one stripe account?-->


<?php if($stripe_account): ?>
<div class="roundbox padding10">
  <h3>Account On File</h3>

  <ul class="list-group clearfix">

    <?php foreach($stripe_account->external_accounts->data as $s): ?>
    <li class="list-group-item clearfix"> 
        <h5><span class="fa fa-money marginRight5"></span>Account ending in <?= $s['last4'] ?></h5>

        <ul>
         <li><label>Account Holder Name</label><span></span> <?= $s['account_holder_name']?></span></li>
        <li><label>Bank Name</label><span> <?= $s['bank_name']?></span></li>
        </ul>

        

    </li>

     <?php endforeach; ?>
  </ul>


</div> <!--end of roundbox-->
<?php endif; ?>



<div class="roundbox pad10 marginTop10">

<?php if(!$stripe_account): ?>
<h2>Add Bank Account </h2>
<?php else: ?>
  <h2>Update Bank Account</h2>
<?php endif; ?>


<hr width="100%" size="1">


<div id="account-form" >
	<?php
		$form = ActiveForm::begin([ 'id' =>'baccount-form',
                            'options'=>  ['enctype' => 'multipart/form-data']
                            ]);


	?>

<!-- these have to be defined as properties in the addStripeAccountForm model class
	eg. public string $first_name; 
-->
	

	<?= $form->field($formModel, 'first_name') ?> 
	<?= $form->field($formModel, 'last_name') ?>
	<?= $form->field($formModel, 'dob') ?>
	<?= $form->field($formModel, 'legal_entity_type')->dropDownList( $legalentities, array('prompt'=>'Select Type')) ?>

	<?= $form->field($formModel, 'line1') ?>
	<?= $form->field($formModel, 'city') ?>
	<?= $form->field($formModel, 'state') ?>
	<?= $form->field($formModel, 'postal_code') ?>

	<?= $form->field($formModel, 'ssn_last_4') ?>


	<?= $form->field($formModel, 'tos')->checkBox( array('value'=> '1') ) ?>



	<?php ActiveForm::end() ?>




<!--need to pass these to Stripe without touching our server. 
     we are going to get back a token representing the bank account info and use that
     for handling bank account info. on submit, stripe.js butts in and submits the info to stripe and turns
     it into a token -->

	<form action="" method="POST" id="bank-form">
      <span class='payment-errors'></span>


    <p>
		<label>Bank Routing Number</label> <br/>
		<input type="text" size="40" autocomplete="off" data-stripe="routing_number" class="form-control">
		<span>Enter the number without spaces or hyphens.</span>
    </p>
    
    <p>
		<label>Bank Account Number</label> <br/>
		<input type="text" size="40" autocomplete="off" data-stripe="account_number" class="form-control">
    </p>

		<input type="hidden" size="3" autocomplete="off" data-stripe="country" value="US">
		<input type="hidden" size="4" autocomplete="off" data-stripe="currency" value="usd">

    <p>
    <label>Bank Account Holder Name</label> 
    <br/>
		<input type="text" size="40" autocomplete="off" data-stripe="account_holder_name" class="form-control" >
    </p>

    <p>
    <label>Bank Account Entity Type</label>
    <select autocomplete="off" data-stripe="account_holder_type" class="form-control">
        <option>Individual</option>
        <option>Company</option>
    </select>

    </p>

    <p>
        <?= Html::a('Cancel', ['/profile/edit-profile', 'userid'=>Yii::$app->user->identity->id], 
          ['class'=>'btn btn-default']) ?>

    		<button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
    </p>


  	<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />

	</form>
</div>

</div> <!--end of round box-->


