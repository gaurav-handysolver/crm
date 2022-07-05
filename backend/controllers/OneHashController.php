<?php
namespace backend\controllers;

use backend\models\OneHash;
use common\components\onehash\OneHashService;
use yii\web\Controller;
use Yii;

class OneHashController extends Controller{

    public function actionSaveSetting(){
        $oneHash =   new OneHash();
        $record = OneHash::find()->one();

        //Update onehash setting status
        if(!empty($record)){
           if($record->load(Yii::$app->request->post())){
               $record->setting_name = "OneHash";
               $record->is_enabled = $_POST['OneHash']['is_enabled'];
               if($record->save()){
                   return $this->render('one-hash-setting',['model'=>$record]);
               }
           }
            return $this->render('one-hash-setting',['model'=>$record]);
        }

        //Create onehash setting status (first time)
        if($oneHash->load(Yii::$app->request->post())) {

            $oneHash->setting_name = "OneHash";
            $oneHash->is_enabled = $_POST['OneHash']['is_enabled'];
            if($oneHash->save()){
                return $this->render('one-hash-setting',['model'=>$oneHash]);
            }
        }
        return $this->render('one-hash-setting',['model'=>$oneHash]);
    }
}