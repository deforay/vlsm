<?php
ob_start();
//include('../header.php');
//include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
$vlQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$vlQueryInfo=$db->query($vlQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);

//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}

if($arr['sample_code']=='auto' || $arr['sample_code']=='alphanumeric'){
  $numeric = '';
}else{
  $numeric = 'checkNum';
}

//facility details
$facilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
$facilityResult=$db->query($facilityQuery);
if(!isset($facilityResult[0]['facility_state']) || $facilityResult[0]['facility_state']== ''){
  $facilityResult[0]['facility_state'] = 0;
}
$stateName = $facilityResult[0]['facility_state'];
$stateQuery="SELECT * from province_details where province_name='".$stateName."'";
$stateResult=$db->query($stateQuery);
if(!isset($stateResult[0]['province_code']) || $stateResult[0]['province_code'] == ''){
  $stateResult[0]['province_code'] = 0;
}
//district details
$districtQuery="SELECT * from facility_details where facility_state='".$stateName."'";
$districtResult=$db->query($districtQuery);

$province = '';
$province.="<option value=''> -- Select -- </option>";
            foreach($pdResult as $provinceName){
              $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
            }
            $facility = '';
            $facility.="<option value=''> -- Select -- </option>";
            foreach($fResult as $fDetails){
              $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
            }
            
            
if(isset($vlQueryInfo[0]['sample_collection_date']) && trim($vlQueryInfo[0]['sample_collection_date'])!='' && $vlQueryInfo[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_collection_date']);
 $vlQueryInfo[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_collection_date']='';
}
if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!='' && $vlQueryInfo[0]['patient_dob']!='0000-00-00'){
 $vlQueryInfo[0]['patient_dob']=$general->humanDateFormat($vlQueryInfo[0]['patient_dob']);
}else{
 $vlQueryInfo[0]['patient_dob']='';
}
if(isset($vlQueryInfo[0]['date_of_initiation_of_current_regimen']) && trim($vlQueryInfo[0]['date_of_initiation_of_current_regimen'])!='' && $vlQueryInfo[0]['date_of_initiation_of_current_regimen']!='0000-00-00'){
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($vlQueryInfo[0]['date_of_initiation_of_current_regimen']);
}else{
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']='';
}
if(isset($vlQueryInfo[0]['last_viral_load_date']) && trim($vlQueryInfo[0]['last_viral_load_date'])!='' && $vlQueryInfo[0]['last_viral_load_date']!='0000-00-00'){
 $vlQueryInfo[0]['last_viral_load_date']=$general->humanDateFormat($vlQueryInfo[0]['last_viral_load_date']);
}else{
 $vlQueryInfo[0]['last_viral_load_date']='';
}
if(isset($vlQueryInfo[0]['sample_tested_datetime']) && trim($vlQueryInfo[0]['sample_tested_datetime'])!='' && trim($vlQueryInfo[0]['sample_tested_datetime'])!='0000-00-00 00:00:00'){
 $sampleTestingDateLab = explode(" ",$vlQueryInfo[0]['sample_tested_datetime']);
 $vlQueryInfo[0]['sample_tested_datetime']=$general->humanDateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
}else{
 $vlQueryInfo[0]['sample_tested_datetime']='';
}
if(isset($vlQueryInfo[0]['sample_received_at_vl_lab_datetime']) && trim($vlQueryInfo[0]['sample_received_at_vl_lab_datetime'])!='' && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_received_at_vl_lab_datetime']);
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']='';
}
$disable = "disabled = 'disabled'";
//get test status values
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
?>
<style>
 :disabled {background:white;}
 .form-control{background: #fff !important;}
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
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Edit Vl Request</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method='post'  name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="updateVlRequestHelperZm.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="serialNo">Form Serial No <span class="mandatory">*</span></label>
                          <input type="text" class="form-control serialNo checkNum removeValue" id="" <?php echo $disable; ?> name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "vl_sample_id##".$id;?>','This serial number already exists.Try another number',null)"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3 col-sm-offset-2 col-md-offset-2" style="padding:10px;">
                        <div class="form-group">
                        <label for="urgency">Urgency&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <label class="radio-inline">
                             <input type="radio" class="" id="urgencyNormal" name="urgency" value="normal" <?php echo $disable; ?> title="Please check urgency" <?php echo ($vlQueryInfo[0]['test_urgency']=='normal')?"checked='checked'":""?>> Normal
                        </label>
                        <label class="radio-inline">
                             <input type="radio" class=" " id="urgencyUrgent" name="urgency" value="urgent" <?php echo $disable; ?> title="Please check urgency" <?php echo ($vlQueryInfo[0]['test_urgency']=='urgent')?"checked='checked'":""?>> Urgent
                        </label>
                        </div>
                      </div>

                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province</label>
                          <select class="form-control" name="province" id="province" title="Please choose province" <?php echo $disable; ?> style="width:100%;" onchange="getfacilityDetails(this);">
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['facility_state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  </label>
                          <select class="form-control" name="district" id="district" title="Please choose district" <?php echo $disable; ?> style="width:100%;">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($districtResult as $districtName){
                              ?>
                              <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($facilityResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                    </div>
                
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicName">Clinic Name <span class="mandatory">*</span> </label>
                      <select class="form-control " id="clinicName" name="clinicName" title="Please select clinic name" <?php echo $disable; ?> style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                        <option value=''> -- Select -- </option>
			<?php foreach($fResult as $fDetails){ ?>
                        <option value="<?php echo $fDetails['facility_id'];?>" <?php echo ($vlQueryInfo[0]['facility_id']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
                        <?php } ?>
		      </select>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicianName">Clinician Name </label>
                    <input type="text" class="form-control  " name="clinicianName" id="clinicianName" placeholder="Enter Clinician Name" <?php echo $disable; ?> style="width:100%;"  value="<?php echo $vlQueryInfo[0]['lab_contact_person'];?>">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="sampleCollectionDate">Sample Collection Date</label>
                    <input type="text" class="form-control" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" <?php echo $disable; ?> placeholder="Sample Collection Date" value="<?php echo $vlQueryInfo[0]['sample_collection_date'];?>">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="">Sample Received Date</label>
                    <input type="text" class="form-control" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" <?php echo $disable; ?> placeholder="Sample Received Date" value="<?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control" name="collectedBy" id="collectedBy" style="width:100%;" <?php echo $disable; ?> title="Enter Collected by (Initials)" placeholder="Enter Collected by (Initials)" value="<?php echo $vlQueryInfo[0]['sample_collected_by'];?>">
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <!--<td style="width:18%">
                        <label for="sampleCode">Sample Code  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="sampleCode" id="sampleCode" placeholder="Sample Code" title="Enter Sample Code"  style="width:100%;" value="< ?php echo $vlQueryInfo[0]['sample_code'];?>">
                        </td>-->
                        <td style="width:16%">
                        <label for="patientFname">Patient First Name  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="patientFname" id="patientFname" <?php echo $disable; ?> placeholder="First Name" title="Enter First Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_first_name'];?>" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Last Name </label>
                        </td>
                        <td style="width:18%">
                          <input type="text" class="form-control" name="surName" id="surName" placeholder="Last Name" <?php echo $disable; ?> title="Enter Last Name"  style="width:100%;"  value="<?php echo $vlQueryInfo[0]['patient_last_name'];?>" >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender">Gender &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['patient_gender']=='male')?"checked='checked'":""?>> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['patient_gender']=='female')?"checked='checked'":""?>> Female
                          </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['patient_gender']=='not_recorded')?"checked='checked'":""?>> Not Recorded
                          </label>
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_dob'];?>" onchange="getDateOfBirth('ch');return false;">
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter age in years" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_age_in_years'];?>">
                          
                        </td>
                      </tr>
                      <tr>
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_age_in_months'];?>" >
                        </td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['patient_gender'] == 'male')?'style="display:none;"':''; ?>><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['patient_gender'] == 'male')?'style="display:none;"':''; ?>>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" <?php echo $disable; ?> title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?> > Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo $disable; ?> title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='no')?"checked='checked'":""?> > No
                          </label>
                        </td>
                        
                        <td colspan="2"  class="femaleElements" <?php echo($vlQueryInfo[0]['patient_gender'] == 'male')?'style="display:none;"':''; ?>><label for="breastfeeding">Is Patient Breastfeeding?</label>
                        
                          <label class="radio-inline">
                             <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" <?php echo $disable; ?> title="Is Patient Breastfeeding" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='yes')?"checked='checked'":""?> > Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo $disable; ?> title="Is Patient Breastfeeding" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='no')?"checked='checked'":""?>> No
                       </label>
                        </td>
                      </tr>
                      
                      <tr>
                        <td><label for="patientArtNo">Patient OI/ART Number</label></td>
                        <td>
                          <input type="text" class="form-control" name="patientArtNo" id="patientArtNo" <?php echo $disable; ?> placeholder="Patient OI/ART Number" title="Enter Patient OI/ART Number" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_art_no'];?>" >
                        </td>
                        <td><label for="dateOfArt">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" <?php echo $disable; ?> placeholder="Date Of ART Initiation" title="Date Of ART Initiation" style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen'];?>" >
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                            <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" <?php echo $disable; ?> title="Please choose ART Regimen" onchange="checkValue();">
                         <option value=""> -- Select -- </option>
                         <?php
                         foreach($aResult as $parentRow){
                         ?>
                          <option value="<?php echo $parentRow['art_code']; ?>"<?php echo ($vlQueryInfo[0]['current_regimen']==$parentRow['art_code'])?"selected='selected'":""?>><?php echo $parentRow['art_code']; ?></option>
                         <?php
                         }
                         ?>
                         <option value="other">Other</option>
                        </select>
                        </td>
                      </tr>
                      <tr>
                        <td class="newArtRegimen" style="display: none;"><label for="newArtRegimen">New ART Regimen</label><span class="mandatory">*</span></td>
                        <td class="newArtRegimen" style="display: none;">
                          <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" <?php echo $disable; ?> placeholder="New Art Regimen" title="New Art Regimen" style="width:100%;" >
                        </td>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" <?php echo $disable; ?> title="Patient consent to receive SMS" <?php echo ($vlQueryInfo[0]['consent_to_receive_sms']=='yes')?"checked='checked'":""?> onclick="checkPatientReceivesms(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" <?php echo $disable; ?> title="Patient consent to receive SMS" <?php echo ($vlQueryInfo[0]['consent_to_receive_sms']=='no')?"checked='checked'":""?> onclick="checkPatientReceivesms(this.value);"> No
                          </label>
                        </td>
                        <td><label for="patientPhoneNumber" class="">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" readonly="readonly" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_mobile_number'];?>" /></td>
                      </tr>
                      <tr class="newArtRegimen" style="display: none;">
                        
                      </tr>
                      <tr>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" <?php echo $disable; ?> placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_date'];?>" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" <?php echo $disable; ?> placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_result'];?>" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" <?php echo $disable; ?> placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;"  value="<?php echo $vlQueryInfo[0]['last_vl_result_in_log'];?>"/></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" <?php echo $disable; ?> style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <option value="routine_VL" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='routine_VL')?"selected='selected'":""?>>Routine VL</option>
                            <option value="confirmation_of_treatment_failure" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='confirmation_of_treatment_failure')?"selected='selected'":""?>>Confirmation Of Treatment Failure(repeat VL at 3M)</option>
                            <option value="clinical_failure" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='clinical_failure')?"selected='selected'":""?>>Clinical Failure</option>
                            <option value="immunological_failure" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='immunological_failure')?"selected='selected'":""?>>Immunological Failure</option>
                            <option value="single_drug_substitution" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='single_drug_substitution')?"selected='selected'":""?>>Single Drug Substitution</option>
                            <option value="pregnant_mother" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='pregnant_mother')?"selected='selected'":""?>>Pregnant Mother</option>
                            <option value="lactating_mother" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='lactating_mother')?"selected='selected'":""?>>Lactating Mother</option>
                            <option value="baseline_VL" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']=='baseline_VL')?"selected='selected'":""?>>Baseline VL</option>                            
                           </select>
                        </td>
                        <td></td>
                        <td>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-body">
                    <div class="box-header with-border">
                    <h3 class="box-title">FOR LABORATORY USE ONLY</h3>
                    <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('facilitiesModal.php?type=lab',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
                    </div>
                    <table class="table">
                      <tr>
                        <td><label for="serialNo">Form Serial No. <span class="mandatory">*</span></label></td>
                        <td><input type="text" class="form-control serialNo1 <?php echo $numeric;?> isRequired removeValue" id="" name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "vl_sample_id##".$id;?>','This serial number already exists.Try another number',null)" /></td>
                        <td><label for="sampleCode">Request Barcode <span class="mandatory">*</span></label></td>
                        <td>
                          <input type="text" class="form-control reqBarcode <?php echo $numeric;?> isRequired removeValue" name="reqBarcode" id="reqBarcode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "vl_sample_id##".$id;?>','This barcode already exists.Try another barcode',null)"/>
                          <!--<input type="hidden" class="form-control sampleCode" name="sampleCode" id="sampleCode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="< ?php echo $vlQueryInfo[0]['sample_code'];?>">-->
                        </td>
                        <td><label for="labId">Lab Name</label></td>
                        <td>
                          <select name="labId" id="labId" class="form-control" title="Please choose lab name">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($lResult as $labName){
                              ?>
                              <option value="<?php echo $labName['facility_id'];?>"<?php echo ($vlQueryInfo[0]['lab_id']==$labName['facility_id'])?"selected='selected'":""?>><?php echo ucwords($labName['facility_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="labNo">LAB No</label></td>
                        <td><input type="text" class="form-control checkNum" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_code'];?>" /></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                              <option value=""> -- Select -- </option>
                              <?php foreach($importResult as $mName) { ?>
                              <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"<?php echo ($vlQueryInfo[0]['vl_test_platform'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit']==$mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'])?"selected='selected'":""?>><?php echo $mName['machine_name'];?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                        <td><?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?><label for="specimenType">Specimen type</label><?php } ?></td>
                        <td>
                          <?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?>
                            <select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($sResult as $name){
                               ?>
                               <option value="<?php echo $name['sample_id'];?>" <?php echo ($vlQueryInfo[0]['sample_type']==$name['sample_id'])?"selected='selected'":""?>><?php echo ucwords($name['sample_name']);?></option>
                               <?php
                              }
                              ?>
                            </select>
                          <?php } ?>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control " id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['sample_tested_datetime'];?>" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" value="<?php echo $vlQueryInfo[0]['result_value_absolute'];?>" onchange="calculateLogValue(this)" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;" value="<?php echo $vlQueryInfo[0]['result_value_log'];?>" onchange="calculateLogValue(this)" /></td>
                      </tr>
                      <tr class="">
                        <td><label class="noResult">If no result</label></td>
                        <td>
                          <label class="radio-inline noResult">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result" <?php echo ($vlQueryInfo[0]['is_sample_rejected']=='sample_rejected')?"checked='checked'":""?>> Sample Rejected
                          </label>
                          <label class="radio-inline noResult" style="margin-left: 0px;">
                              <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result"<?php echo ($vlQueryInfo[0]['is_sample_rejected']=='technical_error')?"checked='checked'":""?>> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Reviewed By</label></td>
                        <!--<td><input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Enter Reviewed By" title="Please enter reviewed by" style="width:100%;" value="< ?php echo $vlQueryInfo[0]['result_reviewed_by'];?>" /></td>-->
                        <td>
                          <select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by">
                            <option value="">-- Select --</option>
                            <?php
                            foreach($userResult as $uName){
                              ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$vlQueryInfo[0]['result_reviewed_by'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                         </td>
                        <td><label>Approved By</label></td>
                        <!--<td><input type="text" class="form-control" id="approvedBy" name="approvedBy" placeholder="Enter Approved By" title="Please enter approved by" style="width:100%;"  value="< ?php echo $vlQueryInfo[0]['result_approved_by'];?>" /></td>-->
                        <td>
                         <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by">
                            <option value="">-- Select --</option>
                            <?php
                            foreach($userResult as $uName){
                              ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$vlQueryInfo[0]['result_approved_by'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                         </td>
                      </tr>
                      <tr>
                        <td><label for="labComments">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="5"><textarea class="form-control" name="labComments" id="labComments" title="Enter lab comments" style="width:100%"> <?php echo $vlQueryInfo[0]['approver_comments'];?></textarea></td>
                      </tr>
                      <?php if(isset($arr['testing_status']) && trim($arr['testing_status']) == "enabled"){ ?>
                       <tr>
                        <td><label for="status">Status</label></td>
                        <td colspan="5">
                         <select class="form-control" id="status" name="status" title="Please select test status">
                         <option value="">-- Select --</option>
                         <option value="7"<?php echo (7==$vlQueryInfo[0]['result_status']) ? 'selected="selected"':'';?>>Accepted</option>
 			 <option value="4"<?php echo (4==$vlQueryInfo[0]['result_status']) ? 'selected="selected"':'';?>>Rejected</option>
			</select>
                        </td>
                       </tr>
                      <?php } ?>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="treamentId" id="treamentId" value="<?php echo $vlQueryInfo[0]['vl_sample_id'];?>"/>
                <a href="vlResultApproval.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  
  
  <script type="text/javascript">
provinceName = true;
facilityName = true;
machineName = true;
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
            ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
    });
    if(flag){
      getMachineName();
      if(machineName){
        //check approve and review by name
        rBy = $("#reviewedBy").val();
        aBy = $("#approvedBy").val();
        globalValue = '<?php echo $arr["user_review_approve"];?>';
        if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='yes'){
          conf = confirm("Same person is reviewing and approving result!");
          if(conf){}else{
            return false;
          }
        }else if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='no'){
          alert("Same person is reviewing and approving result!");
          return false;
        }
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
      }
    }
  }
  function getfacilityDetails(obj)
  {
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
    if(pName!=''){
      if(provinceName){
      $.post("../includes/getFacilityForClinic.php", { pName : pName},
      function(data){
	  if(data != ""){
            details = data.split("###");
            $("#clinicName").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
	  }
      });
      }
      //$("#sampleCode").val(pNameVal[1]+sCode+sCodeKey);
      //$("#sampleCodeFormat").val(pNameVal[1]+sCode);
      //$("#sampleCodeKey").val(sCodeKey);
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
  }
  function getfacilityProvinceDetails(obj)
  {
     //check facility name
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(cName!='' && provinceName && facilityName){
        provinceName = false;
      }
    if(cName!='' && facilityName){
      $.post("../includes/getFacilityForClinic.php", { cName : cName},
      function(data){
	  if(data != ""){
            details = data.split("###");
            $("#province").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
	  }
      });
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
  }
  $(document).ready(function() {

    if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
      $(".noResult").show();
    }else{
      $(".noResult").hide();
    }    
    
$("#vlResult").bind("keyup change", function(e) {
    if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
      $(".noResult").show();
    }else{
      $( "#noResultRejected" ).prop( "checked", false );
      $( "#noResultError" ).prop( "checked", false );
      $(".noResult").hide();
    }
});
$("#vlLog").bind("keyup change", function(e) {
    if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
      $(".noResult").show();
    }else{
      $( "#noResultRejected" ).prop( "checked", false );
      $( "#noResultError" ).prop( "checked", false );
      $(".noResult").hide();
    }
});    
    
    
  $('.date').datepicker({
     changeMonth: true,
     changeYear: true,
     dateFormat: 'dd-M-yy',
     timeFormat: "hh:mm TT",
     yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    }).click(function(){
   	$('.ui-datepicker-calendar').show();
   });
   
   $('.date').mask('99-aaa-9999');
   $('#sampleCollectionDate,#sampleReceivedDate,#sampleTestingDateAtLab').mask('99-aaa-9999 99:99');
   
   $('#sampleCollectionDate,#sampleReceivedDate,#sampleTestingDateAtLab').datetimepicker({
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
     <?php
     if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!= ''){ ?>
       //getDateOfBirth("ld");
     <?php }
     ?>
  });
  $(".serialNo").keyup(function(){
    $(".serialNo1").val($(".serialNo").val());
    $(".reqBarcode").val($(".serialNo").val());
  });
  $(".serialNo1").keyup(function(){
    $(".serialNo").val($(".serialNo1").val());
    $(".reqBarcode").val($(".serialNo1").val());
  });
  $(".reqBarcode").keyup(function(){
    $(".serialNo").val($(".reqBarcode").val());
    $(".serialNo1").val($(".reqBarcode").val());
  });
  
    function setFacilityLabDetails(fDetails){
      $("#labId").val("");
      facilityArray = fDetails.split("##");
      $("#labId").val(facilityArray[0]);
    }
    function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback)
    {
      console.log(obj);
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                duplicateName=false;
                $(".removeValue").val('');
            }
        });
    }
    //check machine name and limit
    function getMachineName()
    {
      machineName = true;
      var mName = $("#testingPlatform").val();
      var absValue = $("#vlResult").val();
      if(mName!='' && absValue!='')
      {
        //split the value
        var result = mName.split("##");
        if(result[0]=='Roche' && absValue!='<20' && absValue!='>10000000'){
          var lowLimit = result[1];
          var highLimit = result[2];
            if(lowLimit!='' && lowLimit!=0 && parseInt(absValue) < 20){
              alert("Value outside machine detection limit");
              $("#vlResult").css('background-color', '#FFFF99');
              machineName = false;
            }else if(highLimit!='' && highLimit!=0 && parseInt(absValue) > 10000000){
              alert("Value outside machine detection limit");
              $("#vlResult").css('background-color', '#FFFF99');
              machineName  = false;
            }else{
              lessSign = absValue.split("<");
              greaterSign = absValue.split(">");
              if(lessSign.length>1)
              {
                if(parseInt(lessSign[1])<parseInt(lowLimit)){
                alert("Invalid value.Value Lesser than machine detection limit.");  
                }else if(parseInt(lessSign[1])>parseInt(highLimit))
                {
                  alert("Invalid value.Value Greater than machine detection limit.");  
                }else{
                  alert("Invalid value.");  
                }
                $("#vlResult").css('background-color', '#FFFF99');
                machineName = false;
              }else if(greaterSign.length>1)
              {
                if(parseInt(greaterSign[1])<parseInt(lowLimit)){
                alert("Invalid value.Value Lesser than machine detection limit.");  
                }else if(parseInt(greaterSign[1])>parseInt(highLimit))
                {
                  alert("Invalid value.Value Greater than machine detection limit.");  
                }else{
                  alert("Invalid value.");  
                }
                $("#vlResult").css('background-color', '#FFFF99');
                machineName = false;
              }
            }
        }
      }
    }
    
    function calculateLogValue(obj)
    {
      if(obj.id=="vlResult") {
        absValue = $("#vlResult").val();
        if(absValue!='' && absValue!=0){
          $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
        }
      }
      if(obj.id=="vlLog") {
        logValue = $("#vlLog").val();
        if(logValue!='' && logValue!=0){
          var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
          if(absVal!='Infinity'){
          $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
          }else{
            $("#vlResult").val('');
          }
        }
      }
    }
</script>
  
 <?php
 //include('../footer.php');
 ?>
