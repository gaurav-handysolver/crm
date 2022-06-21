<?php

use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\Contact $model
 */

$this->title = $model->firstname." ".$model->lastname;

?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://crm.lookingforwardconsulting.com/backend/web/assets/35901683/css/bootstrap.css">
    <title>NFC</title>


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
        }

        .main__banner {
            position: relative;
            height: 400px;
        }

        .main__banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main__banner .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: #ffffff;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main__banner h1 {
            font-size: 50px;
            text-align: center;
        }

        .user__detail__section {
            padding: 5rem 0;
        }

        .inner__box {
            width: max-content;
            margin: auto;
            padding: 5rem;
            background: #fff;
            box-shadow: 0px 0px 10px 0px #f6f6f6;
        }

        .user__detail__section a {
            text-decoration: none;
            color: #000000;
            transition: .5s ease-in-out;
        }

        .user__detail__section a:hover {
            color: #007bff;
        }

        .user__detail__section .d-flex {
            align-items: center;
        }

        .user__detail__section .user__image {
            width: 100px;
            margin-right: 1.5rem;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user__detail__section .right__detail,
        .user__detail__section .right__detail a {
            color: #565656;
        }

        .user__detail__section .further_details {
            margin-top: 2rem;
        }

        .user__detail__section .user-name {
            font-size: 20px;
        }

        .user__detail__section .entry-title {
            font-size: 20px;
            margin-bottom: 1.2rem;
        }

        .user__detail__section .left__label {
            display: inline-block;
            min-width: 9rem;
        }

        .user__detail__section .d-flex:not(:last-child) {
            margin-bottom: 1rem;
        }


        @media (max-width: 640px) {
            .inner__box {
                width: 100%;
                padding: 2rem 10px;
            }

            .user__detail__section {
                padding: 3rem 0;
            }

            .main__banner h1 {
                font-size: 30px;
            }

            .user__detail__section .d-flex {
                font-size: 12px;
            }

            .user__detail__section .user-name,
            .user__detail__section .entry-title {
                font-size: 16px;
            }

            .user__detail__section .left__label {
                min-width: 6rem;
            }

            .user__detail__section .d-flex:not(:last-child) {
                margin-bottom: 0.8rem;
            }

            .user__detail__section .user__image {
                width: 75px;
                margin-right: 0.5rem;
            }

            .main__banner {
                height: 150px;
            }
        }
    </style>
</head>

<section class="user__detail__section">
    <div class="container">
        <div class="inner__box">
            <div class="d-flex">
                <div class="user__image">
                    <?php if(isset($model->imageUrl)){ ?>
                        <img src=<?= $model->imageUrl ?> alt="user-image" class="img-fluid">
                    <?php }else{  ?>
                        <img src='https://crm.lookingforwardconsulting.com/backend/web/img/anonymous.png' alt="user-image" class="img-fluid">
                    <?php } ?>

                </div>
                <div class="right__contentt">
                    <h5 class="user-name"><?= $model->firstname." ". $model->lastname ?></h5>
                    <a href="mailto:rahul_matharu@handysolver.com"><?= $model->email ?></a>
                </div>

            </div>
            <div class="further_details">
                <h4 class="entry-title">User Details</h4>
                <div class="d-flex">
                    <span class="left__label">Email:</span>
                    <span class="right__detail">
                            <a href="mailto:rahul_matharu@handysolver.com"><?= $model->email ?></a>
                        </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Company:</span>
                    <span class="right__detail"><?= $model->company ?></span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Website:</span>
                    <span class="right__detail">
                            <a href="" target="_blank"><?= $model->website ?></a>
                        </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Address:</span>
                    <span class="right__detail"><?= $model->address . ' ' . $model->city . ' ' . $model->state . ' ' .$model->country . ' ' . $model->pincode ?></span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Mobile Number:</span>
                    <span class="right__detail">
                            <a href="tel:+91-0987654321"><?= $model->mobile_number ?></a>
                        </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Buzz:</span>
                    <span class="right__detail">
                            <a href="tel:+91-0987654321"><?= $model->buzz == 1 ? "Yes" : "No" ?></a>
                    </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Learning Arcade:</span>
                    <span class="right__detail">
                            <a href="tel:+91-0987654321"><?= $model->learning_arcade == 1 ? "Yes" : "No" ?></a>
                    </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Training Pipeline:</span>
                    <span class="right__detail">
                            <a href="tel:+91-0987654321"><?= $model->training_pipeline == 1 ? "Yes" : "No" ?></a>
                    </span>
                </div>
                <div class="d-flex">
                    <span class="left__label">Leadership Edge:</span>
                    <span class="right__detail">
                            <a href="tel:+91-0987654321"><?= $model->leadership_edge == 1 ? "Yes" : "No" ?></a>
                    </span>
                </div>
            </div>
        </div>

    </div>
</section>