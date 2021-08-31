<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var yii\bootstrap4\ActiveForm $form
 */
?>

<div class="contact-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="card">
            <div class="card-body">
                <?php echo $form->errorSummary($model); ?>
                <?= $form->field($model, 'imageUrl')->fileInput() ?>
                <?php echo $form->field($model, 'firstname')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'lastname')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'company')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'website')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'mobile_number')->textInput(['maxlength' => true]) ?>
                <?php echo $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

            </div>
            <div class="card-footer">
                <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>
