<?php

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 */

$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => 'Contact',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Contacts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contact-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
