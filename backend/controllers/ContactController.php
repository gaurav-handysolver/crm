<?php

namespace backend\controllers;

use common\components\onehas\OneHasService;
use Yii;
use common\models\Contact;
use backend\models\search\ContactSearch;
use yii\base\ErrorException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use JeroenDesloovere\VCard\VCard;
use yii\widgets\ActiveForm;

/**
 * ContactController implements the CRUD actions for Contact model.
 */
class ContactController extends Controller
{

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Contact models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ContactSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Contact model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($code)
    {
        return $this->render('view', [
            'model' => Contact::find()->where(['code'=>$code])->one()
        ]);
    }

    /**
     * Creates a new Contact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        phpinfo(); die();
        $model = new Contact();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Contact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */

    public function actionImageDelete($code)
    {
        $contact = Contact::find()->where(['code'=>$code])->one();
//        $contact->imageUrl = null;
        if (file_exists(Yii::getAlias('@storage') . '/web/source' . '/' . $contact->code . '.jpeg')){

            if (unlink(Yii::getAlias('@storage') . '/web/source' . '/' . $contact->code . '.jpeg')){
                $contact->imageUrl = '';
                $contact->save();
                return true;
            }
        }
        return false;
    }


    /**
     * @param $code
     * @return array|string|Response
     */
    public function actionUpdateContact($code,$email)
    {
        if (Yii::$app->user->isGuest){
            $this->layout='businesscard';
        }
        $model =Contact::find()->where(['code'=>$code])->andWhere(['email'=>strtolower($email)])->one();
//        for Input Validation : Start
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
//        for Input Validation : End
        if(!empty($model)){
            if ($model->load(Yii::$app->request->post())) {

                $logoFile = UploadedFile::getInstance($model, 'imageUrl');
                $imagePreviousValue = $model->getOldAttribute('imageUrl');

                if (!empty($logoFile)) {
                    $uploadPath = Yii::getAlias('@storage') . '/web/source' . '/' . $model->code . '.jpeg';
                    $upload = $logoFile->saveAs($uploadPath);

                    $Image_path = '/source'.'/'.$model->code.'.jpeg';
                    $url = Yii::getAlias('@storageUrl') . $Image_path;

                    if ($upload) {
                        $model->imageUrl = $url;
                        $model->save();
                    }
                } elseif ($imagePreviousValue!=NULL) {
                    $model->imageUrl = $imagePreviousValue;
                    $model->save();
                }else{
                    $model->imageUrl = NULL;
                    $model->save();
                }

                //Update the contact details on OneHas as well
                $contact = new OneHasService();
                $file_url='';
                if($model->imageUrl!=''){
                    $file_url = $contact->actionOnehashImageUpdate($model);
                }
                $updateLeadData = $contact->actionOnehashUpdate($model,$logoFile,$file_url);

                //  for Address Update  start
                $address_title = $contact->actionFindOnehashAddress($model->email,$model->createdBy->onehash_token);
                if($address_title['status']){
                    $response = $contact->actionOnehashAddressUpdate($model,$address_title['data']);
                }
                //  for Address Update  end

                $contact_title = $contact->actionFindOnehashContact($model->email,$model->createdBy->onehash_token);
                if($contact_title['status']){
                    $updateOneHasData = $contact->actionOnehashContactUpdate($model,$contact_title['data'],$file_url);
                }


        if (Yii::$app->user->isGuest){
            return $this->redirect(['view-contact','code'=>$code]);
        }else{
            return $this->redirect(['view', 'code' => $model->code]);
        }
    }
}else{
    Yii::$app->session->setFlash('error', "Please enter a valid email address. ");
    return $this->redirect(['auth-contact','code'=>$code]);
}

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionAuthContact($code)
    {
        $email = strtolower(Yii::$app->request->post('email_id'));
        $this->layout = 'businesscard';
        $model =Contact::find()->where(['code'=>$code])->one();
        if(empty($model)) {
            throw new HttpException(404, 'Oops! Contact does not exist anymore for the code: ' . $code);
        }
        if(Yii::$app->request->isPost){
            if (strcasecmp($model->email,$email) == 0) {
                return $this->redirect(['update-contact','code'=>$code,'email'=>$email]);
            } else {
                Yii::$app->session->setFlash('error', "Please enter a valid email address. ");
                return $this->render('auth_contact',['model' => $model,]);
            }
        }
        return $this->render('auth_contact',['model' => $model,]);
    }

    /**
     * @return string
     */
    public function actionContactCodeUrl()
    {
        $this->layout = 'businesscard';
        if (Yii::$app->request->post('code') != null){
            $code = Yii::$app->request->post('code');
            $model =Contact::find()->where(['code'=>$code])->one();
            if(Yii::$app->request->isPost) {
                if (isset($model['code']) == $code) {
                    $vcard = new VCard();
                    $firstname = $model['firstname'];
                    $lastname = $model['lastname'];
                    $additional = '';
                    $prefix = '';
                    $suffix = '';
                    // add personal data
                    $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);

                    /*$vcard->addAddress(
                        "Jeroen Desloovere",
                        "(extended info, again)",
                        "25th Some Address",
                        "Townsville",
                        "Area 51",
                        "045784",
                        "Europe (is a country, right?)",
                        'WORK;PERSONAL'
                    );*/

                    $vcard->addAddress(
                        '',
                        '',
                        $model->address ?? '',
                        $model->city ?? '',
                        $model->state ?? '',
                        $model->pincode ?? '',
                        $model->country ?? '',
                        $model->address_type ?? ''
                    );

                    if (!empty($model['imageUrl'])) {
                        $vcard->addPhoto($model['imageUrl'], true);
                    } else {
//                        $profileImage = Url::to('backend/web/img/icon-male.svg', true);
                        $profileImage = "https://handysolver.myhandydash.com/backend/web/images/male.svg";
                        $vcard->addPhoto($profileImage, true);
                    }
                    $vcard->addCompany($model['company']);
                    $vcard->addEmail($model['email']);
                    $vcard->addPhoneNumber($model['mobile_number'], 'PREF;WORK');
//                $vcard->addAddress($model['address']);
                    $vcard->addURL($model['website']);
                    $vcard->setFilename($model['firstname'], true);
                    $vcard->download();
//                return $this->render('contact_url',['model' => $model,]);
                    return $this->refresh(); // <---- key point is here (prevent form data from resending on refresh)
                } else {
                    Yii::$app->session->setFlash('error', "Please enter a valid code");
                    return $this->render('contact_url');
                }
            }
        }else{
//            Yii::$app->session->setFlash('error', "Please enter Code. ");
            return $this->render('contact_url');
        }
        return $this->render('contact_url');
    }


    public function actionViewContact($code){

        $this->layout='businesscard';
        return $this->render('contact_view', [
            'model' => Contact::find()->where(['code'=>$code])->one(),
        ]);
    }

    /**
     * Deletes an existing Contact model.
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
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
