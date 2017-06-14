<?php
  //sample reorder
  $sampleReorderChecked = '';
  if(trim($vl["sample_reordered"]) == "yes"){
    $sampleReorderChecked = "checked='checked'";
  }
  //patient DOB
  if(isset($vl['patient_dob']) && trim($vl['patient_dob'])!='' && $vl['patient_dob']!='0000-00-00'){
    $patientDob=$general->humanDateFormat($vl['patient_dob']);
  }else{
    $patientDob='';
  }
  //sample collection date
  if(isset($vl['sample_collection_date']) && trim($vl['sample_collection_date'])!='' && $vl['sample_collection_date']!='0000-00-00 00:00:00'){
    $expStr=explode(" ",$vl['sample_collection_date']);
    $vl['sample_collection_date']=$general->humanDateFormat($expStr[0]);
    $sampleCollectionTime = $expStr[1];
  }else{
    $vl['sample_collection_date']='';
    $sampleCollectionTime = '';
  }
  //treatment initiated date
  if(isset($vl['treatment_initiated_date']) && trim($vl['treatment_initiated_date'])!='' && $vl['treatment_initiated_date']!='0000-00-00'){
    $vl['treatment_initiated_date']=$general->humanDateFormat($vl['treatment_initiated_date']);
  }else{
    $vl['treatment_initiated_date']='';
  }
  //date of initiation current regimen
  if(isset($vl['date_of_initiation_of_current_regimen']) && trim($vl['date_of_initiation_of_current_regimen'])!='' && $vl['date_of_initiation_of_current_regimen']!='0000-00-00'){
    $vl['date_of_initiation_of_current_regimen']=$general->humanDateFormat($vl['date_of_initiation_of_current_regimen']);
  }else{
    $vl['date_of_initiation_of_current_regimen']='';
  }
  //sample received datetime
  if(isset($vl['sample_received_at_vl_lab_datetime']) && trim($vl['sample_received_at_vl_lab_datetime'])!='' && $vl['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
    $expStr=explode(" ",$vl['sample_received_at_vl_lab_datetime']);
    $vl['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
    $vl['sample_received_at_vl_lab_datetime']='';
  }
  //sample tested datetime
  if(isset($vl['sample_tested_datetime']) && trim($vl['sample_tested_datetime'])!='' && $vl['sample_tested_datetime']!='0000-00-00 00:00:00'){
    $expStr=explode(" ",$vl['sample_tested_datetime']);
    $vl['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
    $vl['sample_tested_datetime']='';
  }
  //result dispatch datetime
  if(isset($vl['result_dispatched_datetime']) && trim($vl['result_dispatched_datetime'])!='' && $vl['result_dispatched_datetime']!='0000-00-00 00:00:00'){
    $expStr=explode(" ",$vl['result_dispatched_datetime']);
    $vl['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
    $vl['result_dispatched_datetime']='';
  }
  //test request date
  if(isset($vl['test_requested_on']) && trim($vl['test_requested_on'])!='' && $vl['test_requested_on']!='0000-00-00'){
    $vl['test_requested_on']=$general->humanDateFormat($vl['test_requested_on']);
  }else{
    $vl['test_requested_on']='';
  }
  //pdf content
  $html = '';
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Clinic Information: (To be filled by requesting Clinican/Nurse)</h3><hr/></th>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample ID</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Reordered</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Province</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['sample_code'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['sample_reordered']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['facility_state']).'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">District</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Clinic/Health Center</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Clinic/Health Center Code</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['facility_district']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['facility_name']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['facility_code'].'</td>';
  $html .='</tr>';
  $html.='</table>';
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Patient Information</h3><hr/></th>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">ART (TRACNET) No. </td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Birth</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">If DOB unknown,Age in Years</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['patient_art_no'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$patientDob.'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['patient_age_in_years'].'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">If Age < 1, Age in Months</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Patient Name</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Gender</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['patient_age_in_months'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['patient_first_name']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords(str_replace('_',' ',$vl['patient_gender'])).'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Phone Number</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td colspan="3" style="line-height:11px;font-size:11px;text-align:left;">'.$vl['patient_mobile_number'].'</td>';
  $html .='</tr>';
  $html.='</table>';
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Sample Information</h3><hr/></th>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Sample Collection  </td>';
   $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Type</td>';
  $html .='</tr>';
  $html .='<tr>';
    $sampleName = (isset($vl['sample_name']))?ucwords($vl['sample_name']):'';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['sample_collection_date']." ".$sampleCollectionTime.'</td>';
    $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$sampleName.'</td>';
  $html .='</tr>';
  $html.='</table>';
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Treatment Information</h3><hr/></th>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Treatment Initiation </td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Current Regimen</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Initiation of Current Regimen</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['treatment_initiated_date'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['current_regimen'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['date_of_initiation_of_current_regimen'].'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">ARV Adherence</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Is Patient Pregnant?</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Is Patient Breastfeeding?</td>';
  $html .='</tr>';
  $arvAdherencePercentage = '';
  if($vl['arv_adherance_percentage'] == 'good'){
    $arvAdherencePercentage = 'Good >= 95%';
  }else if($vl['arv_adherance_percentage'] == 'fair'){
    $arvAdherencePercentage = 'Fair (85-94%)';
  }else if($vl['arv_adherance_percentage'] == 'poor'){
    $arvAdherencePercentage = 'Poor < 85%';
  }
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$arvAdherencePercentage.'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['is_patient_pregnant'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['is_patient_breastfeeding'].'</td>';
  $html .='</tr>';
  $html.='</table>';
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Indication for Viral Load Testing (Please tick one):<small>(To be completed by clinician)</small></h3><hr/></th>';
  $html .='</tr>';
  if($vl['reason_for_vl_testing']=='routine'){
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Routine Monitoring</h4></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($vl['last_vl_date_routine']).'</td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$vl['last_vl_result_routine'].'</td>';
    $html .='</tr>';
  }else if($vl['reason_for_vl_testing']=='failure'){
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Repeat VL test after suspected treatment failure adherence counselling</h4></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($vl['last_vl_date_failure_ac']).'</td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$vl['last_vl_result_failure_ac'].'</td>';
    $html .='</tr>';
  }else if($vl['reason_for_vl_testing']=='suspect'){
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:12px;font-weight:bold;text-align:left;">Suspect Treatment Failure</td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($vl['last_vl_date_failure']).'</td>';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$vl['last_vl_result_failure'].'</td>';
    $html .='</tr>';
  }else{
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Routine Monitoring</h4></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Repeat VL test after suspected treatment failure adherence counselling</h4></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Suspect Treatment Failure</h4></td>';
    $html .='</tr>';
  }
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Request Clinician </td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Phone Number</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Request Date</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['request_clinician_name']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['request_clinician_phone_number'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['test_requested_on'].'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Focal Person</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Focal Person Phone Number</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Email for HF</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['vl_focal_person']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['vl_focal_person_phone_number'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['facility_emails'].'</td>';
  $html .='</tr>';
  $html.='</table>';
  
  $html.='<table style="padding:0px 2px 2px 2px;">';
  $html .='<tr>';
   $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Laboratory Information</h3><hr/></th>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Lab Name </td>';
   $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Testing Platform</td>';
  $html .='</tr>';
  $labName = (isset($vl['labName']))?ucwords($vl['labName']):'';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$labName.'</td>';
    $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['vl_test_platform']).'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date Sample Received at Testing Lab</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Testing Date</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date Results Dispatched</td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['sample_received_at_vl_lab_datetime'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['sample_tested_datetime'].'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$vl['result_dispatched_datetime'].'</td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Rejection</td>';
    if($vl['is_sample_rejected'] == 'yes'){
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Rejection Reason</td>';
    }else{
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Viral Load Result (copiesl/ml)</td>'; 
    }
  $html .='</tr>';
  $html .='<tr>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($vl['is_sample_rejected']).'</td>';
    if($vl['is_sample_rejected'] == 'yes'){
      $rejectedReason = (isset($vl['rejectionReason']))?ucwords($vl['rejectionReason']):'';
      $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$rejectedReason.'</td>';
    }else{
      if($vl['result'] == 'Target Not Detected'){
        $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">TND</td>';
      }else{
        $html .='<td colspan="2" style="line-height:11px;font-size:11px;text-align:left;">'.$vl['result_value_absolute'].'</td>';
      }
    }
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td colspan="3" style="line-height:10px;"></td>';
  $html .='</tr>';
  $html .='<tr>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Approved By</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Laboratory Scientist Comments</td>';
   $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Status</td>';
  $html .='</tr>';
  $html .='<tr>';
    $approvedBy = (isset($vl['resultApprovedBy']))?ucwords($vl['resultApprovedBy']):'';
    $statusName = (isset($vl['status_name']))?ucwords($vl['status_name']):'';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$approvedBy.'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucfirst($vl['approver_comments']).'</td>';
    $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$statusName.'</td>';
  $html .='</tr>';
$html.='</table>';