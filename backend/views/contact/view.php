<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 */

$this->title = $model->firstname." ".$model->lastname;

//$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Contacts'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contact-view">
    <div class="card">
        <div class="card-header">
<!--            Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary'])-->
<!--            --><?php //echo $model->firstname." ".$model->lastname ?>
            <?php echo Html::a(Yii::t('backend', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('backend', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
        <div class="card-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'imageUrl',
                        'label' => 'Image',
                        'value' => $model->imageUrl . '?nocache=' . time(),
                        'format' => ['image',['width'=>'100','height'=>'100']],
                    ],
//                    'id',
//                    'firstname',
//                    'lastname',
                    'email:email',
                    'company',
                    'website',
                    'address',
                    'mobile_number',
//                    'birthday',
                    [
                        'attribute' => 'pollguru',
                        'label' => 'Poll Guru',
                        'value' => function($data){
                            if ($data->pollguru){
                                return "Yes";
                            }else{
                                return "NO";
                            }
                        }
                    ],
                    [
                        'attribute' => 'buzz',
                        'label' => 'Buzz',
                        'value' => function($data){
                            if ($data->buzz){
                                return "Yes";
                            }else{
                                return "NO";
                            }
                        }
                    ],
                    [
                        'attribute' => 'learning_arcade',
                        'label' => 'Learning Arcade',
                        'value' => function($data){
                            if ($data->learning_arcade){
                                return "Yes";
                            }else{
                                return "NO";
                            }
                        }
                    ],
                    [
                        'attribute' => 'training_pipeline',
                        'label' => 'Training Pipeline',
                        'value' => function($data){
                            if ($data->training_pipeline){
                                return "Yes";
                            }else{
                                return "NO";
                            }
                        }
                    ],
                    [
                        'attribute' => 'leadership_edge',
                        'label' => 'Leadership Edge',
                        'value' => function($data){
                            if ($data->leadership_edge){
                                return "Yes";
                            }else{
                                return "NO";
                            }
                        }
                    ],
                    'notes',
                    [
                        'attribute'=>'created_by',
                        'value' => function($model){
                            return $model->createdBy->username;
                        },
                    ],
                    'updated_at:datetime',
                    'created_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
</div>
