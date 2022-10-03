<?php

namespace common\components\awsParameterStore;

use Yii;

class AwsParameterStore
{
    public function actionGetParameter($keyName){
        $url = "https://vdgcmuqvck.execute-api.us-east-1.amazonaws.com/default/fetchParam?key=".$keyName;
        $awsApiKey = env("AWS_API_KEY");
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl,array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "x-api-key: ${awsApiKey}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $result = json_decode($response,true);

        //If any error come from AWS

        if ($err) {
            Yii::error("AWS Parameter Error #:" . $err);
            return [
                'error' => "AWS Parameter Error"
            ];
        }

        if(isset($result["Name"])){
            return  array("status"=> true,"oneHashTokenValue"=>$result["Value"],"msg"=>"Data Found");
        }else{
            return array("status"=>false,"oneHashTokenValue"=>null,"msg"=>$response);
        }

    }

    public function actionStoreParameter(){
        $url = "https://vdgcmuqvck.execute-api.us-east-1.amazonaws.com/default/pushParam";
        $awsApiKey = env("AWS_API_KEY");

        $post = file_get_contents("php://input");
        $postData = (array)  json_decode($post);

        if(!isset($postData['key']) || !isset($postData['value'])){
            return array("Status"=>false, "msg"=>"Key and value are required");
        }

        $curl = curl_init();
        $data = array("key"=>$postData['key'],"value" => $postData['value']);
        $data = json_encode($data); //Convert data into json to pass in api request

       curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
              "x-api-key: ${awsApiKey}",
              "cache-control: no-cache",
              "content-type: application/json",
        ),
       ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Yii::error("Parameter not store on AWS #:" . $err);
            return [
                'error' => "Parameter not store on AWS"
            ];
        }
        return $response;

    }
}