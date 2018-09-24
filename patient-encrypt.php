<?php
$servername = "localhost";
$username = "root";
$password = "zaq12345";
$dbname = "vl_lab_request";
$conn = new mysqli($servername, $username, $password,$dbname);
if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
}

$vlQuery="SELECT vl_sample_id,sample_code,remote_sample_code,patient_first_name,patient_middle_name,patient_last_name,remote_sample,patient_art_no from vl_request_form";
$vlQueryInfo=$conn->query($vlQuery);
$vldata = array();
$c = count($vlQueryInfo->num_rows);
$i = 1;
while($samples = $vlQueryInfo->fetch_assoc()) {
     if($i == $c){
          $msg = "Patient name encrypted successfully!";
     }

     $sampleCode = $samples['patient_art_no'];

     $vldata['patient_first_name'] = crypto('encrypt',$samples['patient_first_name'],$sampleCode);
     $vldata['patient_middle_name'] = crypto('encrypt',$samples['patient_middle_name'],$sampleCode);
     $vldata['patient_last_name'] = crypto('encrypt',$samples['patient_last_name'],$sampleCode);

     $sql = "UPDATE vl_request_form SET patient_first_name='".$vldata['patient_first_name']."',
     patient_middle_name='".$vldata['patient_middle_name']."',
     patient_last_name ='".$vldata['patient_last_name']."'
     WHERE vl_sample_id='".$samples['vl_sample_id']."'";

     if (mysqli_query($conn, $sql)) {
          //        echo "Record updated successfully";
     } else {
          echo "Error updating sample code: ".$sampleCode." " . mysqli_error($conn);
          die;
     }

     $i++;
}
echo $msg;

function crypto($action, $inputString, $secretIv) {

     if (empty($inputString)) return "";

     $output = false;
     $encrypt_method = "AES-256-CBC";
     $secret_key = 'rXBCNkAzkHXGBKEReqrTfPhGDqhzxgDRQ7Q0XqN6BVvuJjh1OBVvuHXGBKEReqrTfPhGDqhzxgDJjh1OB4QcIGAGaml';

     // hash
     $key = hash('sha256', $secret_key);

     if(empty($secretIv)){
          $secretIv = 'sd893urijsdf8w9eurj';
     }
     // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
     $iv = substr(hash('sha256', $secretIv), 0, 16);

     if ( $action == 'encrypt' ) {
          $output = openssl_encrypt($inputString, $encrypt_method, $key, 0, $iv);
          $output = base64_encode($output);
     } else if( $action == 'decrypt' ) {
          $output = openssl_decrypt(base64_decode($inputString), $encrypt_method, $key, 0, $iv);
     }
     return $output;
}
