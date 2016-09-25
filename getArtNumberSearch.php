<?php
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$strSearch=$_GET['q'];
$facilityQuery="SELECT * from vl_request_form where (art_no like '%$strSearch%') group by vl_sample_id";
$facilityInfo=$db->query($facilityQuery);
        $echoResult = array();
        foreach ($facilityInfo as $row) {
            $sampleColDate = explode(" ",$row['sample_collection_date']);
            $echoResult[] = array("id" => $row['vl_sample_id'], "text" => $row['art_no'],'patient'=>1,'patientName'=>$row['patient_name'],'dob'=>$general->humanDateFormat($row['patient_dob']),'otherId'=>$row['other_id'],'ageYrs'=>$row['age_in_yrs'],'ageMnts'=>$row['age_in_mnts'],'gender'=>$row['gender'],'phNum'=>$row['patient_phone_number'],'sampleCollectDate'=>$general->humanDateFormat($sampleColDate[0])." ".$sampleColDate[1],'sampleType'=>$row['sample_id'],'trtPeriod'=>$row['treatment_initiation'],'trtInitiateDate'=>$general->humanDateFormat($row['treatment_initiated_date']),'crntRegimen'=>$row['current_regimen'],'regimenInitiatedOn'=>$general->humanDateFormat($row['date_of_initiation_of_current_regimen']),'details'=>$row['treatment_details'],'pregnant'=>$row['is_patient_pregnant'],'arcNo'=>$row['arc_no'],'breastFeed'=>$row['is_patient_breastfeeding'],'arvAdherence'=>$row['arv_adherence'],'rmTestLastDate'=>$general->humanDateFormat($row['routine_monitoring_last_vl_date']),'rmTestVlValue'=>$row['routine_monitoring_value'],'rmSampleType'=>$row['routine_monitoring_sample_type'],'repeatTestingLastVLDate'=>$general->humanDateFormat($row['vl_treatment_failure_adherence_counseling_last_vl_date']),'repeatTestingVlValue'=>$row['vl_treatment_failure_adherence_counseling_value'],'repeatTestingSampleType'=>$row['vl_treatment_failure_adherence_counseling_sample_type'],'suspendTreatmentLastVLDate'=>$general->humanDateFormat($row['suspected_treatment_failure_last_vl_date']),'suspendTreatmentVlValue'=>$row['suspected_treatment_failure_value'],'suspendTreatmentSampleType'=>$row['suspected_treatment_failure_sample_type'],'requestClinician'=>$row['request_clinician'],'clinicianPhone'=>$row['clinician_ph_no'],'requestDate'=>$general->humanDateFormat($row['request_date']),'vlFocalPerson'=>$row['vl_focal_person'],'vlPhoneNumber'=>$row['focal_person_phone_number'],'emailHf'=>$row['email_for_HF'],'sampleReceivedOn'=>$general->humanDateFormat($row['date_sample_received_at_testing_lab']),'despachedOn'=>$general->humanDateFormat($row['date_results_dispatched']),'rejection'=>$row['rejection']);
        }
        if (count($echoResult) == 0) {
            $echoResult[] = array("id" => $strSearch, "text" => $strSearch,'patient'=>0);
        }
        echo json_encode(array("result" => $echoResult));
?>