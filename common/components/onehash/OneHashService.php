<?php

namespace common\components\onehash;

use Yii;

class OneHashService
{
    function actionFindOnehashContact($emailId,$authToken)
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
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            $result = ["status"=>true, "data"=>json_decode($response)->data[0]->name];
            return $result;
        }
    }

    //Update contact image
    function actionOnehashImageUpdate($model)
    {
        $image = file_get_contents($model->imageUrl);
        $image = base64_encode($image);

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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact image update oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Contact image update oneHash API curl error"
            ];
        } else {
            return json_decode($response)->message->file_url;
        }
    }

    //  update onehash Contact by contact title
    function actionOnehashContactUpdate($model,$contact_title,$file_url)
    {
        $authToken = 'token '.$model->createdBy->onehash_token;
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $dataArray = array(
            "first_name"=> $model->firstname,
            "last_name"=> $model->lastname,
            "company_name"=> $model->company,
            "image"=>$file_url,
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
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            Yii::error("Contact update oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            $result = ["status"=>true, "data"=>json_decode($response)];
            return $result;
        }
    }

    //Lead update
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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact update oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Contact update oneHash API curl error"
            ];
        } else {
            $result = ["status"=>true, "data"=>json_decode($response)];
            return $result;
        }
    }

    //  find onehash address by email_id
    function actionFindOnehashAddress($emailId,$authToken)
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
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            $result = ["status"=>true, "data"=>json_decode($response)->data[0]->name];
            return $result;
        }
    }

    //  update onehash address by address title
    function actionOnehashAddressUpdate($model,$address_title)
    {
        $authToken = 'token '.$model->createdBy->onehash_token;
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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Contact-Address update oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Contact-Address update oneHash API curl error"
            ];
        } else {
            $result = ["status"=>true, "data"=>json_decode($response)];
            return $result;
        }
    }
}