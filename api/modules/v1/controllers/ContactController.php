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

        return $contacts;
    }

    public function actionView($code)
    {
        $contact = Contact::find()->where(['code'=>$code])->one();

        if(!empty($contact->imageUrl)) {
            $contact->imageUrl .= '?nocache=' . time();
        }

        if(empty($contact->address_type))
            $contact->address_type = 'Personal';

        if(empty($contact->country))
            $contact->country = 'United States';

        return $contact;
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
            $contact->code= strtolower($conJson['code']);
        }

        if(!isset($conJson['firstname']) || !isset($conJson['email'])){
            return [
                'error' => "firstname and email must be passed as POST params"
            ];
        }

        $contact->firstname= $conJson['firstname'];
        $contact->email= $conJson['email'];

        $contact->lastname= $conJson['lastname'] ?? '';
        $contact->company= $conJson['company'] ?? '';

        $contact->mobile_number= $conJson['mobile_number'] ?? '';
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
            return $contact->getErrors();
        }

        $leadId = $this->actionOnehashCreate($contact);

        // add lead id to db returned from onehash 

        $contact->lead_id = $leadId;
        if (!$contact->save(true, ['lead_id'])){
            return $contact->getErrors();
        }

        // add image in onehash
        $file_url='';
        if(!empty($conJson['image'])){
            $file_url = $this->actionOnehashImageUpdate($contact, $conJson['image']);
        }

        $response = $this->actionOnehashUpdate($contact, $conJson['image'], $file_url);

        return $contact;
    }

    function actionOnehashCreate($model)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
//        $authToken = 'token 2afc7871897ea0f:70a48aafae0007f';
         $authToken = 'token '.$model->createdBy->onehash_token;

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
             "phone"=> $model->mobile_number,
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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii:error("Contact create oneHash API curl error #:" . $err);
            return [
                'error' => "Contact create oneHash API curl error"
            ];
        } else {
            return json_decode($response)->data->name;
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
            return [
                'error' => "Contact with passed code could not be found"
            ];
        }

        if(!isset($conJson['code']) || !isset($conJson['firstname']) || !isset($conJson['email'])){
            return [
                'error' => "code, firstname and email must be passed as POST params"
            ];
        }

        $contact->code = $conJson['code'];
        $contact->firstname= $conJson['firstname'];
        $contact->email= $conJson['email'];

        $contact->lastname= $conJson['lastname'] ?? '';
        $contact->company= $conJson['company'] ?? '';

        $contact->mobile_number= $conJson['mobile_number'] ?? '';
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
            return $contact->getErrors();
        }
        
        // call onhash api
        $file_url='';
        if(!empty($conJson['image'])){
            $file_url = $this->actionOnehashImageUpdate($contact, $conJson['image']);
        }

        $response = $this->actionOnehashUpdate($contact, $conJson['image'], $file_url);

//        return $response;

        return $contact;
    }
    
    function actionOnehashUpdate($model, $image, $file_url)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
        // $authToken = 'token 2afc7871897ea0f:70a48aafae0007f'
        $authToken = 'token '.$model->createdBy->onehash_token;

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
            "address_line1"=> $model->address,
            "city"=> $model->city,
            "state"=> $model->state,
            "country"=> $model->country,
            "pincode"=> $model->pincode,
            "phone"=> $model->mobile_number,
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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii:error("Contact update oneHash API curl error #:" . $err);
            return [
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            return json_decode($response);
        }
    }

    function actionOnehashImageUpdate($model, $image)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
//         $authToken = 'token 2afc7871897ea0f:70a48aafae0007f';
        $authToken = 'token '.$model->createdBy->onehash_token;

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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii:error("Contact image update oneHash API curl error #:" . $err);
            return [
                'error' => "Contact image update oneHash API curl error"
            ];
        } else {
            return json_decode($response)->message->file_url;
        }
    }
}
