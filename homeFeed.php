<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/29/15
 * Time: 12:56 PM
 */

use yii\helpers\Url;
use \yii\widgets\LinkPager;
use yii\web\View;
use yii\widgets\ActiveForm;



use frontend\assets\HomefeedAsset;
HomefeedAsset::register($this); 



$displayWidth = Yii::$app->params['IMG_DISPLAY_WIDTH'];
$thumbsize = Yii::$app->params['PROFILE_THUMB_SIZE'];



?>

<div class="theposts">

<!-- each post should be a reusable template, resused in view, homefeed, userprofile -->

<?php foreach($posts as $post): ?>

<div class="post clearfix roundbox" >

    <?php
    $dir = Url::to('@web/uploads/'.$users[$post->userid]['username'].'/');
    ?>

     <?php if($users[$post->userid]['profilepic'] ): ?> 
        <img id="profilePic_<?=$post->userid?>" class="profilepic pull-left" 
            src="<?= $dir. $thumbsize.'_' . $users[$post->userid]['profilepic']?>">
     <?php else: ?> 
        <img id="profilePic_<?=$post->userid?>" class="profilepic pull-left" 
             src="<?= Url::to('@web/uploads/anon.jpg')?>">
    <?php endif; ?>

    <span id="username_<?=$post->userid?>" class="username pull-left" username="<?=$users[$post->userid]['username']?>">
        <a href="<?= Url::to(['profile/user-profile', 'userid'=>$post->userid])?>">
            <?= $users[$post->userid]['username'] ?></a></span>



     <?php if($post->stripe_plan_id):?>
            <small><span class="glyphicon glyphicon-lock pull-right" aria-hidden="true" 
                         alt="<?= $post->stripe_plan_id?>"></span></small>
      <?php endif; ?>

      <hr class="faintHR" /> 



      <?php if($post->title): ?>
        <div class="postTitle" >
          <a href="<?= Url::to(['post/view', 'id'=>$post->id])?>"><h4 class=""><?=$post->title?></h4></a>
        </div>
    <?php endif; ?>


    <?php if($post->imgpath): ?>
    <a href="<?= $dir.$post->imgpath ?>"><img src="<?= $dir. $displayWidth . '_'. $post->imgpath?> "></a>

    <?php else: ?>
     <!--no image path-->

    <?php endif; ?>

    <p class="text"><?=$post->text?></p>


    <span class="fa fa-heart fa-lg pull-right" aria-hidden="true"  alt="tip"></span>

<!--if no stripe account, do not show tip icon-->
<?php if($post->accept_tips && $post->userid != Yii::$app->user->identity->id): ?>
    <span  class="fa fa-coffee fa-lg pull-right " aria-hidden="true"  alt="tip" 
           data-toggle="modal" data-target="#myModal" pid="<?=$post->userid?>"></span>
<?php endif; ?>

</div> <!--end of a post -->
<?php endforeach; ?>

<?php
echo LinkPager::widget([
'pagination' => $pages,
]);
?>


</div> <!--end of theposts -->

<!--hey hey hey hey hey hey hey -->


<!-- Trigger the modal with a button -->
<!--<span class="fa fa-coffee fa-lg tipButton" data-toggle="modal" data-target="#myModal"></span>-->

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content payment-modal">

     <?php
          $form = ActiveForm::begin([ 'id' =>'tip-form', 'action'=> ['feed/tip'],
                                  'options'=>  ['enctype' => 'multipart/form-data']
                                  ]);


        ?>

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Payment</h4>
      </div>


      <div class="modal-body">

        <div class="center-body">

         

           <div class="profilePicCont">         
                 <img class="profilePic" id="tipProfilePic" src="">
          </div> <!--end of profilepic div-->

          <div id="tipUsername">
          </div>

          <div class="btn-group">
            <button type="button" class="btn btn-default amt-btn" value="1">$1</button>
            <button type="button" class="btn btn-default amt-btn" value="2">$2</button>
            <button type="button" class="btn btn-default amt-btn" value="5">$5</button>
            <button id="amt-other" type="button" class="btn btn-default ">other</button>
          </div> <!--end of btn group -->

          <input id="amt-custom" type="text" style="display:none;" class="pull-right"
                placeholder="$0.00" />
          
          <input id="form-amount" type="hidden" name="amount" value="" />

        </div> <!--end of center body-->

        <br />

        <input type="hidden" id="providerid" name="provider_id" value="26"/>

        <label>Card Number</label>  
        <input type="text" class="form-control" data-stripe="number" autocomplete="cc-number" size="20">


        <div class="row cc-expiry">
          <div class="col-sm-3">
          <input type="text" class="form-control" placeholder="MM" data-stripe="exp_month" autocomplete="cc-exp" size="2">
          </div>

          <div class="col-sm-3">
          <input type="text" class="form-control" placeholder="YY" data-stripe="exp_year" >
          </div>

          <div class="col-sm-3">
          <input type="text" class="form-control col-sm-1" placeholder="CVV" data-stripe="cvc" >
          </div>
        </div> <!--end of row-->

        <span class="payment-errors"></span>


      </div> <!--end of modal body-->

      <div class="modal-footer">

       <div class="center-footer">
       <button type="submit" class="btn btn-primary" id="submitBtn">Pay</button>
       </div> <!--end of center footer-->

      </div> <!--end of modal footer-->

        <?php ActiveForm::end() ?>


    </div><!--end of modal content-->

  </div> <!--end of modal dialog-->
</div>