<?php

namespace api\modules\v1\models\definitions;

/**
 * @SWG\Definition(
 *          @SWG\Property(property="name", type="string", example = "Unauthorized"),
 *          @SWG\Property(property="message", type="string", example = "Invalid token!"),
 *          @SWG\Property(property="code", type="integer", example = "0"),
 *          @SWG\Property(property="stauts", type="integer", example = "401"),
 *         @SWG\Property(property="type", type="string", example = "yii\web\HttpException"),
 * )
 *
*/
class JWT
{

}