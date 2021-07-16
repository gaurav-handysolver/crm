<?php

    namespace api\modules\v1\controllers;
    use api\modules\v1\resources\Contact;
    use yii\rest\ActiveController;

    /**
     * Created by PhpStorm.
     * User: cyberains
     * Date: 16-07-2021
     * Time: 10:26
     */

class ContactController extends ActiveController{
    public $modelClass = Contact::class;
}