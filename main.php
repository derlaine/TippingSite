<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\widgets\Alert;

use yii\helpers\Url;


/* @var $this \yii\web\View */
/* @var $content string */


AppAsset::register($this);

function camelCase($str, array $noStrip = [])
{
    // non-alpha and non-numeric characters become spaces
    $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
    $str = trim($str);
    // uppercase the first character of each word
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    $str = lcfirst($str);

    return $str;
}



$action = Yii::$app->controller->action->id;
//$angular = camelCase($action);
$angular = '';
$controller = Yii::$app->controller->id;
$container_class = 'container';

//full width jumbotron need to exist outside a container
if ($action == 'index' && $controller == 'site'){
    $container_class = '';
}

if(!file_exists( Yii::$app->basePath.'/web/js/'.$angular.'.js')) {
    $angular = '';
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="<?=$angular?>">
<head>

    <meta id="baseURL" url="<?= Url::to('@web/') ?>">

    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">


</head>
<body >
    <?php if($angular): ?>
    <script type="text/javascript" src="<?= Url::to('@web/js/'.$angular.'.js') ?>"></script>
    <?php endif; ?>

    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'My Site',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-light navbar-fixed-top', //used to be navbar-inverse
                    'style'=>'background-color:white;'
                ],
            ]);
            $menuItems = [

                ['label' => 'Discover', 'url' => ['/browse/index']],
                //['label' => 'Post', 'url' => ['/post/index']],
                             
            ];

            if( isset( Yii::$app->user->identity->id )){
               
                $ta0 = ['label' => 'Home', 'url' => ['/feed/home-feed']];
                $ta1 = ['label' => 'Post', 'url' => ['/post/create']];

                $ta2 = 
                        ['label' => 'My Profile',
                            'items' => [ 
                                        ['label'=>'My Profile',
                                         'url'=> ['/profile/user-profile',  'userid'=> Yii::$app->user->identity->id]],

                                         ['label'=>'Edit My Profile',
                                         'url'=> ['/profile/edit-profile']],

                                        ['label' => 'My Subscribers', 
                                          'url' => ['/suscribe/my-subscribers']],

                                        ['label'=>'Logout', 
                                         'url'=>['/site/logout'],  
                                         'linkOptions' => ['data-method' => 'post']],

                            ] //end of items
                        ]; //end of label
                        

                array_push( $menuItems, $ta0);
                array_push( $menuItems, $ta1);
                array_push( $menuItems, $ta2);

            }


            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
        ?>

        <!--we want full width jumbotron for the site index-->
        <!--really hacky but i'd rather do this than the other hacks out there-->
            <div class="<?= $container_class ?>">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
        </div>


          <footer class="footer" >
        <nav class="navbar navbar-default">

            <div class="container clearfix">

            <div class="collapse navbar-collapse pull-left" >
            <ul class="nav navbar-nav" >
                <li><a href="<?= Url::to(['/site/about']);?>" style="padding-top: 0 !important;">About</a></li>
                <li><a href="<?= Url::to(['/site/contact']);?>" style="padding-top: 0 !important;">Contact</a></li>       
            </ul>
            </div>

            <p class="pull-right">&copy; My Company <?= date('Y') ?></p>

            </div>
        </nav>
    </footer>
        
    </div>

  

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
