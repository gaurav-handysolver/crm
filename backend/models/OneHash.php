<?php

namespace backend\models;

use yii\base\Exception;
use yii\base\Model;
use Yii;
use yii\db\ActiveRecord;

class OneHash extends ActiveRecord
{
    const ONE_HASH_SETTING_NAME = "OneHash";
    const ONE_HASH_SETTING_ON = 1;
    const ONE_HASH_SETTING_OFF = 0;
    /**
     * @return string
     * @property int $id
     * @property int $is_enabled
     * @property string|null $setting_name
     */

    public static function tableName()
    {
        return '{{%onehash_setting}}';
    }

    public function rules(){
     return[
         [['is_enabled'], 'integer'],
         [['is_enabled','setting_name'], 'safe']


     ];
    }

    public function attributeLabels()
    {
        return[
            "is_enabled" => yii::t('backend',"Disconnect OneHash")
        ];
    }

}