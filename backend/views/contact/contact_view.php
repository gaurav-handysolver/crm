<?php

use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 */

$this->title = $model->firstname." ".$model->lastname;

?>
<div class="mx-5 contact-view">
    <div class="card">
        <div class="card-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'imageUrl',
                        'label' => 'Image',
                        'value' => $model->imageUrl,
                        'format' => ['image',['width'=>'100','height'=>'100']],
                    ],
                    'firstname',
                    'lastname',
                    'email:email',
                    'company',
                    'website',
                    'address',
                    'mobile_number',
                ],
            ]) ?>
        </div>
    </div>
</div>