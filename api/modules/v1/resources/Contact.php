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
    public function fields()
    {
        return ['id', 'firstname','lastname','email','mobile_number','company','website','notes','address','imageUrl','birthday','pollguru','buzz','learning_arcade','training_pipeline','leadership_edge','created_by','code','city','state','country','address_type','pincode'];
    }
}