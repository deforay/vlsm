<?php
//   $_GET['id'] = "6777";
include APPLICATION_PATH . '/phpqrcode/qrlib.php';
  $id = ($_GET['id']);
$text = $_SERVER['HTTP_HOST'] . "/covid-19/results/covid-19-pdf-results.php?id=$id";
  

QRcode::png($text);
?>