<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var yii\bootstrap4\ActiveForm $form
 * @var string $status
 */
?>

<div class="contact-form">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => true,'options' => ['enctype' => 'multipart/form-data']]); ?>
    <div class="form form-vertical ">
        <div class="card-body row">
<!--            --><?php //echo $form->errorSummary($model); ?>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="kv-avatar">
                        <?php echo $form->field($model, 'imageUrl')->widget(FileInput::classname(),
                            [
                                'options' => ['accept' => 'image/*'],
                                'pluginOptions' => [
                                    'overwriteInitial'=>true,
                                    'initialPreviewAsData'=>true,
                                    'initialPreviewDownloadUrl'=>$model->imageUrl . '?nocache=' . time(),
                                    'initialPreview'=>$model->imageUrl . '?nocache=' . time(),
                                    'showCaption' => false ,
                                    'deleteUrl'=>(Url::to('image-delete?code='.$model->code)),
                                    'browseIcon' => '<i class="fas fa-camera"></i>',
                                    'browseLabel' =>  'Select',
                                    'showRemove' => false,
                                    'showUpload' => false,
                                    'showCancel' =>false,
                                ],
                            ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'firstname')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'lastname')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'email')->textInput(['disabled' => false]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'company')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model,'job_title')->textInput(['maxlength'=>true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model,'phone_number')->textInput(['maxlength'=>true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'website')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'mobile_number')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'city')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'state')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'country')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form->field($model, 'pincode')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo Yii::$app->user->isGuest == false ? $form->field($model, 'code')->textInput(['maxlength' => true]) .
                                '<p style="font-size: 13px;margin-top: -16px;"><span class="text-danger">Warning!</span> If code is changed then Dot has to be rewritten.</p>' : '' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center card-footer">
<!--            --><?php
//              if(!$model->isNewRecord){
//                  if(!$status){
//                   $disabled = 'disabled';
//                   $title = "Update is currently disabled for your profile. Please contact support.";
//                  }else{
//                      $disabled = '';
//                      $title = "Update Contact";
//                  }
//              }else{
//                  $title = "Create Contact";
//              }
//            ?>
            <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary ']) ?>
        </div>
        <br>
        <br>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<!--/////////////-->

<!-- some CSS styling changes and overrides -->
<style>
    .kv-avatar .krajee-default.file-preview-frame,.kv-avatar .krajee-default.file-preview-frame:hover {
        margin: 0;
        padding: 0;
        border: none;
        box-shadow: none;
        text-align: center;
    }
    .kv-avatar {
        display: inline-block;
    }
    .kv-avatar .file-input {
        display: table-cell;
        width: 213px;
    }
    .kv-reqd {
        color: red;
        font-family: monospace;
        font-weight: normal;
    }
</style>