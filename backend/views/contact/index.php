<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

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
                    'firstname',
                    'lastname',
                    'email:email',
                    [
                        'attribute' => 'company',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
                    [
                        'attribute' => 'website',
                        'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                    ],
                    'mobile_number',
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
                    [
                        'class' => \common\widgets\ActionColumn::class,
                        'template' => '{view} {update} {copy} {delete}',
                        'buttons' => [
                            'copy' => function($url, $model, $key){
                                return Html::button('<i class="copy-to-clipboard fa fa-clipboard" data-url="'.Url::to(['contact/auth-contact', 'code' => $model->code],true).'" id="copyBtn" "></i>',['class'=>'btn btn-primary btn-xs','title'=>'Copy Link']);
                            },

                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action === 'view') {
                                $url = Url::to(['contact/view', 'code' => $model->code]);
                                return $url;
                            }
                            if ($action === 'delete') {
                                $url = Url::to(['contact/delete', 'id' => $model->id]);
                                return $url;
                            }
                            if ($action === 'update') {
                                $url = Url::to(['contact/update-contact', 'code' => $model->code,'email'=>$model->email]);
                                return $url;
                            }
                        }
                    ],
                ],
            ]); ?>

        </div>
        <div class="card-footer">
            <?php echo getDataProviderSummary($dataProvider) ?>
        </div>
    </div>

</div>

<script>

    document.querySelectorAll(".copy-to-clipboard").forEach(button => {
        button.addEventListener('click', (event) => {
        console.log(event.target.dataset.url);
    var link = event.target.dataset.url;
    navigator.clipboard.writeText(link);
    alert("Link Copied");
    })
    })

</script>