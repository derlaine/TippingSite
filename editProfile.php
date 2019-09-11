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


$dir = Url::to('@web/uploads/'.Yii::$app->user->identity->username.'/');
$thumbsize = Yii::$app->params['PROFILE_THUMB_SIZE'];

$script = '
$(document).ready(function(){
	$("#account-form").hide();

    $("#hide").click(function(){
        $("#account-form").hide();
    });
    $("#show").click(function(){
        $("#account-form").show();
    });
});';

// yii\web\View is the same thing as the above use yii\helpers\Html stuff
$this->registerJs($script, yii\web\View::POS_END, 'my-options');


use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 

$display_options = [0=>'Monthly Total: "$'.number_format($income['real_monthly_total'], 2).' a month"', 
                    2=>'X Digits a Year: "'.strlen((string)$income['yearly_total']).' digits a year"', 
                    3=>'Yearly Range: '. $income['yearly_range'],  
                    4=>'Private: Do not display'];

?>



<div class="roundbox profile-left pad10 margin-right-10" >

<ol>
<li> 
    Who are you
    <br />
    <span class="fa fa-user"></span>Edit Profile
</li>


<li>How you get paid
    <br />
     <span class="fa fa-money"></span> <a href="<?= Url::to(['profile/add-stripe-account'])?>">Accept Payments</a>
</li>

<li>What you sell
    <br />
    <?php if($has_account): ?>
        <span class="glyphicon glyphicon-th-list"></span>
        <a href="<?= Url::to(['profile/manage-stripe-plans'])?>">Manage Your Plans</a>
        
    <?php else: ?>
         <span class="glyphicon glyphicon-info-sign"></span>Don't skip step 2
    <?php endif; ?>
</li>



<li class="step">How you pay
    <br/>
   <span class="glyphicon glyphicon-credit-card"></span><a href="<?= Url::to(['profile/manage-credit-card'])?>">Make Payments</a>
    <span class="fa fa-credit-card"></span><a href="<?= Url::to(['profile/manage-subscriptions'])?>">Manage Subscriptions </a>
</li>

</ol>



</div>





<div class="roundbox profile-right pad10 margin-left-10">
<h1>Edit Your Profile</h1>

<?php
$form = ActiveForm::begin([ 'id' =>'profile-form',
                            'options'=>  ['enctype' => 'multipart/form-data']
                            ]);
?>

<!--upload profile photo -->
<?php if($user->profilepic): ?>
    <img src="<?= $dir.$thumbsize.'_'.$user->profilepic ?>">
<?php endif; ?>

<?= $form->field($formModel, 'profilepic')->fileInput()->hint('Profile Pics are 128 x 128 pixels, max size 50kb
')->label('Profile Picture') ?> 

<!--upload profile banner-->
<?php if($user->profilebanner): ?>
    <img class="bannerpreview" src="<?= $dir.$user->profilebanner ?>">
<?php endif; ?>
<?= $form->field($formModel, 'profilebanner')->fileInput()->hint('Profile Banners are 280px x 1500px , max size ABCkb
')->label('Profile Banner') ?> 


<?= $form->field($formModel, 'profileblurb')->textArea()->label('Profile Bio') ?>

<?= $form->field($formModel, 'website') ?>

<?= $form->field($formModel, 'facebook') ?>

<?= $form->field($formModel, 'twitter') ?>

<?= $form->field($formModel, 'instagram') ?>

<?= $form->field($formModel, 'tumblr') ?>

<?= $form->field($formModel, 'display_options')->dropDownList( $display_options, array('prompt'=>'Select One', ))->label('Display Options')->hint('You currently make $'.number_format( $income['real_monthly_total'], 2).' a month. How do you want to display this on your profile?'); ?> 

            <?= Html::a('Cancel', ['/profile/user-profile', 'userid'=>Yii::$app->user->identity->id], 
                    ['class'=>'btn btn-default']) ?>

            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>

</div>

