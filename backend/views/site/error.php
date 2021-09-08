<?php

use yii\helpers\Html;
use rmrevin\yii\fontawesome\FAS;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
$statusCode = property_exists($exception, 'statusCode') ? $exception->statusCode : 500;
$textColor = $statusCode == 500? 'danger': 'warning';
?>

    <div class="error-page">
    <h2 class="headline text-<?php echo $textColor?>"><?php echo $statusCode ?></h2>

    <div class="error-content">
        <h3 class="font-weight-bold"><?php echo FAS::icon('exclamation-triangle', ['class' => "text-$textColor"]).' '.nl2br(Html::encode($message)) ?></h3>

        <p>
            <?php echo Yii::t('backend', 'Oops! Something went wrong...') ?>
        </p>
    </div>
</div>