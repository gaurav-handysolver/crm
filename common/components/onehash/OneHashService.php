<?php

namespace common\components\onehash;

use api\modules\v1\resources\Contact;
use Yii;

class OneHashService
{
    public static function findOneHashContact($emailId,$authToken)
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
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                $data = json_decode($response)->data;
                if(isset($data) && !empty($data)){
                    $contactName = $data[0]->name;
                    return array('status'=>true,'msg' => 'Contact found', 'payload' => $contactName);
                }else{
                    return array('status'=>false,'msg'=>'Contact not found','payload' => 'Contact not found');
                }
            }else{
                $functionName = 'Find-OneHash-Contact function';
                return array('status' => false, 'msg' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName,'payload' => $response);
            }
        }
    }

    //Upload image
    public static function OneHashImageUpdate($model,$oneHashToken)
    {
        if(isset($model->imageUrl)){
            $image = file_get_contents($model->imageUrl);
            $image = base64_encode($image);
        }else{
            $image = '';
        }

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

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/method/uploadfile",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_POST => 1,
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
                'status'=>false,
                'error' => "Contact image update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'msg' => 'Contact image updated on onehash', 'payload' => json_decode($response)->message->file_url);
            }else{
                $functionName = 'OneHash-Image-Update function';
                return array('status' => false, 'msg' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName,'payload' => $response);
            }
        }
    }

    //  update onehash Contact by contact title
    public static function OneHashContactUpdate($model,$contact_title,$file_url,$oneHashToken)
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
                    "phone" => $model->phone_number,
                    "is_primary_phone" => 1,
                    "is_primary_mobile_no"=> 0
                ],
                [
                    "phone" => $model->mobile_number,
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
                'status'=>false,
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'msg' => 'Contact is updated', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Contact-Update function';
                return array('status' => false, 'msg' => 'Contact is not updated with response code '.$httpCode .' in '.$functionName,'payload' => $response);

            }
        }
    }

    //Lead update
    public static function OneHashLeadUpdate($model, $image, $file_url,$oneHashToken)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
        // $authToken = 'token 2afc7871897ea0f:70a48aafae0007f'
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
            "address_line1"=> $model->address ?: "NA",
            "city"=> $model->city ?: "NA",
            "state"=> $model->state ?: "NA",
            "country"=> $model->country,
            "pincode"=> $model->pincode ?: "NA",
            "phone"=> $model->phone_number,
            "mobile_no"=> $model->mobile_number,
            "website"=> $model->website,
            "job_title" => $model->job_title
        );

        if(!empty($image)){
            $dataArray['image'] = $file_url;
        }

        $data = json_encode($dataArray);

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

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
            Yii::error("Contact update oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'msg' => 'Contact is updated on onehash', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Update function';
                return array('status' => false, 'msg' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName,'payload' => $response);
            }
        }
    }

    //  find onehash address by email_id
    public static function findOneHashAddress($emailId,$authToken)
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
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'msg' => 'Lead address is found', 'payload' => json_decode($response)->data[0]->name);
            }else{
                $functionName = 'Find-OneHash-Address function';
                return array('status' => false, 'msg' => 'Lead address is not found with response code '.$httpCode .' in '.$functionName,'payload' => $response);

            }
        }
    }

    //  update onehash address by address title
    public static function OneHashAddressUpdate($model,$address_title,$oneHashToken)
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
                'status'=>false,
                'error' => "Contact-Address update oneHash API curl error"
            ];
        } else {
            if($httpCode == Contact::SUCCESS_RESPONSE){
                return array('status' => true, 'msg' => 'Contact address is updated on onehash', 'payload' => json_decode($response));
            }else{
                $functionName = 'OneHash-Address-Update function';
                return array('status' => false, 'msg' => 'Contact address is not updated on onehash with response code  '.$httpCode .' in '.$functionName,'payload' => $response);

            }
        }
    }

    //Lead create
    public static function OneHashLeadCreate($model,$oneHashToken)
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
            "phone"=> "$model->phone_number",
            "job_title"=> $model->job_title,
            "mobile_no"=> "$model->mobile_number",
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

    //Upload image on lead doctype
    public static function OneHashUpdateImageOnLead($model, $image, $file_url,$oneHashToken)
    {
        // $leadId = 'CRM-LEAD-2022-00078'
        // $authToken = 'token 2afc7871897ea0f:70a48aafae0007f'
        $authToken = 'token '.$oneHashToken;

        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

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

    //Upload image on contact doctype
    public static function OneHashUpdateImageOnContact($model,$contact_title,$file_url,$oneHashToken)
    {
        $authToken = 'token '.$oneHashToken;
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $dataArray = array(
            "image"=>$file_url,
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