<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

use yii\widgets\ActiveForm;



/* @var $this yii\web\View */
/* @var $model app\models\posts */

//$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];

$dir = Url::to('@web/uploads/'.$user['username'].'/');
$displayWidth = Yii::$app->params['IMG_DISPLAY_WIDTH'];



use frontend\assets\HomefeedAsset;
HomefeedAsset::register($this); 


//need to make this url pretty :|
?>


<div class="roundbox clearfix post">

       <?php
    $dir = Url::to('@web/uploads/'.$user['username'].'/');
    ?>

     <?php if($user['profilepic'] ): ?> 
        <img class="profilepic pull-left" src="<?= $dir. $thumbsize.'_' . $users[$post->userid]['profilepic']?>">
     <?php else: ?> 
        <img class="profilepic pull-left" src="<?= Url::to('@web/uploads/anon.jpg')?>">
    <?php endif; ?>

    <span class="username pull-left">
        <a href="<?= Url::to(['profile/user-profile', 'userid'=>$post->userid])?>">
            <?= $user['username'] ?></a></span>






      <?php if($post->stripe_plan_id):?>
            <small><span class="glyphicon glyphicon-lock pull-right" aria-hidden="true" 
                         alt="<?= $post->stripe_plan_id?>"></span></small>
      <?php endif; ?>

      <hr class="faintHR" />

        <?php if($post->title): ?>
             <div class="postTitle" >
                    <a href="<?= Url::to(['post/view', 'id'=>$post->id])?>">
                     <h4><?=$post->title?></h4>
                     </a>
             </div>
    <?php endif; ?>


    <?php if($post->imgpath): ?>
    <a href="<?= $dir.$post->imgpath ?>"><img src="<?= $dir. $displayWidth . '_'. $post->imgpath?> "></a>

    <?php else: ?>
     <!--no image path-->

    <?php endif; ?>

    <p class="text"><?=$post->text?></p>

    <hr class="faintHR">

    <div class="postFooter">
        <span class="col-sm-4 timestamp">
        <?= date('Y M j ga   ', $post['timestamp']) ?>
        </span>


        <!--if no stripe account, do not show tip icon-->
        <?php if($post->accept_tips && $post->userid != Yii::$app->user->identity->id): ?>
             <span  class="fa fa-coffee fa-lg pull-right " aria-hidden="true"  alt="tip" 
                data-toggle="modal" data-target="#myModal" pid="<?=$post->userid?>"></span>
        <?php endif; ?>

         <span class="pull-right">
         &nbsp; Share
        </span>

        <span class="pull-right">
        &nbsp; Likes &#0149;
        </span>

        <span class="pull-right ">
        Comments &#0149;
        </span>
    </div>


   

</div> <!--end of roundbox -->

<?php if($post->userid ==  Yii::$app->user->identity->id): ?>

    <p class="pull-right">
        <?= Html::a('Update', ['update', 'id' => $post->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $post->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

<?php endif; ?>





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

         

          <p>Put provider profile pic here </p>

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
