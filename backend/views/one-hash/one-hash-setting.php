<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('backend','OneHash Setting');
$this->params['breadcrumbs'][] = $this->title;

/**
 *  * @var backend\models\OneHash $model

 */
?>

<div>
    <div >
        <div>
            <?php $form = ActiveForm::begin(); ?>

            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form->field($model,'is_enabled')->checkbox()?>

                    <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Save') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

