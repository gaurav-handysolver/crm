<?php

namespace common\models;

use common\models\query\ContactQuery;
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
 * @property string|null $mobile_number
 * @property string|null $birthday
 * @property int|null $pollguru
 * @property int|null $buzz
 * @property int|null $learning_arcade
 * @property int|null $training_pipeline
 * @property int|null $leadership_edge
 * @property int|null $created_by
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
            [['birthday', 'updated_at', 'created_at'], 'safe'],
            [['pollguru', 'buzz', 'learning_arcade', 'training_pipeline', 'leadership_edge', 'created_by'], 'integer'],
            [['firstname', 'lastname', 'email', 'company'], 'string', 'max' => 50],
            [['website'], 'string', 'max' => 512],
            [['mobile_number'], 'string', 'max' => 20],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common\models', 'ID'),
            'firstname' => Yii::t('common\models', 'Firstname'),
            'lastname' => Yii::t('common\models', 'Lastname'),
            'email' => Yii::t('common\models', 'Email'),
            'company' => Yii::t('common\models', 'Company'),
            'website' => Yii::t('common\models', 'Website'),
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
        ];
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
