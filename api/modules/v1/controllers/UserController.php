<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\LoginForm;
use common\models\User;
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
            return $model->getUser()->toArray(['id', 'username', 'access_token']);
        }

        Yii::$app->response->statusCode = 422;
        return [
            'errors' => $model->errors,
        ];
    }

}