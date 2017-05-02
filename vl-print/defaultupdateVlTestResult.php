<?php
//print_r($result);die;
ob_start();
//include('../header.php');
//include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
//$id=base64_decode($_GET['id']);
$fQuery="SELECT vl.sample_code,vl.patient_first_name,vl.patient_art_no,vl.patient_dob,vl.patient_gender,vl.patient_mobile_number,vl.patient_location,vl.sample_collection_date,vl.treatment_initiation,vl.date_of_initiation_of_current_regimen,vl.is_patient_pregnant,vl.is_patient_breastfeeding,vl.arv_adherance_percentage,vl.last_vl_date_routine,vl.last_vl_result_routine,vl.last_vl_sample_type_routine,vl.last_vl_date_failure_ac,vl.last_vl_result_failure_ac,vl.last_vl_sample_type_failure_ac,vl.last_vl_date_failure,vl.last_vl_result_failure,vl.last_vl_sample_type_failure,vl.request_clinician_name,vl.request_clinician_phone_number,vl.sample_testing_date,vl.vl_focal_person,vl.vl_focal_person_phone_number,vl.sample_received_at_vl_lab_datetime,vl.result_dispatched_datetime,vl.is_sample_rejected,vl.patient_other_id,vl.patient_age_in_years,vl.patient_age_in_months,vl.treatment_initiated_date,vl.patient_anc_no,vl.treatment_details,vl.lab_name,vl.lab_contact_person,vl.lab_phone_number,vl.sample_tested_datetime,vl.result_value_log,vl.result_value_absolute,vl.result_value_text,vl.result,vl.approver_comments,vl.result_reviewed_by,vl.result_reviewed_datetime,vl.result_status,ts.status_name,r_a_c_d.art_code,f.facility_name,f.facility_code,f.facility_emails,f.facility_state,f.facility_hub_name,r_s_t.sample_name,r_s_t_rm.sample_name as snrm,r_s_t_tfac.sample_name as sntfac,r_s_t_stf.sample_name as snstf from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_type as r_s_t ON r_s_t.sample_id=vl.sample_type INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_sample_type as r_s_t_rm ON r_s_t_rm.sample_id=vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as r_s_t_tfac ON r_s_t_tfac.sample_id=vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as r_s_t_stf ON r_s_t_stf.sample_id=vl.last_vl_sample_type_failure LEFT JOIN r_art_code_details as r_a_c_d ON r_a_c_d.art_id=vl.current_regimen where vl_sample_id=$id";
//echo $fQuery;die;
$result=$db->query($fQuery);

if(isset($result[0]['patient_dob']) && trim($result[0]['patient_dob'])!='' && $result[0]['patient_dob']!='0000-00-00'){
 $result[0]['patient_dob']=$general->humanDateFormat($result[0]['patient_dob']);
}else{
 $result[0]['patient_dob']='';
}

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_collection_date']);
 $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_collection_date']='';
}

if(isset($result[0]['treatment_initiated_date']) && trim($result[0]['treatment_initiated_date'])!='' && trim($result[0]['treatment_initiated_date'])!='0000-00-00'){
 $result[0]['treatment_initiated_date']=$general->humanDateFormat($result[0]['treatment_initiated_date']);
}else{
 $result[0]['treatment_initiated_date']='';
}

if(isset($result[0]['date_of_initiation_of_current_regimen']) && trim($result[0]['date_of_initiation_of_current_regimen'])!='' && trim($result[0]['date_of_initiation_of_current_regimen'])!='0000-00-00'){
 $result[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result[0]['date_of_initiation_of_current_regimen']);
}else{
 $result[0]['date_of_initiation_of_current_regimen']='';
}

if(isset($result[0]['last_vl_date_routine']) && trim($result[0]['last_vl_date_routine'])!='' && trim($result[0]['last_vl_date_routine'])!='0000-00-00'){
 $result[0]['last_vl_date_routine']=$general->humanDateFormat($result[0]['last_vl_date_routine']);
}else{
 $result[0]['last_vl_date_routine']='';
}

if(isset($result[0]['last_vl_date_failure_ac']) && trim($result[0]['last_vl_date_failure_ac'])!='' && trim($result[0]['last_vl_date_failure_ac'])!='0000-00-00'){
 $result[0]['last_vl_date_failure_ac']=$general->humanDateFormat($result[0]['last_vl_date_failure_ac']);
}else{
 $result[0]['last_vl_date_failure_ac']='';
}

if(isset($result[0]['last_vl_date_failure']) && trim($result[0]['last_vl_date_failure'])!='' && trim($result[0]['last_vl_date_failure'])!='0000-00-00'){
 $result[0]['last_vl_date_failure']=$general->humanDateFormat($result[0]['last_vl_date_failure']);
}else{
 $result[0]['last_vl_date_failure']='';
}

if(isset($result[0]['sample_tested_datetime']) && trim($result[0]['sample_tested_datetime'])!='' && trim($result[0]['sample_tested_datetime'])!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_tested_datetime']);
 $result[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_tested_datetime']='';
}

if(isset($result[0]['sample_received_at_vl_lab_datetime']) && trim($result[0]['sample_received_at_vl_lab_datetime'])!='' && trim($result[0]['sample_received_at_vl_lab_datetime'])!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_received_at_vl_lab_datetime']);
 $result[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_received_at_vl_lab_datetime']='';
}

if(isset($result[0]['result_dispatched_datetime']) && trim($result[0]['result_dispatched_datetime'])!='' && trim($result[0]['result_dispatched_datetime'])!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['result_dispatched_datetime']);
 $result[0]['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['result_dispatched_datetime']='';
}

if(isset($result[0]['result_reviewed_datetime']) && trim($result[0]['result_reviewed_datetime'])!='' && trim($result[0]['result_reviewed_datetime'])!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['result_reviewed_datetime']);
 $result[0]['result_reviewed_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['result_reviewed_datetime']= '';
}
//get test status values
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <style>
   #toogleResultDiv{
     
   }
   .ui_tpicker_second_label {
       display: none !important;
      }.ui_tpicker_second_slider {
       display: none !important;
      }.ui_tpicker_millisec_label {
       display: none !important;
      }.ui_tpicker_millisec_slider {
       display: none !important;
      }.ui_tpicker_microsec_label {
       display: none !important;
      }.ui_tpicker_microsec_slider {
       display: none !important;
      }.ui_tpicker_timezone_label {
       display: none !important;
      }.ui_tpicker_timezone {
       display: none !important;
      }.ui_tpicker_time_input{
       width:100%;
      }
   </style>
   <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
   <link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui-timepicker-addon.css" />
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Update VL Test Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Update VL Test Result</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <!--<div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"></div>
        </div>-->
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <div class="box-body">
              <div class="row">
                   <div class="col-md-12"><h4><a id="vlrfa" href="javascript:void(0);" onclick="formToggler('-');">VL Request Form Details <i class="fa fa-minus"></i></a></h4></div>
               </div>
             <div id="toogleFormDiv">
              <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Clinic Information</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Clinic/Health Center </label>
                        <div class="col-lg-7" style="font-style:italic;">
                            <?php echo ucwords($result[0]['facility_name']); ?>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Clinic Code </label>
                        <div class="col-lg-7" style="font-style:italic;">
                            <?php echo $result[0]['facility_code']; ?>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['facility_state']); ?>
                        </div>
                    </div>
                  </div>
                   
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['facility_hub_name']); ?>
                        </div>
                    </div>
                  </div> 
                </div>
              </div>
            </div>
            <!-- /.box-footer-->
              
         <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Patient Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="artNo" class="col-lg-4 control-label">Unique ART No. </label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_art_no']; ?>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleCode" class="col-lg-4 control-label">Sample Code </label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['sample_code']; ?>
                        </div>
                    </div>
                  </div>
                  
                   
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_other_id']; ?>
                        </div>
                    </div>
                   </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name </label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['patient_first_name']); ?>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                     <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_dob']; ?>
                        </div>
                    </div>
                  </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInYrs" class="col-lg-4 control-label">Age in years</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_age_in_years']; ?>
                        <br><p class="help-block"><small>If DOB Unkown</small></p>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInMtns" class="col-lg-4 control-label">Age in months</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['patient_age_in_months']; ?>
                        <br><p class="help-block"><small>If age < 1 year </small></p>
                        </div>
                    </div>
                  </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender" class="col-lg-4 control-label">Gender</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['patient_gender']); ?>
                        </div>
                    </div>
                  </div>
                  
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_mobile_number']; ?>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="location" class="col-lg-4 control-label">Location/District Code</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['patient_location']); ?>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
            <!-- /.box-footer-->
          </div>
               
               <div class="box box-danger ">
            <div class="box-header with-border">
              <h3 class="box-title">Sample Information </h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Sample Collected On</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['sample_collection_date']; ?>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Sample Type </label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['sample_name']); ?>
                        </div>
                    </div>
                  </div>                       
                </div>
            </div>
            <!-- /.box-footer-->
          </div>
                
                <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Treatment Information</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatPeriod" class="col-lg-4 control-label">How long has this patient been on treatment ?</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['treatment_initiation']; ?>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiated On</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['treatment_initiated_date']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['art_code']; ?>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Current Regimen Initiated On</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['date_of_initiation_of_current_regimen']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <div class="form-group">
                        <label for="treatmentDetails" class="col-lg-2 control-label">Which line of treatment is Patient on ?</label>
                        <div class="col-lg-10" style="font-style:italic;">
                           <?php echo ucwords($result[0]['treatment_details']); ?>
                        </div>
                    </div>
                  </div>    
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="pregYes" class="col-lg-4 control-label">Is Patient Pregnant ?</label>
                        <div class="col-lg-7" style="font-style:italic;">                        
                            <?php echo ucfirst($result[0]['is_patient_pregnant']); ?>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="arcNo" class="col-lg-4 control-label">If Pregnant, ARC No.</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['patient_anc_no']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="breastfeeding" class="col-lg-4 control-label">Is Patient Breastfeeding?</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucfirst($result[0]['is_patient_breastfeeding']); ?>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo $result[0]['arv_adherance_percentage']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
            </div>
            <!-- /.box-footer-->
          </div>
               
                
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Indication for viral load testing</h3>
              <small>(Please tick one):(To be completed by clinician)</small>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="control-label">
                               <?php
                               if($result[0]['last_vl_date_routine']!='' || $result[0]['last_vl_result_routine']!='' || $result[0]['last_vl_sample_type_routine']!=''){
                               ?>
                                 <strong>Routine Monitoring</strong>
                               <?php } elseif($result[0]['last_vl_date_failure_ac']!='' || $result[0]['last_vl_result_failure_ac']!='' || $result[0]['last_vl_sample_type_failure_ac']!='') {?>
                                 <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                               <?php } elseif($result[0]['last_vl_date_failure']!='' || $result[0]['last_vl_result_failure']!='' || $result[0]['last_vl_sample_type_failure']!='') { ?>
                                 <strong>Suspect Treatment Failure</strong>
                               <?php } ?>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php
                 if($result[0]['last_vl_date_routine']!='' || $result[0]['last_vl_result_routine']!='' || $result[0]['last_vl_sample_type_routine']!=''){
                 ?>
                  <div class="row">
                     <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-lg-4 control-label">Last VL Date</label>
                          <div class="col-lg-7" style="font-style:italic;">
                             <?php echo $result[0]['last_vl_date_routine']; ?>
                          </div>
                      </div>
                    </div>
                     <div class="col-md-4">
                      <div class="form-group">
                          <label for="rmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                          <div class="col-lg-7" style="font-style:italic;">
                            <?php echo $result[0]['last_vl_result_routine']; ?>
                          </div>
                      </div>
                    </div>
                     <div class="col-md-4">
                      <div class="form-group">
                          <label for="rmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                          <div class="col-lg-7" style="font-style:italic;">
                             <?php echo ucwords($result[0]['snrm']); ?>
                          </div>
                      </div>
                    </div>                   
                  </div>
                <?php } elseif($result[0]['last_vl_date_failure_ac']!='' || $result[0]['last_vl_result_failure_ac']!='' || $result[0]['last_vl_sample_type_failure_ac']!=''){ ?>
                    <div class="row">
                      <div class="col-md-4">
                       <div class="form-group">
                           <label class="col-lg-4 control-label">Last VL Date</label>
                           <div class="col-lg-7" style="font-style:italic;">
                              <?php echo $result[0]['last_vl_date_failure_ac']; ?>
                           </div>
                       </div>
                     </div>
                      <div class="col-md-4">
                       <div class="form-group">
                           <label for="repeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                           <div class="col-lg-7" style="font-style:italic;">
                              <?php echo $result[0]['last_vl_result_failure_ac']; ?>
                           </div>
                       </div>
                     </div>
                      <div class="col-md-4">
                       <div class="form-group">
                           <label for="repeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                           <div class="col-lg-7" style="font-style:italic;">
                              <?php echo ucwords($result[0]['sntfac']); ?>
                           </div>
                       </div>
                     </div>                   
                   </div>
                <?php } elseif($result[0]['last_vl_date_failure']!='' || $result[0]['last_vl_result_failure']!='' || $result[0]['last_vl_sample_type_failure']!=''){
                 ?>
                   <div class="row">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['last_vl_date_failure']; ?>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['last_vl_result_failure']; ?>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7" style="font-style:italic;">
                           <?php echo ucwords($result[0]['snstf']); ?>
                        </div>
                    </div>
                  </div>                   
                </div>
                <?php } ?>
            </div>
            <!-- /.box-footer-->
          </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestClinician" class="col-lg-4 control-label">Request Clinician</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['request_clinician_name']; ?>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="clinicianPhone" class="col-lg-4 control-label">Phone No.</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['request_clinician_phone_number']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestDate" class="col-lg-4 control-label">Sample Testing Date</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo $result[0]['sample_tested_datetime']; ?>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlFocalPerson" class="col-lg-4 control-label">VL Focal Person</label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo ucwords($result[0]['vl_focal_person']); ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlPhoneNumber" class="col-lg-4 control-label">VL Focal Person Phone Number</label>
                        <div class="col-lg-7" style="font-style:italic;">
                         <?php echo $result[0]['vl_focal_person_phone_number']; ?>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="emailHf" class="col-lg-4 control-label">Email for HF</label>
                        <div class="col-lg-7" style="font-style:italic;">
                         <?php echo $result[0]['facility_emails']; ?>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="rejection" class="col-lg-4 control-label">Rejected by Clinic </label>
                        <div class="col-lg-7" style="font-style:italic;">
                          <?php echo ucfirst($result[0]['is_sample_rejected']); ?>
                        </div>
                    </div>
                  </div>                                    
                </div>
             </div>
             
             <div class="row">
                <div class="col-md-12"><h4><a id="lra" href="javascript:void(0);" onclick="resultToggler('-');">Lab/Result Details <i class="fa fa-minus"></i></a></h4></div>
             </div>
             
            <div id="toogleResultDiv" class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Lab Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <form class="form-horizontal" method='post' name='updateVlTest' id='updateVlTest' autocomplete="off"  action="updateVlTestResultHelper.php">
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labName" class="col-lg-4 control-label">Lab Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labName" name="labName" placeholder="Enter Lab Name" title="Please enter lab name" value="<?php echo $result[0]['lab_name']; ?>"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labContactPerson" class="col-lg-4 control-label">Lab Contact Person </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Enter Lab Contact Person Name" title="Please enter lab contact person name" value="<?php echo $result[0]['lab_contact_person']; ?>"/>
                        </div>
                    </div>
                   </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labPhoneNo" class="col-lg-4 control-label">Phone Number </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labPhoneNo" name="labPhoneNo" placeholder="Enter Lab Phone No." title="Please enter lab phone no." value="<?php echo $result[0]['lab_phone_number']; ?>"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Sample Received at Testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Select Sample Received Date" title="Select sample received date" value="<?php echo $result[0]['sample_received_at_vl_lab_datetime']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Lab Sample Testing Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleTestedOn" name="sampleTestedOn" placeholder="Select Sample Testing Date" title="Select sample testing date" value="<?php echo $result[0]['sample_tested_datetime']; ?>"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Results Dispatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Select Result Dispatched Date" title="Select result dispatched date" value="<?php echo $result[0]['result_dispatched_datetime']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <!--<div class="col-md-6">
                    <div class="form-group">
                        <label for="reviewedBy" class="col-lg-4 control-label">Reviewed By</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Enter Reviewed By Name" title="Please enter reviewed by name" value="< ?php echo $result[0]['result_reviewed_by']; ?>"/>
                        </div>
                    </div>
                  </div>-->
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Reviewed Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="reviewedOn" name="reviewedOn" placeholder="Select Reviewed Date" title="Select reviewed date" value="<?php echo $result[0]['result_reviewed_datetime']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12"><h4>Result Details</h4></div>
                </div>
                 
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="logValue" class="col-lg-4 control-label">Log Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="logValue" name="logValue" placeholder="Enter Log Value" title="Please enter log value" value="<?php echo $result[0]['result_value_log']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="absoluteValue" class="col-lg-4 control-label">Absolute Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="absoluteValue" name="absoluteValue" placeholder="Enter Absolute Value" title="Please enter absolute value" value="<?php echo $result[0]['result_value_absolute']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="textValue" class="col-lg-4 control-label">Text Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="textValue" name="textValue" placeholder="Enter Text Value" title="Please enter text value" value="<?php echo $result[0]['result_value_text']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="result" class="col-lg-4 control-label">Result</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="result" name="result" placeholder="Enter Result" title="Please enter result" value="<?php echo $result[0]['result']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                 <br>
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="comments" class="col-lg-4 control-label">Comments</label>
                        <div class="col-lg-7">
                         <textarea class="form-control" id="comments" name="comments" row="4" placeholder="Enter Comments" title="Please enter comments"><?php echo $result[0]['approver_comments']; ?></textarea>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status</label>
                        <div class="col-lg-7">
                         <select class="form-control" id="status" name="status" title="Please select test status">
			    <?php
                            foreach($tsResult as $status){
                             ?>
                             <option value="<?php echo $status['status_id']; ?>" <?php echo ($status['status_id']==$result[0]['result_status']) ? 'selected="selected"':'';?>><?php echo ucwords($status['status_name']);?></option>
                             <?php
                            }
                            ?>
			  </select>
                        </div>
                    </div>
                  </div>
                 </div>
                 <div class="box-footer">
                <input type="hidden" id="treamentId" name="treamentId" value="<?php echo $id; ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <a href="javascript:void(0);" onclick="window.history.go(-1)" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
            </div>
          </div>
        </div>
        <!-- /.box-body -->
        <!-- /.row -->
        </div>
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <script type="text/javascript">
   $(document).ready(function() {
     
     $('#sampleReceivedOn').datetimepicker({
         changeMonth: true,
         changeYear: true,
         dateFormat: 'dd-M-yy',
         timeFormat: "HH:mm",
         onChangeMonthYear: function(year, month, widget) {
            setTimeout(function() {
               $('.ui-datepicker-calendar').show();
            });
    	},
         onSelect: function(selectedDate) {
             $('#sampleTestedOn').val("");
             $('#resultDispatchedOn').val("");
             $('#reviewedOn').val("");
             $("#sampleTestedOn").datepicker("option", "minDateTime", new Date($(this).datepicker('getDate')));
             $("#sampleTestedOn").datepicker("option", "minDate", selectedDate);
         },
         yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
    	$('.ui-datepicker-calendar').show();
    });
     $('#sampleTestedOn').datetimepicker({
         changeMonth: true,
         changeYear: true,
         dateFormat: 'dd-M-yy',
         timeFormat: "HH:mm",
         onChangeMonthYear: function(year, month, widget) {
            setTimeout(function() {
               $('.ui-datepicker-calendar').show();
            });
    	},
         onSelect: function(selectedDate) {
             $('#resultDispatchedOn').val("");
             $('#reviewedOn').val("");
             $("#resultDispatchedOn").datepicker("option", "minDateTime", new Date($(this).datepicker('getDate')));
             $("#resultDispatchedOn").datepicker("option", "minDate", selectedDate);
         },
         yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
    	$('.ui-datepicker-calendar').show();
    });
     $('#resultDispatchedOn').datetimepicker({
         changeMonth: true,
         changeYear: true,
         dateFormat: 'dd-M-yy',
         timeFormat: "HH:mm",
         onChangeMonthYear: function(year, month, widget) {
            setTimeout(function() {
               $('.ui-datepicker-calendar').show();
            });
    	},
         onSelect: function(selectedDate) {
             $('#reviewedOn').val("");
             $("#reviewedOn").datepicker("option", "minDateTime", new Date($(this).datepicker('getDate')));
             $("#reviewedOn").datepicker("option", "minDate", selectedDate);
         },
         yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
    	$('.ui-datepicker-calendar').show();
    });
     $('#reviewedOn').datetimepicker({
         changeMonth: true,
         changeYear: true,
         dateFormat: 'dd-M-yy',
         timeFormat: "HH:mm",
         onChangeMonthYear: function(year, month, widget) {
            setTimeout(function() {
               $('.ui-datepicker-calendar').show();
            });
    	},
         yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
    	$('.ui-datepicker-calendar').show();
    });
     $('.ui-datepicker-calendar').show();
   });
   function validateNow(){
    flag = deforayValidator.init({
        formId: 'updateVlTest'
    });
    if(flag){
      document.getElementById('updateVlTest').submit();
    }
  }
    function resultToggler(symbol) {
      if(symbol == "+"){
          $("#toogleResultDiv").slideToggle();
          $("#lra").html('Lab/Result Details <i class="fa fa-minus"></i>');
          $("#lra").attr("onclick", "resultToggler('-')");
      }else{
        $("#toogleResultDiv").slideToggle();
        $("#lra").html('Lab/Result Details <i class="fa fa-plus"></i>');
        $("#lra").attr("onclick", "resultToggler('+')");
      }
    }
    
    function formToggler(symbol){
      if(symbol == "-"){
          $("#toogleFormDiv").slideToggle();
          $("#vlrfa").html('VL Request Form Details <i class="fa fa-plus"></i>');
          $("#vlrfa").attr("onclick", "formToggler('+')");
      }else{
        $("#toogleFormDiv").slideToggle();
        $("#vlrfa").html('VL Request Form Details <i class="fa fa-minus"></i>');
        $("#vlrfa").attr("onclick", "formToggler('-')");
      }
    }
  </script>
 <?php
 //include('../footer.php');
 ?>
