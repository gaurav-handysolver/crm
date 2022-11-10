<?php

namespace common\models;

use common\components\awsParameterStore\AwsParameterStore;
use common\components\onehash\OneHashService;
use common\components\onehash\OneHashServiceNew;
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
 *  @property string job_title
 *  @property string phone_number
 *
 * @property User $createdBy
 */
class Contact extends \yii\db\ActiveRecord
{
    //This is the test account
    CONST USER_ACCOUNT_FOR_TESTING = 17;
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
            [['mobile_number'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['city', 'state', 'country', 'address_type', 'pincode', 'lead_id'], 'string', 'max' => 255],
            [['job_title'],'string'],
            [['phone_number'],'integer']
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
            'lead_id' => Yii::t('common\models', 'Lead Id'),

            'job_title' => Yii::t('common\models', 'Job Title'),
            'phone_number' => Yii::t('common\models', 'Phone Number'),
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
        $this->writeVcard($this);
    }

    public static function writeVcard($contact){
        $vcard = new VCard();
        $firstname = $contact->firstname;
        $lastname = $contact->lastname;
        $additional = '';
        $prefix = '';
        $suffix = '';

        $vcard->addAddress(
            '',
            '',
            $contact->address ?? '',
            $contact->city ?? '',
            $contact->state ?? '',
            $contact->pincode ?? '',
            $contact->country ?? '',
            $contact->address_type ?? ''
        );


        // add personal data
        $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);
        if (!empty($contact->imageUrl)){
            $vcard->addPhoto($contact->imageUrl,true);
        }
        // add work data
        $vcard->addCompany($contact->company);
        $vcard->addJobtitle($contact->job_title);
        $vcard->addRole('');
        $vcard->addEmail($contact->email);
        $vcard->addPhoneNumber($contact->phone_number, 'PREF;HOME');
        $vcard->addPhoneNumber($contact->mobile_number,'PREF;CELL');
        $vcard->addURL($contact->website);
        $vcard->setFilename($contact->code,true);
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

    public static function findOnehashContact($emailId,$authToken)
    {
        $authToken1 = 'token '.$authToken;
        $url = 'https://one.lookingforwardconsulting.com/api/resource/Contact?filters=[["email_id","=",'.'"'.$emailId.'"'.']]';
        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL =>$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken1}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        //Getting the response of http code of onehash API
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == \api\modules\v1\resources\Contact::SUCCESS_RESPONSE){
                $data = json_decode($response)->data;
                if(isset($data) && !empty($data)){
                    $contactName = $data[0]->name;
                    return array('status'=>true,'msg' => 'Contact found', 'payload' => $contactName);
                }else{
                    return array('status'=>false,'msg'=>'Contact not found','payload' => 'Contact not found');
                }
            }else{
                $functionName = 'Find-OneHash-Contact function';
                return array('status' => false, 'msg' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName,'payload' => $response);
            }
        }
    }

    public static function getOneHashLeads($authToken){
        $authToken1 = 'token '.$authToken;
        $url = 'https://one.lookingforwardconsulting.com/api/resource/Lead?limit_page_length=4000&fields=["email_id"]';

        $curl = curl_init();

        //set false to run api in localhost using curl, otherwise it will throw SSL certification expire issue
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL =>$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: ${authToken1}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        //Getting the response of http code of onehash API
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            Yii::error("Lead-Address Getting oneHash API curl error #:" . $err);
            return [
                'status'=>false,
                'error' => "Lead-Address Getting oneHash API curl error"
            ];
        } else {
            if($httpCode == \api\modules\v1\resources\Contact::SUCCESS_RESPONSE){
                $data = json_decode($response)->data;
                if(isset($data) && !empty($data)){
                    $contactName = $data;
                    return array('status'=>true,'msg' => 'Contact found', 'payload' => $contactName);
                }else{
                    return array('status'=>false,'msg'=>'Contact not found','payload' => 'Contact not found');
                }
            }else{
                $functionName = 'Find-OneHash-Contact function';
                return array('status' => false, 'msg' => 'Onehash API Error with response code '.$httpCode .' in '.$functionName,'payload' => $response);
            }
        }
    }

}
