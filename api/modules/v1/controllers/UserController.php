<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\LoginForm;
use common\models\User;
use Firebase\JWT\JWT;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\HttpHeaderAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\rest\OptionsAction;
use yii\web\NotFoundHttpException;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UserController extends Controller
{
    /**
     * @return array
     */
//    public function behaviors()
    //    {
    //        $behaviors = parent::behaviors();
    //
    //        $behaviors['authenticator'] = [
    //            'class' => CompositeAuth::class,
    //            'authMethods' => [
    //                HttpBasicAuth::class,
    //                HttpBearerAuth::class,
    //                HttpHeaderAuth::class,
    //                QueryParamAuth::class
    //            ]
    //        ];
    //        return $behaviors;
    //    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => OptionsAction::class
            ]
        ];
    }

    /**
     * @return User|null|ActiveDataProvider|\yii\web\IdentityInterface
     */
    public function actionIndex()
    {
        $resource = new User();
        $resource->load(Yii::$app->user->getIdentity()->attributes, '');
        return  $resource;
    }


    /**
     * @return array
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $jwt = $this->generateJwtToken($model->getUser()->email);
            $user = $model->getUser();
            return ['status' => 1, 'messege' => 'success', 'payload' => [
                'id'=> $user->id,
                'username'=> $user->username,
                'access_token'=> $user->access_token,
                'onehash_token' => $user->onehash_token,
                'jwt_token' => $jwt
            ]];
//            return [
//                'id'=> $user->id,
//                'username'=> $user->username,
//                'access_token'=> $user->access_token,
//                'onehash_token' => $user->onehash_token,
//                'jwt_token' => $jwt
//            ];
//            return $model->getUser()->toArray(['id', 'username', 'access_token','onehash_token']);
        }

        Yii::$app->response->statusCode = 422;
        return ['status' => 0, 'messege' => 'validation failed', 'payload' => $model->errors];
//        return ['errors' => $model->errors,];
    }

    /**
     * @param $email
     * @return string|null
     * Generate JWT token using user's email address
     */
    public function generateJwtToken($email){
        $issueAt = time();

        $data = [
            'iat'=>$issueAt,
            'nbf' => $issueAt,
            'email' => $email
        ];

        $jwt = JWT::encode($data,BaseController::JWT_SECRET_KEY,'HS512');

        if(isset($jwt)){
            return $jwt;
        }
        return null;
    }

}