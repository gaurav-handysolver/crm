<?php
    /**
     * Created by PhpStorm.
     * User: cyberains
     * Date: 08-07-2021
     * Time: 11:07
     */


namespace app\api\modules\v1\models;


use common\models\User;

/**
 * @property-read null|\common\models\User $user
 */
class LoginForm extends User
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

    public function login() { }
}