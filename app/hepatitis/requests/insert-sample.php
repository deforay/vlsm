<?php

use App\Models\Hepatitis;


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$hepatitisModel = new Hepatitis();
echo $hepatitisModel->insertSampleCode($_POST);
