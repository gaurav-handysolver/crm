<?php

    namespace api\modules\v1\controllers;
    use api\modules\v1\resources\Contact;
    use backend\models\OneHash;
    use common\components\awsParameterStore\AwsParameterStore;
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
        /*$activeData = new ActiveDataProvider([
            'query' => Contact::find()->orderBy('firstname'),
            'pagination' => false
        ]);

        return $activeData;*/

        $contacts = Contact::find()->orderBy('firstname')->all();

        foreach($contacts as $contact) {
            if(!empty($contact->imageUrl)) {
                $contact->imageUrl .= '?nocache=' . time();
            }
        }

        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contacts];
        // return $contacts;
    }

    public function actionView($code)
    {
        $contact = Contact::find()->where(['code'=>$code])->one();
        if($contact == null){

            $error = ['code' => 'Contact not found'];

            //Save the error in system log
            Yii::error($error,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Contact not found', 'payload' => $error];
            // return array('status'=> 404,'msg'=>'Contact not found');
        }
        if(!empty($contact->imageUrl)) {
            $contact->imageUrl .= '?nocache=' . time();
        }

        if(empty($contact->address_type))
            $contact->address_type = 'Personal';

        if(empty($contact->country))
            $contact->country = 'United States';

        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contact];
        // return $contact;
    }

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
            // return ['error' => "firstname and email must be passed as POST params"];
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
            // if($contact->getErrors()['lastname']){
            //     echo 'at email error';
            //     Yii::$app->response->statusCode = 500;
            // }
            // Yii::$app->response->statusCode = 422;
            foreach($contact->getErrors() as $key => $values) {
                $validationErrors[$key] = implode(',',$values);
            }
            //Save the error in system log
            Yii::error($validationErrors,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Validation failed', 'payload' => $contact->getErrors()?$validationErrors:''];
        }
//        return $contact;
//        die();

        //Check the OneHash setting is one/off
        $oneHashSettingStatus = OneHash::find()->where(['setting_name'=>OneHash::ONE_HASH_SETTING_NAME])->one();
        if($oneHashSettingStatus->is_enabled == OneHash::ONE_HASH_SETTING_OFF) {

            //Get the OneHashToken from AWS Parameter Store
            $aws = new AwsParameterStore();
            $result = $aws->actionGetParameter($contact->created_by);

            if($result['status']){
               $oneHashToken = $result['oneHashTokenValue'];
            }else{
                Yii::error($result,'AWS APIs');
                return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'AWS parameter error', 'payload' => $result['msg']];
//                return array("Error"=> $result['msg']);
            }

            $oneHashCreateContactResponse = $this->oneHashCreate($contact,$oneHashToken);
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
                $oneHashImageApiResponse = $this->oneHashImageUpdate($contact, $myImage,$oneHashToken);
                if($oneHashImageApiResponse['status']){
                    $file_url = $oneHashImageApiResponse['payload'];
                }else{
                    yii::error($oneHashImageApiResponse,'ONEHASH APIs');
                    return $oneHashImageApiResponse;
                }
            }

            $oneHashUpdateContact = $this->oneHashUpdate($contact, $myImage, $file_url,$oneHashToken);
            if(!$oneHashUpdateContact['status']){
                Yii::error($oneHashUpdateContact, 'ONEHASH APIs');
                return $oneHashUpdateContact;
            }

            //  for Contact Update  start
            $oneHashFindApiResponse = $this->findOneHashContact($contact->email, $oneHashToken);
            if($oneHashFindApiResponse['status']){
                $oneHashContactUpdateResponse = $this->oneHashContactUpdate($contact, $oneHashFindApiResponse['payload'], $file_url,$oneHashToken);
                if(!$oneHashContactUpdateResponse['status']){
                    Yii::error($oneHashContactUpdateResponse, 'ONEHASH APIs');
                    return $oneHashContactUpdateResponse;
                }
            }else{
                Yii::error($oneHashFindApiResponse, 'ONEHASH APIs');
                return $oneHashFindApiResponse;
            }
            //  for Contact Update  end
        }
        return ['status' => Contact::SUCCESS_STATUS_CODE , 'message' => 'Success', 'payload' => $contact];
        // return $contact;
    }

    function oneHashCreate($model,$oneHashToken)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
//        $authToken = 'token 2afc7871897ea0f:70a48aafae0007f';
         $authToken = 'token '.$oneHashToken;

        $curl = curl_init();
        $dataArray = array(
             "first_name"=> $model->firstname,
             "last_name"=> $model->lastname,
             "lead_name"=> $model->firstname." ".$model->lastname,
             "company_name"=> $model->company,
             "source"=> "NFC Dot App",
             "email_id"=> $model->email,
             "poll_guru"=> $model->pollguru,
             "buzz"=> $model->buzz,
             "learning_arcade"=> $model->learning_arcade,
             "training_pipeline"=> $model->training_pipeline,
             "leadership_edge"=> $model->leadership_edge,
             "notes"=> $model->notes,
             "address_title"=> $model->firstname." ".$model->address_type." address",
             "address_type"=> $model->address_type,
             "address_line1"=> empty($model->address) ? 'NA' : $model->address,
             "city"=> empty($model->city) ? 'NA' : $model->city,
             "state"=> empty($model->state) ? 'NA' : $model->state,
             "country"=> $model->country,
             "pincode"=> empty($model->pincode) ? 'NA' : $model->pincode,
             "phone"=> $model->phone_number,
             "job_title"=> $model->job_title,
             "mobile_no"=> $model->mobile_number,
             "website"=> $model->website,
        );

        $data = json_encode($dataArray);
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/resource/Lead",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "authorization: ${authToken}",
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        ));

        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact create oneHash API curl error #:" . $err);
            return [
                'error' => "Contact create oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Contact is created on onehash', 'payload' => json_decode($response)->data->name);
            }else{
                $functionName = 'OneHash-Create function';
                return array('status' => false, 'message' => 'contact is not created on onehash','payload' => 'Contact is not created on onehash with response code  '.$httpCode .' in '.$functionName. 'and the error is '.$response);

            }
        }
    }

    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

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
            // return ['error' => "Contact with passed code could not be found"];
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
            // return ['error' => "code, firstname and email must be passed as POST params"];
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

            foreach($contact->getErrors() as $key => $values) {
                $validationErrors[$key] = implode(',',$values);
            }
            //Save the error in system log
            Yii::error($validationErrors,'CRM APIs');

            return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'Validation error', 'payload' => $contact->getErrors()?$validationErrors:''];
            // return $contact->getErrors();
        }


        //Check the OneHash setting is one/off
        $oneHashSettingStatus = OneHash::find()->where(['setting_name'=>OneHash::ONE_HASH_SETTING_NAME])->one();
        if($oneHashSettingStatus->is_enabled == OneHash::ONE_HASH_SETTING_OFF) {

            //Get the OneHashToken from AWS Parameter Store
            $aws = new AwsParameterStore();
            $result = $aws->actionGetParameter($contact->created_by);

            if($result['status']){
                $oneHashToken = $result['oneHashTokenValue'];
            }else{
                Yii::error($result,'AWS APIs');
                return ['status' => Contact::ERROR_STATUS_CODE , 'message' => 'AWS parameter error', 'payload' => $result['msg']];
                // return array("Error"=> $result['msg']);
            }

            //Check contact present on OneHash or not
            $oneHashFindApiResponse =  $this->findOneHashContact($oldEmailId,$oneHashToken);
            if($oneHashFindApiResponse['status']){
                // call onehash api
                if (isset($conJson['image']) && !empty($conJson['image'])) {
                    $myImage = $conJson['image'];
                } else {
                    $myImage = '';
                }

                $file_url = '';
                if (!empty($conJson['image'])) {
                    $oneHashImageApiResponse = $this->oneHashImageUpdate($contact, $myImage,$oneHashToken);
                    if($oneHashImageApiResponse['status']){
                        $file_url = $oneHashImageApiResponse['payload'];
                    }else{
                        yii::error($oneHashImageApiResponse,'ONEHASH APIs');
                        return $oneHashImageApiResponse;
                    }
                }

                //Update the lead's information that is associated with contact
                $oneHashUpdateContact = $this->oneHashUpdate($contact, $myImage, $file_url,$oneHashToken);
                if(!$oneHashUpdateContact['status']){
                    Yii::error($oneHashUpdateContact, 'ONEHASH APIs');
                    return $oneHashUpdateContact;
                }

                //  for Address Update  start
                $oneHashFindAddressResponse = $this->findOneHashAddress($oldEmailId, $oneHashToken);
                if($oneHashFindAddressResponse['status']){
                    $oneHashAddressUpdateResponse = $this->oneHashAddressUpdate($contact, $oneHashFindAddressResponse['payload'],$oneHashToken);
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
                $oneHashContactUpdateResponse = $this->oneHashContactUpdate($contact, $oneHashFindApiResponse['payload'], $file_url,$oneHashToken);
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
        // return ['status'=>true,"data"=>$contact];
    }

    function oneHashUpdate($model, $image, $file_url,$oneHashToken)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
        // $authToken = 'token 2afc7871897ea0f:70a48aafae0007f'
        $authToken = 'token '.$oneHashToken;

        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $dataArray = array(
            "first_name"=> $model->firstname,
            "last_name"=> $model->lastname,
            "lead_name"=> $model->firstname." ".$model->lastname,
            "company_name"=> $model->company,
            "source"=> "NFC Dot App",
            "email_id"=> $model->email,
            "poll_guru"=> $model->pollguru,
            "buzz"=> $model->buzz,
            "learning_arcade"=> $model->learning_arcade,
            "training_pipeline"=> $model->training_pipeline,
            "leadership_edge"=> $model->leadership_edge,
            "notes"=> $model->notes,
            "address_title"=> $model->firstname." ".$model->address_type." address",
            "address_type"=> $model->address_type,
            "address_line1"=> $model->address ?: "NA",
            "city"=> $model->city ?: "NA",
            "state"=> $model->state ?: "NA",
            "country"=> $model->country,
            "pincode"=> $model->pincode ?: "NA",
            "phone"=> $model->phone_number,
            "job_title"=> $model->job_title,
            "mobile_no"=> $model->mobile_number,
            "website"=> $model->website,
        );

        if(!empty($image)){
            $dataArray['image'] = $file_url;
        }

        $data = json_encode($dataArray);
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/resource/Lead/".$model->lead_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "authorization: ${authToken}",
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        ));

        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            Yii::error("Lead update oneHash API curl error #:" . $err);
            return [
                'error' => "Lead update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Lead is updated on onehash', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Update function';
                return array('status' => false, 'message' => 'Lead is not updated on onehash','payload' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName.' and the error is '.$response);
            }
        }
    }

    function oneHashImageUpdate($model, $image,$oneHashToken)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
//         $authToken = 'token 2afc7871897ea0f:70a48aafae0007f';
        $authToken = 'token '.$oneHashToken;

        $curl = curl_init();
        $dataArray = array(
            "docname"=> $model->lead_id,
            "filename"=> $model->firstname . "_" . $model->lead_id . ".png",
            "filedata"=> $image,
            "from_form"=> "1",
            "docfield"=> "image"
        );
        $data = json_encode($dataArray);
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/method/uploadfile",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "authorization: ${authToken}",
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        ));

        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact image update oneHash API curl error #:" . $err);
            return [
                'error' => "Contact image update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Contact image updated on onehash', 'payload' => json_decode($response)->message->file_url);
            }else{
                $functionName = 'OneHash-Image-Update function';
                return array('status' => false, 'message' => 'Contact image is not updated on onehash','payload' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName.' and the error is '.$response);
            }
        }
    }

    //  find onehash address by email_id
    function findOneHashAddress($emailId,$authToken)
    {
        $authToken1 = 'token '.$authToken;
        $url = 'https://one.lookingforwardconsulting.com/api/resource/Address?filters=[["email_id","=",'.'"'.$emailId.'"'.']]';
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL =>$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken1}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            Yii::error("Contact-Address Getting oneHash API curl error #:" . $err);
            return [
                'error' => "Contact-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Contact address is found', 'payload' => json_decode($response)->data[0]->name);
            }else{
                $functionName = 'Find-OneHash-Address function';
                return array('status' => false, 'message' => 'Contact address is not found','payload' => 'Contact address is not found with response code '.$httpCode .' in '.$functionName.' and the error is '.$response);

            }
        }
    }

    //  update onehash address by address title
    function oneHashAddressUpdate($model,$address_title,$oneHashToken)
    {
        $authToken = 'token '.$oneHashToken;
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $dataArray = array(
            "address_title"=> $model->firstname." ".$model->address_type ?? "Personal" ." address",
            "address_type"=> $model->address_type ?: "Personal",
            "address_line1"=> $model->address ?: "NA",
            "city"=> $model->city ?: "NA",
            "state"=> $model->state ?: "NA",
            "country"=> $model->country ?: "United States",
            "pincode"=> $model->pincode ?: "NA",
            "phone"=> $model->phone_number,
            "email_id" => $model->email

        );
        $data = json_encode($dataArray);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/resource/Address/".$address_title,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact-Address update oneHash API curl error #:" . $err);
            return [
                'error' => "Contact-Address update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Contact address is updated on onehash', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Address-Update function';
                return array('status' => false, 'message' => 'Contact address is not updated on onehash','payload' => 'Contact address is not updated on onehash with response code  '.$httpCode .' in '.$functionName. ' and the error is '. $response);

            }
        }
    }

    //  find onehash Contact by email_id
    function findOneHashContact($emailId,$authToken)
    {
        $authToken1 = 'token '.$authToken;
        $url = 'https://one.lookingforwardconsulting.com/api/resource/Contact?filters=[["email_id","=",'.'"'.$emailId.'"'.']]';
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL =>$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken1}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        //Getting the response of http code of onehash API
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $err = curl_error($curl);


        curl_close($curl);
        if ($err) {
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status' => false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                $data = json_decode($response)->data;
                if(isset($data) && !empty($data)){
                    $contactName = $data[0]->name;
                    return array('status'=>true,'message' => 'Contact found on onehash', 'payload' => $contactName);
                }else{
                    return array('status'=>false,'message'=>'Contact not found on onehash','payload' => 'Contact not found');
                }
            }else{
                $functionName = 'Find-OneHash-Contact function';
                return array('status' => false, 'message' => 'Contact not found on onehash','payload' => 'Onehash API Error with response code with response code  '.$httpCode .' in '.$functionName.' and the error is '.$response);
            }
        }
    }

    //  update onehash Contact by contact title
    function oneHashContactUpdate($model,$contact_title,$file_url,$oneHashToken)
    {
        $authToken = 'token '.$oneHashToken;
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $dataArray = array(
            "first_name"=> $model->firstname,
            "last_name"=> $model->lastname,
            "company_name"=> $model->company,
            "image"=>$file_url,
            "email_ids" => [
                [
                    "email_id" => $model->email,
                    "is_primary" => 1
                ]
            ],
            "phone_nos" => [
                [
                    "phone" => $model->phone_number?$model->phone_number:0,
                    "is_primary_phone" => 1,
                    "is_primary_mobile_no"=> 0
                ],
                [
                    "phone" => $model->mobile_number?$model->mobile_number:0,
                    "is_primary_phone" => 0,
                    "is_primary_mobile_no" => 1
                ]
            ]
        );

        $data = json_encode($dataArray);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/resource/Contact/".$contact_title,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        //Getting the response code of curl request
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact update oneHash API curl error #:" . $err);
            return [
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'message' => 'Contact is updated on onehash', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Contact-Update function';
                return array('status' => false, 'message' => 'Contact is not updated on onehash','payload' => 'Contact is not updated with response code '.$httpCode .' in '.$functionName.' and the error is ' .$response);

            }
        }
    }

}
