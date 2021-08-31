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
                    'email:email',
                    'company',
                    'website',
                    'address',
                    'mobile_number',
//                    [
//                        'attribute' => 'pollguru',
//                        'label' => 'Poll Guru',
//                        'value' => function($data){
//                            if ($data->pollguru){
//                                return "Yes";
//                            }else{
//                                return "NO";
//                            }
//                        }
//                    ],
//                    [
//                        'attribute' => 'buzz',
//                        'label' => 'Buzz',
//                        'value' => function($data){
//                            if ($data->buzz){
//                                return "Yes";
//                            }else{
//                                return "NO";
//                            }
//                        }
//                    ],
//                    [
//                        'attribute' => 'learning_arcade',
//                        'label' => 'Learning Arcade',
//                        'value' => function($data){
//                            if ($data->learning_arcade){
//                                return "Yes";
//                            }else{
//                                return "NO";
//                            }
//                        }
//                    ],
//                    [
//                        'attribute' => 'training_pipeline',
//                        'label' => 'Training Pipeline',
//                        'value' => function($data){
//                            if ($data->training_pipeline){
//                                return "Yes";
//                            }else{
//                                return "NO";
//                            }
//                        }
//                    ],
//                    [
//                        'attribute' => 'leadership_edge',
//                        'label' => 'Leadership Edge',
//                        'value' => function($data){
//                            if ($data->leadership_edge){
//                                return "Yes";
//                            }else{
//                                return "NO";
//                            }
//                        }
//                    ],
//                    'notes',
//                    [
//                        'attribute'=>'created_by',
//                        'value' => function($model){
//                            return $model->createdBy->username;
//                        },
//                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>