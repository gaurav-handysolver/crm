<?php

namespace backend\controllers;

use Yii;
use common\models\Contact;

/**
 * Site controller
 */
class SiteController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->layout = Yii::$app->user->isGuest || !Yii::$app->user->can('loginToBackend') ? 'base' : 'common';

        return parent::beforeAction($action);
    }

    public function actionLeadReport()
    {
        ob_start();

        $allContacts = Contact::find()->all();
        $totalRecord = 0;
        foreach ($allContacts as $key => $allContact) {
            print_r('Lfc crm id--> '.$allContact->id);
            echo "<br>";
            if(empty($allContact->lead_id)){
                echo $allContact->email." - this user is not present in oneHash Lead";
            } else{
                $oneHashLead = $this->getDataFromOneHash($allContact->lead_id);
                if(isset($oneHashLead->data)){
                    print_r('OneHash id--> '.$allContact->lead_id);
                    echo "<br>";

                    echo "<b>Name:  </b>";
                    if($allContact->firstname && ($allContact->firstname.' '.$allContact->lastname == $oneHashLead->data->lead_name)){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->firstname)? $allContact->firstname.' '.$allContact->lastname : 'no data'); echo '|'; print_r(isset($oneHashLead->data->lead_name)? $oneHashLead->data->lead_name : 'no data'); echo "<br>";
                    }

                    echo "<b>Email:  </b>";
                    if($allContact->email == $oneHashLead->data->email_id){
                        echo 'Same';
                        echo "<br>";
                    } else{
                        print_r($allContact->email); echo '       |       '; print_r($oneHashLead->data->email_id);
                        echo "<br>";
                    }

                    echo "<b>Website:  </b>";
                    if($allContact->website == $oneHashLead->data->website){
                        echo "Same";
                        echo "<br>";
                    } else{
                        print_r($allContact->website); echo '       |       '; print_r($oneHashLead->data->website);
                        echo "<br>";
                    }

                    echo "<b>City:  </b>";
                    if($allContact->city == $oneHashLead->data->city){
                        echo "Same";
                        echo "<br>";
                    } else{
                        print_r($allContact->city); echo '       |       '; print_r($oneHashLead->data->city);
                        echo "<br>";
                    }
                    
                    echo "<b>State:  </b>";
                    if($allContact->state == $oneHashLead->data->state){
                        echo "Same";
                        echo "<br>";
                    } else{
                        print_r($allContact->state); echo '       |       '; print_r($oneHashLead->data->state);
                        echo "<br>";
                    }
                    
                    echo "<b>Country:  </b>";
                    if($allContact->country == $oneHashLead->data->country){
                        echo "Same";
                        echo "<br>";
                    } else{
                        print_r($allContact->country); echo '       |       '; print_r($oneHashLead->data->country);
                        echo "<br>";
                    }

                    echo "<b>Pin code:  </b>";
                    if($allContact->pincode && $allContact->pincode == $oneHashLead->data->pincode){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->pincode)? $allContact->pincode : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->pincode)? $oneHashLead->data->pincode : 'no data'); echo "<br>";
                    }
                    
                    echo "<b>Job Title:  </b>";
                    if($allContact->job_title && $allContact->job_title == $oneHashLead->data->job_title){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->job_title)? $allContact->job_title : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->job_title)? $oneHashLead->data->job_title : 'no data'); echo "<br>";
                    }

                    echo "<b>Phone:  </b>";
                    if($allContact->phone_number && $allContact->phone_number == $oneHashLead->data->phone){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->phone_number)? $allContact->phone_number : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->phone)? $oneHashLead->data->phone : 'no data'); echo "<br>";
                    }

                    echo "<b>Mobile Number:  </b>";
                    if($allContact->mobile_number && $allContact->mobile_number == $oneHashLead->data->mobile_no){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->mobile_number)? $allContact->mobile_number : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->mobile_no)? $oneHashLead->data->mobile_no : 'no data'); echo "<br>";
                    }

                    echo "<b>Poll Guru:  </b>";
                    if($allContact->pollguru && $allContact->pollguru == $oneHashLead->data->poll_guru){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->pollguru)? $allContact->pollguru : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->poll_guru)? $oneHashLead->data->poll_guru : 'no data'); echo "<br>";
                    }

                    echo "<b>Buzz:  </b>";
                    if($allContact->buzz && $allContact->buzz == $oneHashLead->data->buzz){
                        echo "Same"; echo "<br>";
                    } else{
                        print_r(isset($allContact->buzz)? $allContact->buzz : 'no data'); echo '       |       '; print_r(isset($oneHashLead->data->buzz)? $oneHashLead->data->buzz : 'no data'); echo "<br>";
                    }

                    
                } else{
                    echo 'no data in Onehash crm';
                }
            }

            // if()
            echo "<br>";
            echo "---------------------------";
            echo "<br>";
            // die();
            $totalRecord++;
        }
        print_r('Total Records fetched--> '.$totalRecord);
        ob_end_flush();

    }

    public function getDataFromOneHash($lead_id){
        $authToken = 'token 2afc7871897ea0f:70a48aafae0007f';
        // $authToken = 'token '.$oneHashToken;
        $curl = curl_init();
        

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://one.lookingforwardconsulting.com/api/resource/Lead/".$lead_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
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
                print_r('error on one hash get api');
                print_r($err);
            } else {
                // echo 'fffhereupp';
                // print_r($response);
                // die();
                return json_decode($response);
            }
    }
}
