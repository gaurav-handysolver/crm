<?php

namespace api\modules\v1\controllers;

use common\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\rest\Controller;
use yii;
use yii\web\HttpException;

/**
 * @SWG\SecurityScheme(
 *   securityDefinition="Bearer",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization",
 *   description = "Click the link to see how to pass JWT token https://share.getcloudapp.com/NQujP5o2"
 * )
 */
class BaseController extends yii\rest\ActiveController
{
    const JWT_SECRET_KEY = "NFqRh33ofXCLYQ9SvFiX3lnBa7qLl2NcMBj_gYMaTCwdcxSIqY3rYxJ2UWXiE1R0Ow0oYg4fJk9HaVGykWzFry";


   public function beforeAction($action)
   {
        Yii::$app->response->format = 'json';
        Yii::$app->request->setBodyParams(null);

        $user = $this->authWithJwt();

        if($user == null){
            throw new HttpException('401', 'Invalid token!');
        }

        return $user;
   }

    /**
     * Authentication using JWT Bearer Header
     *
     * @return User|null
     * @throws HttpException
     */

   private function authWithJwt(){

       $authHeader = Yii::$app->request->getHeaders()->get('Authorization');

       if(!empty($authHeader) && preg_match('/^Bearer\s+(.*?)$/',$authHeader,$matches)){
         $token = $matches[1];

         try{
              $validData = JWT::decode($token, new key(self::JWT_SECRET_KEY,'HS512'));
              if($validData->email){
                 return  User::find()->where(['email'=>$validData->email])->one();
              }

         }catch (Exception $e){
           throw new HttpException(401, $e->getMessage());
         }
       }
       return null;
   }
}