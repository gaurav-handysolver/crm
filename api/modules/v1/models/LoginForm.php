<?php
    namespace api\modules\v1\models;
    use common\models\User;


    /**
     * @property array|\common\models\User|mixed|null _user
     */
    class LoginForm extends \backend\models\LoginForm
    {
        /**
         * Finds user by [[username]]
         * @return User|null
         */
        public function getUser()
        {
            if ($this->user === false) {
                $this->user = User::findByUsername($this->username);
            }
            return $this->user;
        }
    }