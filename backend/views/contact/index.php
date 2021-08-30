<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var backend\models\search\ContactSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = Yii::t('backend', 'Contacts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contact-index">
    <div class="card">
<!--        <div class="card-header">-->
<!--            --><?php //echo Html::a(Yii::t('backend', 'Create {modelClass}', ['modelClass' => 'Contact',]), ['create'], ['class' => 'btn btn-success']) ?>
<!--        </div>-->

        <div class="card-body p-0">
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
            <?php echo GridView::widget([
                'layout' => "{items}\n{pager}",
                'options' => [
                    'class' => ['gridview', 'table-responsive'],
                ],
                'tableOptions' => [
                    'class' => ['table', 'text-nowrap', 'table-striped', 'table-bordered', 'mb-0'],
                ],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'id',
                        'label' => 'Contact Id',
                        'visible' => false
                    ],
                    [
                        'attribute' => 'imageUrl',
                        'label' => 'Image',
                        'value' => 'imageUrl',
                        'format' => ['image',['width'=>'50','height'=>'50']],
                    ],
//                    [
//                        'attribute' => 'firstname',
//                        'label' => 'Name',
//                        'value' => function($data){
//                            return $data->firstname." ".$data->lastname;
//                        },
//                    ],
                    'firstname',
                    'lastname',
                    'email:email',
//                    'company',
                    [
                        'attribute' => 'company',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
//                    'website',
                    [
                        'attribute' => 'website',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
                    'mobile_number',
//                    'birthday',
                    [
                        'attribute' => 'address',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
                    [
                        'attribute' => 'notes',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
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
                    [
                        'attribute'=>'created_by',
                        'value' => 'createdBy.username'
                    ],
                    'code',
//                    'updated_at',
//                    'created_at',
                    [
                        'class' => \common\widgets\ActionColumn::class,
                        'template' => '{view}{delete}',
                    ],
                ],
            ]); ?>
    
        </div>
        <div class="card-footer">
            <?php echo getDataProviderSummary($dataProvider) ?>
        </div>
    </div>

</div>
