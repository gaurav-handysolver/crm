<?php

namespace common\models;

use common\models\query\ContactQuery;
use JeroenDesloovere\VCard\VCard;
use Yii;

/**
 * This is the model class for table "tbl_contact".
 *
 * @property int $id
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $email
 * @property string|null $company
 * @property string|null $website
 * @property string|null $notes
 * @property string|null $address
 * @property string|null $imageUrl
 * @property string|null $mobile_number
 * @property string|null $birthday
 * @property int|null $pollguru
 * @property int|null $buzz
 * @property int|null $learning_arcade
 * @property int|null $training_pipeline
 * @property int|null $leadership_edge
 * @property int|null $created_by
 * @property int|null $code
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $address_type
 * @property string $pincode
 * @property string $updated_at
 * @property string $created_at
 *
 * @property User $createdBy
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email','code'], 'unique'],
            [['email'], 'email'],
            [['firstname','email','code'],'required'],
            [['birthday', 'updated_at', 'created_at'], 'safe'],
            [['pollguru', 'buzz', 'learning_arcade', 'training_pipeline', 'leadership_edge', 'created_by'], 'integer'],
            [['firstname', 'lastname', 'email', 'company'], 'string', 'max' => 50],
            [['website'], 'string', 'max' => 512],
            [['notes','address'], 'string'],
            [['imageUrl'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpeg,png,jpg'],
            [['mobile_number'], 'string', 'max' => 20],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['city', 'state', 'country', 'address_type', 'pincode'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common\models', 'ID'),
            'code' => Yii::t('common\models', 'Code'),
            'firstname' => Yii::t('common\models', 'Firstname'),
            'lastname' => Yii::t('common\models', 'Lastname'),
            'email' => Yii::t('common\models', 'Email'),
            'company' => Yii::t('common\models', 'Company'),
            'website' => Yii::t('common\models', 'Website'),
            'notes' => Yii::t('common\models', 'Notes'),
            'address' => Yii::t('common\models', 'Address'),
            'imageUrl' => Yii::t('common\models', 'Image'),
            'mobile_number' => Yii::t('common\models', 'Mobile Number'),
            'birthday' => Yii::t('common\models', 'Birthday'),
            'pollguru' => Yii::t('common\models', 'Pollguru'),
            'buzz' => Yii::t('common\models', 'Buzz'),
            'learning_arcade' => Yii::t('common\models', 'Learning Arcade'),
            'training_pipeline' => Yii::t('common\models', 'Training Pipeline'),
            'leadership_edge' => Yii::t('common\models', 'Leadership Edge'),
            'created_by' => Yii::t('common\models', 'Created By'),
            'updated_at' => Yii::t('common\models', 'Updated At'),
            'created_at' => Yii::t('common\models', 'Created At'),
            'city' => Yii::t('common\models', 'City'),
            'state' => Yii::t('common\models', 'State'),
            'country' => Yii::t('common\models', 'Country'),
            'address_type' => Yii::t('common\models', 'Address Type'),
            'pincode' => Yii::t('common\models', 'Pin Code'),
        ];
    }

    /**
     * Convert email to lowercase before save
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->email = strtolower($this->email);
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $vcard = new VCard( );
        $firstname = $this->firstname;
        $lastname = $this->lastname;
        $additional = '';
        $prefix = '';
        $suffix = '';

        // add personal data
        $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);
        if (!empty($this->imageUrl)){
            $vcard->addPhoto($this->imageUrl,true);
        }
        // add work data
        $vcard->addCompany($this->company);
        $vcard->addJobtitle('');
        $vcard->addRole('');
        $vcard->addEmail($this->email);
        $vcard->addPhoneNumber($this->mobile_number, 'PREF;WORK');
        $vcard->addAddress($this->address);
        $vcard->addURL($this->website);
        $vcard->setFilename($this->code,true);
        $vcard->setSavePath(Yii::getAlias('@storage').'/web/source');
        return $vcard->save();
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * {@inheritdoc}
     * @return ContactQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContactQuery(get_called_class());
    }
}
