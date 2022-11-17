<?php

    namespace api\modules\v1\controllers;
    use api\modules\v1\resources\Contact;
    use backend\models\OneHash;
    use common\components\awsParameterStore\AwsParameterStore;
    use common\components\onehash\OneHashService;
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

class ContactController extends BaseController
{
    public $modelClass = Contact::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $contacts = Contact::find()->orderBy('firstname')->all();

        foreach($contacts as $contact) {
            if(!empty($contact->imageUrl)) {
                $contact->imageUrl .= '?nocache=' . time();
            }
        }

        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contacts];
    }

    public function actionView($code)
    {
        $contact = Contact::find()->where(['code'=>$code])->one();
        if($contact == null){

            $error = ['code' => 'Contact not found'];

            //Save the error in system log
            Yii::error($error,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Contact not found', 'payload' => $error];
        }
        if(!empty($contact->imageUrl)) {
            $contact->imageUrl .= '?nocache=' . time();
        }

        if(empty($contact->address_type))
            $contact->address_type = 'Personal';

        if(empty($contact->country))
            $contact->country = 'United States';

        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contact];
    }

    //In create action, we have to just call the lead create API and the data will automatically add in contact doctype and address doctype
    public function actionCreate()
    {
        $code = strtolower(substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',ceil(6/strlen($x)))),1,6));

//        ini_set("memory_limit","50M");
        $post = file_get_contents("php://input");
        $conJson = (array) \json_decode($post);

        $contact= new Contact();
        if($conJson['code'] == null){
            $contact->code= $code;
        }else{
            $contact->code= strtolower(trim($conJson['code']));
        }

        if(!isset($conJson['firstname']) || !isset($conJson['email'])){
            Yii::$app->response->statusCode = 422;

            if(!isset($conJson['firstname']) && isset($conJson['email'])){
                $result = ['firstname' => 'firstname is required'];
            }elseif (isset($conJson['firstname']) && !isset($conJson['email'])){
                $result = ['email' => 'email is required'];
            }else{
                $result = ['firstname' => 'firstname is required', 'email' => 'email is required'];
            }

            //Save the error in system log
            Yii::error($result,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Missing required fields', 'payload' => $result];
        }

        $contact->firstname= $conJson['firstname'];
        $contact->email= $conJson['email'];

        $contact->lastname= $conJson['lastname'] ?? '';
        $contact->company= $conJson['company'] ?? '';

        $contact->mobile_number= $conJson['mobile_number'] ?? '';
        $contact->phone_number= $conJson['phone_number'] ?? '';
        $contact->job_title= $conJson['job_title'] ?? '';
        $contact->website= $conJson['website'] ?? '';
        $contact->notes= $conJson['notes'] ?? '';
        $contact->address= $conJson['address'] ?? '';
        $contact->pollguru= $conJson['pollguru'] ?? '';
        $contact->buzz= $conJson['buzz'] ?? '';
        $contact->learning_arcade= $conJson['learning_arcade'] ?? '';
        $contact->training_pipeline= $conJson['training_pipeline'] ?? '';
        $contact->leadership_edge= $conJson['leadership_edge'] ?? '';
        $contact->city= $conJson['city'] ?? '';
        $contact->state= $conJson['state'] ?? '';
        $contact->country= $conJson['country'] ?? 'United States';
        $contact->address_type= $conJson['address_type'] ?? 'Personal';
        $contact->pincode= $conJson['pincode'] ?? '';
        $contact->lead_id= $conJson['lead_id'] ?? '';
        $contact->created_by= $conJson['created_by'] ?? '';

        if(isset($conJson['image'])){
            $realImage = base64_decode($conJson['image']);
        }

        if (!empty($realImage) ){
            $myfile = fopen(Yii::getAlias('@storage').'/web/source'.'/'.$contact->code.'.jpeg',"a") or die("Unable to open location for log file !");
            fwrite($myfile, $realImage);
            fclose($myfile);
            $contact->imageUrl = Url::to('/storage/web/source'.'/'.$contact->code.'.jpeg',true);
        }

        if (!$contact->save()){
            $errorDescription = '';
            foreach($contact->getErrors() as $key => $values) {
                $validationErrors[$key] = implode(',',$values);
                $errorDescription = $errorDescription . implode(',' , $values);
            }
            //Save the error in system log
            Yii::error($validationErrors,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => str_replace('\\', ' ', $errorDescription), 'payload' => $contact->getErrors()?$validationErrors:''];
        }

        //Check the OneHash setting is one/off
        $oneHashSettingStatus = OneHash::find()->where(['setting_name'=>OneHash::ONE_HASH_SETTING_NAME])->one();
        if($oneHashSettingStatus->is_enabled == OneHash::ONE_HASH_SETTING_OFF) {

            //Get the OneHashToken from AWS Parameter Store
            $aws = new AwsParameterStore();
            $result = $aws->actionGetParameter(\common\models\Contact::USER_ACCOUNT_FOR_TESTING);

            if($result['status']){
               $oneHashToken = $result['oneHashTokenValue'];
            }else{
                Yii::error($result,'AWS APIs');
                return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'AWS parameter error', 'payload' => $result['msg']];
//                return array("Error"=> $result['msg']);
            }

            $oneHashCreateContactResponse = OneHashService::OneHashLeadCreate($contact,$oneHashToken);
            if($oneHashCreateContactResponse['status']){
                // add lead id to db returned from onehash
                $contact->lead_id = $oneHashCreateContactResponse['payload'];
            }else{
                Yii::error($oneHashCreateContactResponse,'ONEHASH APIs');
                return $oneHashCreateContactResponse;
            }


            if (!$contact->save(true, ['lead_id'])) {
                return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Some error occurred', 'payload' => $contact->getErrors()];
                // return $contact->getErrors();
            }

            // add image in onehash
            if (isset($conJson['image']) && !empty($conJson['image'])) {
                $myImage = $conJson['image'];
            } else {
                $myImage = '';
            }
            $file_url = '';
            if (!empty($conJson['image'])) {
                $oneHashImageApiResponse = OneHashService::OneHashImageUpdate($contact,$oneHashToken);
                if($oneHashImageApiResponse['status']){
                    $file_url = $oneHashImageApiResponse['payload'];

                    //Upload image on lead doctype
                    $oneHashUpdateImageOnLeadResponse = OneHashService::OneHashUpdateImageOnLead($contact, $myImage, $file_url,$oneHashToken);
                    if(!$oneHashUpdateImageOnLeadResponse['status']){
                        Yii::error($oneHashUpdateImageOnLeadResponse, 'ONEHASH APIs');
                        return $oneHashUpdateImageOnLeadResponse;
                    }

                    //Upload image on contact doctype
                    $contactTitle = $contact->firstname. ' '.$contact->lastname;
                    $oneHashContactUpdateResponse = OneHashService::OneHashUpdateImageOnContact($contact, $contactTitle, $file_url,$oneHashToken);
                    if(!$oneHashContactUpdateResponse['status']){
                        Yii::error($oneHashContactUpdateResponse, 'ONEHASH APIs');
                        return $oneHashContactUpdateResponse;
                    }

                }else{
                    yii::error($oneHashImageApiResponse,'ONEHASH APIs');
                    return $oneHashImageApiResponse;
                }
            }
            //  for Contact Update  end
        }
        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contact];
        // return $contact;
    }

    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    //In update action, we have to lead, contact and address APIs. data will not automatically update in contact doctype and address doctype
    public function actionUpdate($code)
    {

        $post = file_get_contents("php://input");
        $conJson = (array) \json_decode($post);

        $contact =Contact::find()->where(['code'=>$code])->one();

        if(!isset($contact)) {
            $error = ['code' => 'Contact with passed code could not be found'];

            //Save the error in system log
            Yii::error($error,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Contact not found', 'payload' => $error];
        }

        if(!isset($conJson['code']) || !isset($conJson['firstname']) || !isset($conJson['email'])){

            if(!isset($conJson['code']) && isset($conJson['firstname']) && isset($conJson['email'])){
                $result = ['code' => 'code is required'];
            }elseif (isset($conJson['code']) && !isset($conJson['firstname']) && isset($conJson['email'])){
                $result = ['firstname' => 'firstname is required'];
            }elseif (isset($conJson['code']) && isset($conJson['firstname']) && !isset($conJson['email'])){
                $result = ['email' => 'email is required'];
            }elseif (!isset($conJson['code']) && !isset($conJson['firstname']) && isset($conJson['email'])){
                $result = ['code' => 'code is required','firstname' => 'firstname is required'];
            }elseif (isset($conJson['code']) && !isset($conJson['firstname']) && !isset($conJson['email'])){
                $result = ['firstname' => 'firstname is required','email' => 'email is required',];
            }elseif (!isset($conJson['code']) && isset($conJson['firstname']) && !isset($conJson['email'])){
                $result = ['code' => 'code is required','email' => 'email is required',];
            }else{
                $result = ['code' => 'code is required','firstname' => 'firstname is required', 'email' => 'email is required'];
            }

            //Save the error in system log
            Yii::error($result,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Missing required fields', 'payload' => $result];
        }

        //To Update the email Id on onehash platform we have to use old email id for finding the user
        if(isset($contact)){
            $oldEmailId = $contact->email;
        }

        $contact->code = strtolower(trim($conJson['code']));
        $contact->firstname= $conJson['firstname'];
        $contact->email= $conJson['email'];

        $contact->lastname= $conJson['lastname'] ?? '';
        $contact->company= $conJson['company'] ?? '';

        $contact->mobile_number= $conJson['mobile_number'] ?? '';
        $contact->phone_number= $conJson['phone_number'] ?? '';
        $contact->job_title= $conJson['job_title'] ?? '';
        $contact->website= $conJson['website'] ?? '';
        $contact->notes= $conJson['notes'] ?? '';
        $contact->address= $conJson['address'] ?? '';
        $contact->pollguru= $conJson['pollguru'] ?? '';
        $contact->buzz= $conJson['buzz'] ?? '';
        $contact->learning_arcade= $conJson['learning_arcade'] ?? '';
        $contact->training_pipeline= $conJson['training_pipeline'] ?? '';
        $contact->leadership_edge= $conJson['leadership_edge'] ?? '';
        $contact->city= $conJson['city'] ?? '';
        $contact->state= $conJson['state'] ?? '';
        $contact->country= $conJson['country'] ?? '';
        $contact->address_type= $conJson['address_type'] ?? '';
        $contact->pincode= $conJson['pincode'] ?? '';

        if(isset($conJson['image'])){
            $realImage = base64_decode($conJson['image']);
        }

        if (!empty($realImage) ){
            $contact->imageUrl = "";
            $myfile = fopen(Yii::getAlias('@storage').'/web/source'.'/'.$contact->code.'.jpeg',"w+") or die("Unable to open location for log file !");
//            $txt = "Log details goes here ...";
            fwrite($myfile, $realImage);
            fclose($myfile);
            $contact->imageUrl = Url::to('/storage/web/source'.'/'.$contact->code.'.jpeg',true);
        }else{
            $contact->imageUrl = null;

        }


        if (!$contact->save()){
            $errorDescription = '';
            foreach($contact->getErrors() as $key => $values) {
                $validationErrors[$key] = implode(',',$values);
                $errorDescription = $errorDescription . implode(',' ,$values);
            }
            //Save the error in system log
            Yii::error($validationErrors,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => str_replace('\\', ' ', $errorDescription), 'payload' => $contact->getErrors()?$validationErrors:''];
        }


        //Check the OneHash setting is one/off
        $oneHashSettingStatus = OneHash::find()->where(['setting_name'=>OneHash::ONE_HASH_SETTING_NAME])->one();
        if($oneHashSettingStatus->is_enabled == OneHash::ONE_HASH_SETTING_OFF) {

            //Get the OneHashToken from AWS Parameter Store
            $aws = new AwsParameterStore();
            $result = $aws->actionGetParameter(\common\models\Contact::USER_ACCOUNT_FOR_TESTING);

            if($result['status']){
                $oneHashToken = $result['oneHashTokenValue'];
            }else{
                Yii::error($result,'AWS APIs');
                return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'AWS parameter error', 'payload' => $result['msg']];
            }

            //Check contact present on OneHash or not
            $oneHashFindApiResponse =  OneHashService::findOneHashContact($oldEmailId,$oneHashToken);
            if($oneHashFindApiResponse['status']){
                // call onehash api
                if (isset($conJson['image']) && !empty($conJson['image'])) {
                    $myImage = $conJson['image'];
                } else {
                    $myImage = '';
                }

                $file_url = '';
                if (!empty($conJson['image'])) {
                    $oneHashImageApiResponse = OneHashService::OneHashImageUpdate($contact,$oneHashToken);
                    if($oneHashImageApiResponse['status']){
                        $file_url = $oneHashImageApiResponse['payload'];
                    }else{
                        yii::error($oneHashImageApiResponse,'ONEHASH APIs');
                        return $oneHashImageApiResponse;
                    }
                }

                //Update the lead's information that is associated with contact
                $oneHashUpdateContact = OneHashService::OneHashLeadUpdate($contact, $myImage, $file_url,$oneHashToken);
                if(!$oneHashUpdateContact['status']){
                    Yii::error($oneHashUpdateContact, 'ONEHASH APIs');
                    return $oneHashUpdateContact;
                }

                //  for Address Update  start
                $oneHashFindAddressResponse = OneHashService::findOneHashAddress($oldEmailId, $oneHashToken);
                if($oneHashFindAddressResponse['status']){
                    $oneHashAddressUpdateResponse = OneHashService::OneHashAddressUpdate($contact, $oneHashFindAddressResponse['payload'],$oneHashToken);
                    // The response of contact address update's api is not using further so, we only target the error situation.
                    if(!$oneHashAddressUpdateResponse['status']){
                        Yii::error($oneHashAddressUpdateResponse,'ONEHASH APIs');
                        return $oneHashAddressUpdateResponse;
                    }
                }else{
                    Yii::error($oneHashFindAddressResponse, 'ONEHASH APIs');
                    return $oneHashFindAddressResponse;
                }
                //  for Address Update  end


                //  for Contact Update  start
                $oneHashContactUpdateResponse = OneHashService::OneHashContactUpdate($contact, $oneHashFindApiResponse['payload'], $file_url,$oneHashToken);
                if(!$oneHashContactUpdateResponse['status']){
                    Yii::error($oneHashContactUpdateResponse, 'ONEHASH APIs');
                    return $oneHashContactUpdateResponse;
                }
                //  for Contact Update  end
            }else{
                Yii::error($oneHashFindApiResponse, 'ONEHASH APIs');
                return $oneHashFindApiResponse;
            }
        }
        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contact];
    }

}
