<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;

class PagesController extends Controller
{

    /**
     * Support & Privacy Policy Page.
     * @return mixed
     */
    public function actionSupport()
    {
      $this->layout=false;
      return $this->render('support');
    }

    public function actionPrivacyPolicy()
    {
      $this->layout=false;
      return $this->render('privacy-policy');
    }

}
