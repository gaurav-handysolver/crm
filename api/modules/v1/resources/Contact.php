<?php
    /**
     * Created by PhpStorm.
     * User: cyberains
     * Date: 16-07-2021
     * Time: 10:28
     */

namespace api\modules\v1\resources;

class Contact extends \common\models\Contact
{
    CONST ERROR_STATUS_CODE = 0;
    CONST SUCCESS_STATUS_CODE = 1;

    CONST SUCCESS_RESPONSE = 200;
    CONST REDIRECT_RESPONSE = 301;


    public function fields()
    {
        return ['id', 'firstname','lastname','email','job_title','mobile_number','phone_number','company','website','notes','address','imageUrl','birthday','pollguru','buzz','learning_arcade','training_pipeline','leadership_edge','created_by','code','city','state','country','address_type','pincode','lead_id'];
    }
}