<?php
  
include APPLICATION_PATH . '/phpqrcode/qrlib.php';
  
$text = "Covid-19";
  

QRcode::png($text);
?>