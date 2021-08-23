<?php

    namespace api\modules\v1\controllers;
    use api\modules\v1\resources\Contact;
    use Yii;
    use yii\data\ActiveDataProvider;
    use yii\helpers\Url;
    use yii\rest\ActiveController;
    use yii\web\NotFoundHttpException;

    /**
     * Created by PhpStorm.
     * User: cyberains
     * Date: 16-07-2021
     * Time: 10:26
     */

class ContactController extends ActiveController
{
    public $modelClass = Contact::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => Contact::find(),
            'pagination' => false
        ]);
        return $activeData;
    }


    public function actionCreate()
    {

//        ini_set("memory_limit","50M");
        $post = file_get_contents("php://input");
        $conJson = (array) \json_decode($post);

        $contact= new Contact();
        $contact->firstname= $conJson['firstname'];
        $contact->lastname= $conJson['lastname'];
        $contact->company= $conJson['company'];
        $contact->email= $conJson['email'];
        $contact->mobile_number= $conJson['mobile_number'];
        $contact->website= $conJson['website'];
        $contact->notes= $conJson['notes'];
        $contact->address= $conJson['address'];
        $contact->pollguru= $conJson['pollguru'];
        $contact->buzz= $conJson['buzz'];
        $contact->learning_arcade= $conJson['learning_arcade'];
        $contact->training_pipeline= $conJson['training_pipeline'];
        $contact->leadership_edge= $conJson['leadership_edge'];
        $contact->created_by= $conJson['created_by'];

        $realImage = base64_decode($conJson['image']);
        if (!empty($realImage) ){


            $myfile = fopen(Yii::getAlias('@storage').'/web/source'.'/'.$contact->email.'.jpeg',"a") or die("Unable to open location for log file !");
//            $txt = "Log details goes here ...";
            fwrite($myfile, $realImage);
            fclose($myfile);
            $contact->imageUrl = Url::to('/storage/web/source'.'/'.$contact->email.'.jpeg',true);


//            if(file_put_contents(Yii::getAlias('@storage').'/web/source'.'/'.$contact->email.'.jpeg',$realImage,LOCK_EX | FILE_APPEND)){
//                $contact->imageUrl = Url::to('/storage/web/source'.'/'.$contact->email.'.jpeg',true);
//            }
        }


        if (!$contact->save(false)){
            return $contact->getErrors();
        }

        return $contact;

    }

    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdate($id)
    {
        $post = file_get_contents("php://input");
        $conJson = (array) \json_decode($post);

        $contact = $this->findModel($id);
        $contact->firstname= $conJson['firstname'];
        $contact->lastname= $conJson['lastname'];
        $contact->company= $conJson['company'];
        $contact->email= $conJson['email'];
        $contact->mobile_number= $conJson['mobile_number'];
        $contact->website= $conJson['website'];
        $contact->notes= $conJson['notes'];
        $contact->address= $conJson['address'];
        $contact->pollguru= $conJson['pollguru'];
        $contact->buzz= $conJson['buzz'];
        $contact->learning_arcade= $conJson['learning_arcade'];
        $contact->training_pipeline= $conJson['training_pipeline'];
        $contact->leadership_edge= $conJson['leadership_edge'];

        $realImage = base64_decode($conJson['image']);




        if (!empty($realImage) ){
            $contact->imageUrl = "";
            $myfile = fopen(Yii::getAlias('@storage').'/web/source'.'/'.$contact->email.'.jpeg',"w+") or die("Unable to open location for log file !");
//            $txt = "Log details goes here ...";
            fwrite($myfile, $realImage);
            fclose($myfile);
            $contact->imageUrl = Url::to('/storage/web/source'.'/'.$contact->email.'.jpeg',true);
        }else{
            $contact->imageUrl = null;

        }


        if (!$contact->save(false)){
            return $contact->getErrors();
        }

        return $contact;

    }
}

//
//// The first method
//    $post = $GLOBALS['HTTP_RAW_POST_DATA'];
//// The second method
//    $post = file_get_contents("php://input");
