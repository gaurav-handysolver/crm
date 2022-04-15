<?php
/**
 * Created by PhpStorm.
 * User: vishnu
 * Date: 3/30/2022
 * Time: 01:00 PM
 */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var yii\bootstrap4\ActiveForm $form
 */


$this->title = Yii::t('backend', 'Contact - URL');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="container-fluid p-5">
    <p>
        Leadership Edge offer unlimited live virtual classes divided into short segments with practical application, including additional support from a leadership coach, all for one low subscription price. Visit us at <a href="https://www.leadershipedge.live">https://www.leadershipedge.live</a>
    </p>
    <p>
        We offer custom learning solutions focus on any technical or essential skills topic. For more information, visit us at <a href="https://www.lookingforwardconsulting.com">https://www.lookingforwardconsulting.com</a>
    </p>
</div>

<div class="login-box mx-auto mb-5">

    <div class="card">
        <div class="card-body login-card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
            <div class="form">
                    <div class="col-sm">
                        <form name="form" action="" method="post">
                            <div class="form-group">
                                <label for="u-code">Enter Code</label>
                                <input
                                        id="u-code"
                                        type="text"
                                        class="form-control"
                                        name="code"
                                        aria-describedby="Help"
                                        placeholder="Enter Code"
                                        >
                            </div>
                        </form>
                    </div>
                    <div class="text-center card-footer">
                        <?php echo Html::submitButton(Yii::t('backend', 'Get Contact'), ['class' => 'btn btn-primary','id'=>'code-btn']) ?>
                    </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<div class="mh-100" style="height:200px"></div>



<!--    <img src="https://chart.googleapis.com/chart?cht=qr&chs=250x250&chl=https://crm.lookingforwardconsulting.com/backend/web/contact/contact-code-url">-->

<?php
$script = /** @lang text */
    <<< JS
        $(document).ready(function() {
            $(':input[type="submit"]').prop('disabled', true);
            $('input[type="text"]').keyup(function() {
                if($(this).val() != '') {
                    $(':input[type="submit"]').prop('disabled', false);
                }
                if($(this).val().length == 0) {
                    $(':input[type="submit"]').prop('disabled', false);
                }
            });
        });
JS;
$this->registerJs($script);
?>