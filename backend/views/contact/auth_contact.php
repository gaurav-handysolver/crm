<?php
/**
 * Created by PhpStorm.
 * User: vishnu
 * Date: 9/6/2021
 * Time: 12:26 PM
 */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var yii\bootstrap4\ActiveForm $form
 */


$this->title = Yii::t('backend', 'Auth-Contact');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="login-box mx-auto">

    <div class="card">
        <div class="card-body login-card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
            <div class="form">
                    <div class="col-sm">
                        <form name="form" action="" method="post">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" name="email_id" aria-describedby="emailHelp" placeholder="Enter email" oninput="this.value = this.value.toLowerCase()">
                            </div>
                        </form>
                    </div>
                    <div class="text-center card-footer">
                        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Email Verification'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>