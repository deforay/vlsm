<?php


use App\Services\CommonService;

$general=new CommonService();
$artNo=$_POST['artPatientNo'];

$count = 0;
$pQuery="SELECT * FROM form_eid where (child_id like '%".$artNo."%' OR child_name like '%".$artNo."%' OR child_surname like '%".$artNo."%' OR caretaker_phone_number like '%".$artNo."%') ORDER BY sample_tested_datetime DESC";
$pResult = $db->rawQuery($pQuery);
$count = count($pResult);
echo $count;
