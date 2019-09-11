<?php

use yii\helpers\Html;


use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\posts */

$this->title = 'Create Posts';
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$my_stripe_plans = [];

foreach ($allPlans as  $p) {
   $my_stripe_plans[$p->id] = $p->id . ': $'.number_format($p->amount/100,2) . ' every ' .
                                 $p->interval_count .' '. $p->interval ;
}


use frontend\assets\PostAsset;
PostAsset::register($this); 


?>


<div class="posts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $form = ActiveForm::begin([ 'id' =>'posts-form',
        'options'=>  ['enctype' => 'multipart/form-data']
    ]);
    ?>

    <?= $form->field($model, 'imgpath')->fileInput() ?>



    <?= $form->field($model, 'title') ?>
    <?= $form->field($model, 'text')->label('Text')->textArea(['rows' => '6']) ?>

    <?= $form->field($model, 'stripe_plan_id')->dropDownList( $my_stripe_plans, ['prompt'=>'Everyone', ])->label('Who can see this post?'); ?>

    <div class="checkbox email_errone" style="display:none;">
    <label> <?= Html::checkbox( 'createPostForm[email_errone]', TRUE)?>Email everyone at this tier </label>
    </div>


    <?php if($hasAccount): ?>
    <div class="checkbox">
    <label> <?= Html::checkbox( 'createPostForm[accept_tips]', TRUE)?>Acccept Tips? </label>
    </div>
    <?php endif; ?> 

    <div class="form-group">
        <div class="">
            <a class="btn btn-default" href="@web">Cancel</a>

            <?= Html::submitButton('Post', ['class' => 'btn btn-primary']) ?>

        </div>

        
    </div>

    <?php ActiveForm::end() ?>

</div>
