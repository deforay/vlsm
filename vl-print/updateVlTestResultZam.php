<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}

//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
//get vltest reason details
$testRQuery="SELECT * FROM r_vl_test_reasons";
$testReason = $db->rawQuery($testRQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='zam'";
$aResult=$db->query($aQuery);

$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);

$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);

$vlQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$vlQueryInfo=$db->query($vlQuery);
//facility details
$facilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
$facilityResult=$db->query($facilityQuery);
if(isset($facilityResult[0]['state']) && $facilityResult[0]['state']!=''){
}else{
  $facilityResult[0]['state'] = 0;
}
$stateName = $facilityResult[0]['state'];
$stateQuery="SELECT * from province_details where province_name='".$stateName."'";
$stateResult=$db->query($stateQuery);
if(isset($stateResult[0]['province_code']) && $stateResult[0]['province_code']!=''){
}else{
  $stateResult[0]['province_code'] = 0;
}
//district details
$districtQuery="SELECT DISTINCT district from facility_details where state='".$stateName."'";
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
if(isset($vlQueryInfo[0]['lab_tested_date']) && trim($vlQueryInfo[0]['lab_tested_date'])!='' && trim($vlQueryInfo[0]['lab_tested_date'])!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$vlQueryInfo[0]['lab_tested_date']);
 $vlQueryInfo[0]['lab_tested_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['lab_tested_date']='';
}
if(isset($vlQueryInfo[0]['date_sample_received_at_testing_lab']) && trim($vlQueryInfo[0]['date_sample_received_at_testing_lab'])!='' && $vlQueryInfo[0]['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['date_sample_received_at_testing_lab']);
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']='';
}

$disable = "disabled = 'disabled'";
?>
<style>
    :disabled {background:white;}
 .form-control{background: #fff !important;}
  .ui_tpicker_second_label {
       display: none !important;
      }
      .ui_tpicker_second_slider {
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
        <li><a href="../dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
            <form class="form-inline" method='post'  name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="updateVlRequestHelperZam.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample Code <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Enter Form Serial No." title="" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" <?php echo $disable;?> />
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;"  <?php echo $disable;?>>
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;"  <?php echo $disable;?>>
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($districtResult as $districtName){
                              ?>
                              <option value="<?php echo $districtName['district'];?>" <?php echo ($facilityResult[0]['district']==$districtName['district'])?"selected='selected'":""?>><?php echo ucwords($districtName['district']);?></option>
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
                    <label for="clinicName">Clinic Name <span class="mandatory">*</span></label>
                      <select class="form-control isRequired" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;"  <?php echo $disable;?>>
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
                    <input type="text" class="form-control  " name="clinicianName" id="clinicianName" placeholder="Enter Clinician Name" style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_contact_person'];?>"  <?php echo $disable;?>>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="sampleCollectionDate">Sample Collection Date <span class="mandatory">*</span></label>
                    <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $vlQueryInfo[0]['sample_collection_date'];?>"  <?php echo $disable;?>>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="">Sample Received Date</label>
                    <input type="text" class="form-control" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" placeholder="Sample Received Date" value="<?php echo $vlQueryInfo[0]['date_sample_received_at_testing_lab']; ?>"  <?php echo $disable;?>>
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:16%">
                        <label for="patientFname">Patient First Name   <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired " name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_name'];?>"  <?php echo $disable;?>>
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['surname'];?>" <?php echo $disable;?> >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender">Gender &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='male')?"checked='checked'":""?> <?php echo $disable;?>> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='female')?"checked='checked'":""?> <?php echo $disable;?>> Female
                          </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='not_recorded')?"checked='checked'":""?> <?php echo $disable;?>> Not Recorded
                          </label>
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_dob'];?>"  <?php echo $disable;?> >
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_yrs'];?>" <?php echo $disable;?> >
                        </td>
                      </tr>
                      <tr>
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_mnts'];?>" <?php echo $disable;?>>
                        </td>
                        <td class="femaleElements"><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements">
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?> <?php echo $disable;?>> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='no')?"checked='checked'":""?>  <?php echo $disable;?>> No
                          </label>
                        </td>
                        
                         <td colspan="2" class="femaleElements"><label for="breastfeeding">Is Patient Breastfeeding?</label>
                        
                          <label class="radio-inline">
                             <input type="radio" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='yes')?"checked='checked'":""?> <?php echo $disable;?> >Yes
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='no')?"checked='checked'":""?>  <?php echo $disable;?>>No
                          </label>
                        </td>
                      </tr>
                      
                      <tr>
                        <td><label for="patientArtNo">Patient OI/ART Number</label></td>
                        <td>
                          <input type="text" class="form-control" name="patientArtNo" id="patientArtNo" placeholder="Patient OI/ART Number" title="Enter Patient OI/ART Number" style="width:100%;" value="<?php echo $vlQueryInfo[0]['art_no'];?>" <?php echo $disable;?> >
                        </td>
                        <td><label for="dateOfArt">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiation" title="Date Of ART Initiation"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen'];?>"  <?php echo $disable;?>>
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                            <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" <?php echo $disable;?>>
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
                          <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New Art Regimen" title="New Art Regimen" style="width:100%;" >
                        </td>
                        <td><label for="patientPhoneNumber" class="">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_phone_number'];?>" <?php echo $disable;?> /></td>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_date'];?>"  <?php echo $disable;?> /></td>
                      </tr>
                      
                      <tr>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_result'];?>" <?php echo $disable;?>/></td>
                        <td><label for="vlTestReason">Reason VL Requested</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;" <?php echo $disable;?>>
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($testReason as $reason){
                              ?>
                              <option value="<?php echo $reason['test_reason_name'];?>"<?php echo ($vlQueryInfo[0]['vl_test_reason']==$reason['test_reason_name'])?"selected='selected'":""?>><?php echo ucwords($reason['test_reason_name']);?></option>
                              <?php
                            }
                            ?>
                           </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="enhanceSession">Enhanced Sessions</label></td>
                        <td>
                          <select name="enhanceSession" id="enhanceSession" class="form-control" title="Please choose enhance session" <?php echo $disable;?>>
                          <option value=""> -- Select -- </option>
                          <option value="1"<?php echo ($vlQueryInfo[0]['enhance_session']=='1')?"selected='selected'":""?>>1</option>
                          <option value="2"<?php echo ($vlQueryInfo[0]['enhance_session']=='2')?"selected='selected'":""?>>2</option>
                          <option value="3"<?php echo ($vlQueryInfo[0]['enhance_session']=='3')?"selected='selected'":""?>>3</option>
                          <option value=">3"<?php echo ($vlQueryInfo[0]['enhance_session']=='>3')?"selected='selected'":""?>> > 3</option>
                         </select>
                        </td>
                        <td colspan="3"><label>After Enhanced Adherence Poor Adherence was identified</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="poorAdherence" name="poorAdherence" value="yes" title="After Enhanced Adherence Poor Adherence was identified" <?php echo ($vlQueryInfo[0]['poor_adherence']=='yes')?"checked='checked'":""?>  <?php echo $disable;?> > Yes
                          </label>
                          <label class="radio-inline noResult" style="margin-left: 0px;">
                                  <input type="radio" class="" id="poorAdherence1" name="poorAdherence" value="no" title="After Enhanced Adherence Poor Adherence was identified" <?php echo ($vlQueryInfo[0]['poor_adherence']=='no')?"checked='checked'":""?> <?php echo $disable;?>> No
                          </label>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-body">
                    <div class="box-header with-border">
                    <h3 class="box-title">FOR LABORATORY USE ONLY</h3>
                    
                    </div>
                    <table class="table">
                      <tr>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                            <option value="">-- Select --</option>
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
                                 <option value="<?php echo $name['sample_id'];?>"<?php echo ($vlQueryInfo[0]['sample_id']==$name['sample_id'])?"selected='selected'":""?>><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
                          <?php } ?>
                        </td>
                        <td><label for="testMethods">Test Methods</label></td>
                        <td>
                          <select name="testMethods" id="testMethods" class="form-control " title="Please choose test methods">
                          <option value=""> -- Select -- </option>
                          <option value="individual"<?php echo ($vlQueryInfo[0]['test_methods']=='individual')?"selected='selected'":""?>>Individual</option>
                          <option value="minipool"<?php echo ($vlQueryInfo[0]['test_methods']=='minipool')?"selected='selected'":""?>>Minipool</option>
                          <option value="other pooling algorithm"<?php echo ($vlQueryInfo[0]['test_methods']=='other pooling algorithm')?"selected='selected'":""?>>Other Pooling Algorithm</option>
                         </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control " id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_tested_date'];?>" onchange="checkSampleTestingDate();" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" value="<?php echo $vlQueryInfo[0]['absolute_value'];?>" /></td>
                        
                      </tr>
                      <tr class="noResult">
                        <td><label>If no result</label></td>
                        <td>
                          <label class="radio-inline noResult">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result" > Sample Rejected
                          </label>
                          <label class="radio-inline noResult" style="margin-left: 0px;">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result" > Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Approved By</label></td>
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
                        <td colspan="5"><textarea class="form-control" name="labComments" id="labComments" title="Enter lab comments" style="width:100%"> <?php echo $vlQueryInfo[0]['comments'];?></textarea></td>
                      </tr>
                      <tr>
                        <td><label for="status">Status</label></td>
                        <td colspan="5">
                         <select class="form-control" id="status" name="status" title="Please select test status">
                            <option value="">-- Select --</option>
                            <option value="7"<?php echo (7==$vlQueryInfo[0]['status']) ? 'selected="selected"':'';?>>Accepted</option>
 			    <option value="4"<?php echo (4==$vlQueryInfo[0]['status']) ? 'selected="selected"':'';?>>Rejected</option>
			  </select>
                        </td>
                       </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="vlSampleId" id="vlSampleId" value="<?php echo $vlQueryInfo[0]['vl_sample_id'];?>"/>
                <a href="vlTestResult.php" class="btn btn-default"> Cancel</a>
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
  $(document).ready(function() {
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
  });
    function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
    }
    function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback)
    {
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
    function checkSampleTestingDate(){
      var sampleCollectionDate = $("#sampleCollectionDate").val();
      var sampleTestingDate = $("#sampleTestingDateAtLab").val();
      if($.trim(sampleCollectionDate)!= '' && $.trim(sampleTestingDate)!= '') {
        //Set sample coll. date
        splitSampleCollDateTime = sampleCollectionDate.split(" ");
        splitSampleCollDate = splitSampleCollDateTime[0].split("-");
        var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2]+", "+splitSampleCollDate[0]);
        var monthDigit = sampleCollOn.getMonth();
        var smplCollYear = splitSampleCollDate[2];
        var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplCollMonth = (smplCollMonth<10) ? '0'+smplCollMonth: smplCollMonth;
        var smplCollDate = splitSampleCollDate[0];
        sampleCollDateTime = smplCollYear+"-"+smplCollMonth+"-"+smplCollDate+" "+splitSampleCollDateTime[1]+":00";
        //Set sample testing date
        splitSampleTestedDateTime = sampleTestingDate.split(" ");
        splitSampleTestedDate = splitSampleTestedDateTime[0].split("-");
        var sampleTestingOn = new Date(splitSampleTestedDate[1] + splitSampleTestedDate[2]+", "+splitSampleTestedDate[0]);
        var monthDigit = sampleTestingOn.getMonth();
        var smplTestingYear = splitSampleTestedDate[2];
        var smplTestingMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplTestingMonth = (smplTestingMonth<10) ? '0'+smplTestingMonth: smplTestingMonth;
        var smplTestingDate = splitSampleTestedDate[0];
        sampleTestingAtLabDateTime = smplTestingYear+"-"+smplTestingMonth+"-"+smplTestingDate+" "+splitSampleTestedDateTime[1]+":00";
        //Check diff
        if(moment(sampleCollDateTime).diff(moment(sampleTestingAtLabDateTime)) > 0) {
          alert("Sample Testing Date could not be earlier than Sample Collection Date!");
          $("#sampleTestingDateAtLab").val("");
        }
      }
    }
    
  </script>