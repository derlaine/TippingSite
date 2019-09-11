<?php

namespace frontend\controllers;

use frontend\models\createPostForm;
use Yii;
use frontend\models\posts;
use app\models\PostSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use frontend\models\StripeAccounts;

use yii\filters\VerbFilter;

use yii\helpers\BaseFileHelper;
use yii\web\UploadedFile;

use yii\imagine\Image;
use common\models\User;



/**
 * PostController implements the CRUD actions for posts model.
 */
class PostController extends Controller
{



    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all posts models.
     * @return mixed
     */
    public function actionIndex()
    {

        //can comment out all the following when i'm done, no need to search through grid view for my posts
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single posts model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {

        $post = $this->findModel($id);

        if(!$post->userid){
            throw new HttpException('Database Error: User ID faulty in post ');
        }

        //get the username of the userid
        $user = User::find()->select('username')->where(['id'=>$post->userid])->one();

        return $this->render('view', [
            'post' => $post, 'user' => $user
        ]);
    }

    /**
     * Creates a new posts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws HttpException
     */
    public function actionCreate()
    {

        $allPlans = [];
        $post = new createPostForm();

        if(Yii::$app->request->isPost){
            $tempfile = UploadedFile::getInstance($post, 'imgpath');
        }

        //load all my stripe plans
       \Stripe\Stripe::setApiKey(Yii::$app->params['sk_test']);
        $stripeacc = StripeAccounts::findOne( ['userid'=>Yii::$app->user->identity->id]);

        $hasAccount = $stripeacc ? TRUE: FALSE;

        if($stripeacc) {
            $allPlans = \Stripe\Plan::all([],['stripe_account'=> $stripeacc->stripe_id]);
            $allPlans = $allPlans['data'];
        }

        //no input, return to create post
        if(! $post->load(Yii::$app->request->post())) {
            return $this->render('create', ['model' => $post, 'allPlans' => $allPlans, 'hasAccount'=>$hasAccount]);
        }

        if($tempfile){

            $post->imgpath = $tempfile;

            $dir = Yii::$app->basePath .'/web/uploads/'. Yii::$app->user->identity->username.'/';

            //make a directory if it doesn't already exist
            if(! file_exists($dir)){
                BaseFileHelper::createDirectory($dir, 755);
            }


            //save the fullsize image
            if($post->imgpath) {
                //basepath gives an absolute path without ~username, url::to gives ~localhost which is unusable here
                $post->imgpath->saveAs($dir . $post->imgpath->name);
            }

            //save out a thumbnail/smaller version for feed display
            list($width, $height, $type, $attr) = getimagesize($dir . $post->imgpath->name);

            $displayWidth = Yii::$app->params['IMG_DISPLAY_WIDTH'];
            $new_height = $height;

            if($width > $displayWidth){
                //aspect ratio: original height / original width x new width = new height
                $new_height = $height / $width * $displayWidth ;
                $new_height = ceil($new_height);
            }

            /* UGH do this later
            if($width <= $displayWidth){
                 //aspect ratio: original height / original width x new width = new height
                $new_height = $height / $width * $displayWidth ;
                $new_height = ceil($new_height);
            }
            */

                Image::thumbnail($dir . $post->imgpath->name, $displayWidth, $new_height)
                    ->save(Yii::getAlias($dir . $displayWidth. '_'.$post->imgpath->name), ['quality' => 100]);
            //}


        }

        $id = $post->createPost();

        if(!$id){
            throw new HttpException('Error: Could not save form to database, please try again.');
        }

         return $this->redirect(['view', 'id' => $id]);        
    }

    /**
     * Updates an existing posts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing posts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }



    /**
     * Finds the posts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return posts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = posts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
