<?php

use backend\models\OneHash;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

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
        <?php Pjax::begin(['id' => 'contact-record']); ?>
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
                        'value' => function($model){
                            if(isset($model->imageUrl)){
                                return "<img src='" . $model->imageUrl . "?nocache=" . time() . "' style='width:50px;height:50px;'></img>";
                            }else{
                                return "<img src='https://crm.lookingforwardconsulting.com/backend/web/img/anonymous.png' style='width:50px;height:50px;'></img>";
                            }
                        },
                        'format' => 'raw'
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
                        'template' => '{view} {update} {copy} {delete} {sync}',
                        'buttons' => [
                            'copy' => function($url, $model, $key){
                                return Html::button('<i class="copy-to-clipboard fa fa-clipboard" data-url="'.Url::to(['contact/auth-contact', 'code' => $model->code],true).'" id="copyBtn" "></i>',['class'=>'btn btn-primary btn-xs clipboard','title'=>'Copy Link','data-url' => Url::to(['contact/auth-contact', 'code' => $model->code],true)]);
                            },
                            'sync' => function($url, $model, $key){
                                $oneHashSettingStatus = OneHash::find()->where(['setting_name'=>OneHash::ONE_HASH_SETTING_NAME])->one();
                                if($oneHashSettingStatus->is_enabled != OneHash::ONE_HASH_SETTING_OFF){
                                    $disabled = 'disabled';
                                    $btnTitle = 'Onehash is disconnect';
                                }else{
                                    $disabled = '';
                                    $btnTitle = 'Add record on Onehash';
                                }
                                return Html::button('<i class="fas fa-sync-alt" data-url="'.Url::to(['contact/add-record-onehash', 'id' => $model->id],true).'"  id="syncBtn"></i>',['class'=>'btn btn-primary btn-xs syncBtn '.$disabled,'title'=>$btnTitle,'data-url' => Url::to(['contact/add-record-onehash', 'id' => $model->id],true)]);

                            }

                        ],
                          'visibleButtons' => [
                            'update' => function ($model, $key, $index) {
                                return \backend\controllers\ContactController::checkContact($model);
                             },
                              'copy' => function ($model, $key, $index) {
                                  return \backend\controllers\ContactController::checkContact($model);
                              },
                              'sync' => function($model, $key, $index){
                                  return !\backend\controllers\ContactController::checkContact($model);
                              }
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

            <?php Pjax::end(); ?>

        </div>
        <div class="card-footer">
            <?php echo getDataProviderSummary($dataProvider) ?>
        </div>
    </div>

</div>

<script>

    document.querySelectorAll(".clipboard").forEach(button => {
        button.addEventListener('click', (event) => {
    var link = event.target.dataset.url;
    console.log(event);
    copyText(link);
    // navigator.clipboard.writeText(link);
    // alert("");
    })
    });

    document.querySelectorAll(".syncBtn").forEach(button => {
       button.addEventListener("click", (event) => {
           var link = event.target.dataset.url;
           var syncIcon = event.target;
           if(confirm('Are you sure you want to add record on Onehash?')){

               button.setAttribute("disabled", true);
               syncIcon.setAttribute("disabled", true);
               syncIcon.classList.toggle('down');

               $.ajax({
                   url: link,
                   type: 'GET',
                   success: function(result){
                     if(result.status){
                         button.removeAttribute("disabled");
                         syncIcon.removeAttribute("disabled");
                         //pjax reload
                         location.reload();
                         // $.pjax.reload({container: '#contact-record', timeout: 2000});
                     }else{
                         console.log(result);
                         syncIcon.classList.toggle('down');
                         button.setAttribute("disabled", true);
                         syncIcon.setAttribute("disabled", true);
                         location.reload();

                     }
                   }
               });
           }


       })
    });

    async function copyText(link){
        await navigator.clipboard.writeText(link).then(()=>{
         alert("Link Copied");
        }).catch(()=>{
            alert("Link not copied");
        })
    }

</script>

<style>

    #syncBtn.down{
        -moz-transform:rotate(180deg);
        -webkit-transform:rotate(180deg);
        transform:rotate(180deg);
        animation: rotate-animation 2s infinite linear;

    }
    @keyframes rotate-animation {
        0% {
            transform: rotate(0deg);
        }
        50% {
            transform: rotate(180deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>