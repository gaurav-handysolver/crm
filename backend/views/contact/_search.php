<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var yii\bootstrap4\ActiveForm $form
 */
?>

<div class="contact-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'id') ?>
    <?php echo $form->field($model, 'firstname') ?>
    <?php echo $form->field($model, 'lastname') ?>
    <?php echo $form->field($model, 'email') ?>
    <?php echo $form->field($model, 'company') ?>
    <?php // echo $form->field($model, 'website') ?>
    <?php // echo $form->field($model, 'mobile_number') ?>
    <?php // echo $form->field($model, 'birthday') ?>
    <?php // echo $form->field($model, 'pollguru') ?>
    <?php // echo $form->field($model, 'buzz') ?>
    <?php // echo $form->field($model, 'learning_arcade') ?>
    <?php // echo $form->field($model, 'training_pipeline') ?>
    <?php // echo $form->field($model, 'leadership_edge') ?>
    <?php // echo $form->field($model, 'created_by') ?>
    <?php // echo $form->field($model, 'updated_at') ?>
    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('backend', 'Reset'), ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
