<?php
/**
 * Created by PhpStorm.
 * User: derlaine
 * Date: 4/24/15
 * Time: 5:25 PM
 */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\validators\UrlValidator;
use \yii\widgets\LinkPager;


$loggedin=Yii::$app->user->identity->id;
$userid = $user->id;
$dir = Url::to('@web/uploads/'.$user->username.'/');
$thumbsize = Yii::$app->params['PROFILE_THUMB_SIZE'];
$displayWidth = Yii::$app->params['IMG_DISPLAY_WIDTH'];

use frontend\assets\ProfileAsset;
ProfileAsset::register($this); 

$income = explode(' a ', $income_string);
?>


<div id="profileCanopy">

     <div id="names">
         <span id="firstName">First Name, Last Name </span> <span id="username"> (<?= $user->username ?>) </span>
     </div>

    <div id="profileBanner" style="margin-top:0;">
         <?php if($user->profilebanner): ?>
            <img src="<?=$dir .$user->profilebanner ?>" />
        <?php else: ?>
            <img src="<?= Url::to('@web/assets/misc/blankprofilebanner.jpg'); ?>" />
        <?php endif; ?>
    </div>



     <div id="profileStats">
        <ul>
            <li><span class="statBold"><?=  is_null($userStats) ? 0:  $userStats->total_followers; ?></span> <br/><span class="statSmall">followers</span></li>

            <li><span class="statBold"> <?= is_null($userStats) ? 0: $userStats->total_subscribers; ?></span> <br/><span class="statSmall">subscribers</span></li>

            <?php if(!isset($income[1])): ?>

            <?php else: ?>
            <li><span class="statBold"><?= $income[0] ?> </span> <br/><span class="statSmall">a <?= $income[1]?></span></li>
            <?php endif;?>
        </ul>


        <?php if( $userid == $loggedin): ?>
            <a class="btn btn-default" href="<?= Url::to(['profile/edit-profile', 
            'userid'=>Yii::$app->user->identity->id])?>">Edit this page </a>

        <?php elseif(  $subscribed_to):?>

             <a class="btn btn-default" href="<?= Url::to(['suscribe/cancel-subscription', 
            'userid'=>$userid])?>">Unsubscribe</a>

        <!--if user has not added bank account, do not show subscribe-->

        <?php elseif($has_account && $userid != $loggedin): ?>

            <a class="btn btn-success subscribe" href="<?= Url::to(['suscribe/suscribe', 'userid'=>$userid])?>">Subscribe </a>

        <?php endif; ?>

        <!--if already following, show unfollow -->
        <?php if($userid != $loggedin && !$isFollowing): ?>
             <a class="btn btn-default" href="<?= Url::to(['follow/follow', 'userid'=>$userid]) ?>">Follow</a>
        <?php elseif($userid != $loggedin && $isFollowing): ?>
             <a class="btn btn-default" href="<?= Url::to(['follow/unfollow', 'userid'=>$userid]) ?>">Unfollow</a>
        <?php endif; ?>

       



    </div> <!--end of ProfileStats-->

    <div id="profilePic">
             <?php if($user->profilepic): ?>
                 <img src="<?=$dir.$thumbsize.'_' .$user->profilepic ?>">
            <?php else: ?>
                <img src="<?= Url::to('@web/assets/misc/anonuser.png'); ?>" />

            <?php endif;?>
     </div>




</div> <!--end of profileCanopy-->

<!--******************************************************************************-->

<div id="empty"></div>

<div class="theposts clearfix">


<!-- extra user info like website and social media links-->

<?php if( $user->profileblurb || $user->twitter || $user->tumblr || $user->facebook || $user->instagram): ?>
<div id="userInfo" class="roundbox pull-left ">

    <p class="text"> 
    <?= $user->profileblurb; ?>
    </p>

    <div id="socialmedia">
              

                    <?php
                    $uv = new UrlValidator();
                    $http_twitter = $http_fb = $http_tumblr = $http_insta = '';
                    if(! $uv->validate($user->twitter)){
                        $http_twitter = 'http://';
                    }

                    if(! $uv->validate($user->facebook)){
                        $http_fb = 'http://';
                    }

                    if(! $uv->validate($user->instagram)){
                        $http_insta = 'http://';
                    }

                    if(! $uv->validate($user->tumblr)){
                        $http_tumblr = 'http://';
                    }

                      if(! $uv->validate($user->website)){
                        $http_website = 'http://';
                    }

                    if($user->twitter)
                        echo Html::a('', $http_twitter.$user->twitter , ['target'=>'new',
                            'class'=>"fa fa-twitter fa-lg", 'aria-hidden'=>"true" ]) ;

                    if($user->facebook)
                         echo Html::a('', $http_fb.$user->facebook , ['target'=>'new',
                            'class'=>"fa fa-facebook fa-lg" ,'aria-hidden'=>"true"]) ;

                    if($user->tumblr)
                        echo  Html::a('', $http_tumblr.$user->tumblr , ['target'=>'new',
                            'class'=>"fa fa-tumblr fa-lg" ,'aria-hidden'=>"true"]);

                    if($user->instagram)
                         echo  Html::a('', $http_insta.$user->instagram , ['target'=>'new',
                            'class'=>"fa fa-instagram fa-lg", 'aria-hidden'=>"true"]);

                    if($user->website)
                         echo  Html::a('', $http_website.$user->website , ['target'=>'new',
                            'class'=>"fa fa-globe fa-lg", 'aria-hidden'=>"true"]) ;



                    ?>

            
    </div>

</div> <!--end of user info-->
<?php endif; ?>


<div class="postpost pull-left">
<?php foreach($posts as $post): ?>

<div class="post roundbox clearfix ">
 

    <?php if($post->stripe_plan_id):?>
            <small><span class="glyphicon glyphicon-lock pull-right" aria-hidden="true" 
                         alt="<?= $post->stripe_plan_id?>"></span></small>
      <?php endif; ?>
     

    
      <?php if($post->title): ?>
                  <div class="postTitle" >
                    <a href="<?= Url::to(['post/view', 'id'=>$post->id])?>">
                    <h4 ><?=$post->title?></h4>
                    </a>
                </div>
    <?php endif; ?>


    <?php if($post->imgpath): ?>
    <a href="<?= $dir.$post->imgpath ?>"><img src="<?= $dir. $displayWidth . '_'. $post->imgpath?> "></a>

    <?php else: ?>
    <!--no image path-->

    <?php endif; ?>

    <p class="text"><?=$post->text?></p>
</div> <!--end of a post -->
<?php endforeach; ?>

</div> <!--end of postposts--> 
</div> <!-- end of thePosts-->


<div class=" col-md-offset-7 col-md-2" style="margin-left:55%">
    <?php
    echo LinkPager::widget([
    'pagination' => $pages,
    ]);

    ?>
  
</div>

    <follow userid="<?=$userid?>" loggedin="<?=$loggedin?>"></follow>


