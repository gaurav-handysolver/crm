<?php

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 * @var string $status
 */

$this->title = Yii::t('backend', 'Update {modelClass} ', [
    'modelClass' => 'Contact',
]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Contacts'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'code' => $model->code]];
//$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="mx-5 contact-update">

    <?php echo $this->render('_form', [
        'model' => $model,
//        'status' => $status
    ]) ?>

</div>
