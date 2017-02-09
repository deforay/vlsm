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
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons";
$rejectionResult = $db->rawQuery($rejectionQuery);

$bQuery="SELECT * FROM batch_details";
$bResult = $db->rawQuery($bQuery);
//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);

$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='png'";
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
if(isset($vlQueryInfo[0]['failed_test_date']) && trim($vlQueryInfo[0]['failed_test_date'])!='' && trim($vlQueryInfo[0]['failed_test_date'])!='0000-00-00 00:00:00'){
  $failedDate=explode(" ",$vlQueryInfo[0]['failed_test_date']);
 $vlQueryInfo[0]['failed_test_date']=$general->humanDateFormat($failedDate[0])." ".$failedDate[1];
}else{
 $vlQueryInfo[0]['failed_test_date']='';
}
if(isset($vlQueryInfo[0]['date_sample_received_at_testing_lab']) && trim($vlQueryInfo[0]['date_sample_received_at_testing_lab'])!='' && $vlQueryInfo[0]['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['date_sample_received_at_testing_lab']);
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']='';
}
if(isset($vlQueryInfo[0]['art_cd_date']) && trim($vlQueryInfo[0]['art_cd_date'])!='' && $vlQueryInfo[0]['art_cd_date']!='0000-00-00'){
 $vlQueryInfo[0]['art_cd_date']=$general->humanDateFormat($vlQueryInfo[0]['art_cd_date']);
}else{
 $vlQueryInfo[0]['art_cd_date']='';
}
if(isset($vlQueryInfo[0]['qc_date']) && trim($vlQueryInfo[0]['qc_date'])!='' && $vlQueryInfo[0]['qc_date']!='0000-00-00'){
 $vlQueryInfo[0]['qc_date']=$general->humanDateFormat($vlQueryInfo[0]['qc_date']);
}else{
 $vlQueryInfo[0]['qc_date']='';
}
if(isset($vlQueryInfo[0]['report_date']) && trim($vlQueryInfo[0]['report_date'])!='' && $vlQueryInfo[0]['report_date']!='0000-00-00'){
 $vlQueryInfo[0]['report_date']=$general->humanDateFormat($vlQueryInfo[0]['report_date']);
}else{
 $vlQueryInfo[0]['report_date']='';
}
if(isset($vlQueryInfo[0]['clinic_date']) && trim($vlQueryInfo[0]['clinic_date'])!='' && $vlQueryInfo[0]['clinic_date']!='0000-00-00'){
 $vlQueryInfo[0]['clinic_date']=$general->humanDateFormat($vlQueryInfo[0]['clinic_date']);
}else{
 $vlQueryInfo[0]['clinic_date']='';
}
?>
<style>
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
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
      <ol class="breadcrumb">
        <li><a href="../dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Vl Request</li>
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
            <form class="form-inline" method='post'  name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="editVlRequestHelperPng.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample Code <span class="mandatory">*</span></label>
                          <input type="text" class="form-control sampleCode isRequired " id="sampleCode" name="sampleCode" placeholder="Enter Sample Code" title="Please enter sample code" style="width:100%;"  value="<?php echo $vlQueryInfo[0]['sample_code'];?>" onblur="checkNameValidation('vl_request_form','sample_code',this,'<?php echo "vl_sample_id##".$id;?>','This sample code already exists.Try another number',null)"/>
                        </div>
                      </div>
                    </div>
		    <br/>
                    <table class="table" style="width:100%">
		      <tr><td colspan="6" style="font-size: 18px; font-weight: bold;">Section 1: Clinic Information</td></tr>
                      <tr>
                        <td style="width:16%">
                        <label for="province">Province   <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
			    <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </td>
                        <td style="width:10%">
                        <label for="district">District  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($districtResult as $districtName){
                              ?>
                              <option value="<?php echo $districtName['district'];?>" <?php echo ($facilityResult[0]['district']==$districtName['district'])?"selected='selected'":""?>><?php echo ucwords($districtName['district']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                        <td style="width:10%">
                        <label for="facility">Facility/Ward  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <select class="form-control isRequired" id="wardData" name="wardData" title="Please select ward data" style="width:100%;">
			    <option value="">-- Select --</option>
			    <option value="inpatient" <?php echo ($vlQueryInfo[0]['ward']=="inpatient")?"selected='selected'":""?>>In-Patient</option>
			    <option value="outpatient" <?php echo ($vlQueryInfo[0]['ward']=="outpatient")?"selected='selected'":""?>>Out-Patient</option>
			    <option value="anc"<?php echo ($vlQueryInfo[0]['ward']=="anc")?"selected='selected'":""?>>ANC</option>
			  </select>
                        </td>
                      </tr>
                      <tr>
                        <td style="width:16%">
                        <label for="officerName">Requesting Medical Officer   <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired " name="officerName" id="officerName" placeholder="Officer Name" title="Enter Medical Officer Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_contact_person'];?>" >
                        </td>
                        <td style="width:10%">
                        <label for="telephone">Telephone  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired" name="telephone" id="telephone" placeholder="Telephone" title="Enter Telephone"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_phone_no'];?>" >
                        </td>
                        <td style="width:10%">
                        <label for="clinicDate">Date  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired date" name="clinicDate" id="clinicDate" placeholder="Date" title="Enter Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['clinic_date']; ?>"  >
                        </td>
                      </tr>
		      <tr><td colspan="6" style="font-size: 18px; font-weight: bold;">Section 2: Patient Information</td></tr>
                      <tr>
                        <td style="width:16%">
                        <label for="patientFname">First Name  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control " name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_name'];?>" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['surname'];?>" >
                        </td>
                        <td colspan="2">
                        <label for="gender">Gender &nbsp;&nbsp;</label>
			  <label class="radio-inline">
			   <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='male')?"checked='checked'":""?>> Male
			   </label>
			 <label class="radio-inline">
			   <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='female')?"checked='checked'":""?>> Female
			 </label>
			 <label class="radio-inline">
			   <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo ($vlQueryInfo[0]['gender']=='not_recorded')?"checked='checked'":""?>> Not Recorded
			 </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="dob">Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_dob'];?>"/>
                        </td>
                        <td><label for="clinicName">Clinic ID <span class="mandatory">*</span></label></td>
                        <td>
                          <select class="form-control isRequired" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
			    <option value=''> -- Select -- </option>
			    <?php foreach($fResult as $fDetails){ ?>
			    <option value="<?php echo $fDetails['facility_id'];?>" <?php echo ($vlQueryInfo[0]['facility_id']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
			    <?php } ?>
			  </select>
                        </td>
			<td></td><td></td>
                      </tr>
		      <tr><td colspan="6" style="font-size: 18px; font-weight: bold;">Section 3: ART Information</td></tr>
                      <tr>
                        <td colspan="2">
                        <label class="radio-inline">
			   <input type="radio" class="" id="firstLine" name="artLine" value="first_line" title="Please check ART Line"> First Line
			   </label>
			 <label class="radio-inline">
			   <input type="radio" class=" " id="secondLine" name="artLine" value="second_line" title="Please check ART Line"> Second Line
			 </label><br/>
			 <label for="currentRegimen">Current Regimen </label>
			 <label class="radio-inline">
			    <select class="form-control" id="currentRegimen" name="currentRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" onchange="checkValue();">
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
			 </label>
                        </td>
                        <td style="width:8%">
                        <label for="cdCells">CD4(cells/ul)  </label>
                        </td>
                        <td style="width:10%">
                          <input type="text" class="form-control" name="cdCells" id="cdCells" placeholder="CD4 Cells" title="CD4 Cells"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['art_cd_cells'];?>" >
                        </td>
                        <td style="width:8%">
                        <label for="cdDate">CD4 Date </label>
                        </td>
                        <td>
			  <input type="text" class="form-control date" name="cdDate" id="cdDate" placeholder="CD4 Date" title="Enter CD4 Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['art_cd_date'];?>">
                        </td>
                      </tr>
                      <tr>
			<td class="newArtRegimen" style="display: none;"><label for="newArtRegimen">New ART Regimen</label><span class="mandatory">*</span></td>
                        <td class="newArtRegimen" style="display: none;">
                          <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New Art Regimen" title="New Art Regimen" style="width:100%;" >
                        </td>
                        <td>
			  <label for="regStartDate">Current Regimen Start Date</label>
			</td>
			<td>
			  <label class="radio-inline">
			    <input type="text" class="form-control date" name="regStartDate" id="regStartDate" placeholder="Start Date" title="Enter Start Date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen'];?>" >
			  </label>
			</td>
                        <td colspan="2" class="clinicalStage"><label for="breastfeeding">WHO Clinical Stage</label>&nbsp;&nbsp;
                          <label class="radio-inline">
                             <input type="radio" id="clinicalOne" name="clinicalStage" value="one" title="WHO Clinical Statge" <?php echo ($vlQueryInfo[0]['who_clinical_stage']=='one')?"checked='checked'":""?>>I
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="clinicalTwo" name="clinicalStage" value="two" title="WHO Clinical Statge"<?php echo ($vlQueryInfo[0]['who_clinical_stage']=='two')?"checked='checked'":""?>>II
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="clinicalThree" name="clinicalStage" value="three" title="WHO Clinical Statge"<?php echo ($vlQueryInfo[0]['who_clinical_stage']=='three')?"checked='checked'":""?>>III
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="clinicalFour" name="clinicalStage" value="four" title="WHO Clinical Statge"<?php echo ($vlQueryInfo[0]['who_clinical_stage']=='four')?"checked='checked'":""?>>IV
                          </label>
                        </td>
                      </tr>
		      <tr><td colspan="6" style="font-size: 18px; font-weight: bold;">Section 4: Reason For Testing</td></tr>
                      <tr>
                         <td colspan="3" class="routine">
			    <label for="routine">Routine</label><br/>
                          <label class="radio-inline">
                             &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="routineOne" name="reasonForTest" value="First VL, routine monitoring (On ART for at least 6 months)" title="Please Check Routine"<?php echo ($vlQueryInfo[0]['reason_testing_png']=='First VL, routine monitoring (On ART for at least 6 months)')?"checked='checked'":""?>>First VL, routine monitoring (On ART for at least 6 months)
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="routineTwo" name="reasonForTest" value="Annual routine follow-up VL (Previous VL < 1000 cp/mL)" title="Please Check Routine" <?php echo ($vlQueryInfo[0]['reason_testing_png']=='Annual routine follow-up VL (Previous VL < 1000 cp/mL)')?"checked='checked'":""?>>Annual routine follow-up VL (Previous VL < 1000 cp/mL)
                          </label>
			 </td>
                         <td colspan="3" class="suspect">
			  <label for="suspect">Suspected Treatment Failure</label><br/>
                          <label class="radio-inline">
                             <input type="radio" id="suspectOne" name="reasonForTest" value="Suspected TF" title="Please Suspected TF"<?php echo ($vlQueryInfo[0]['reason_testing_png']=='Suspected TF')?"checked='checked'":""?>>Suspected TF
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="suspectTwo" name="reasonForTest" value="Follow-up VL after EAC (Previous VL >= 1000 cp/mL)" title="Please Suspected TF"<?php echo ($vlQueryInfo[0]['reason_testing_png']=='Follow-up VL after EAC (Previous VL >= 1000 cp/mL)')?"checked='checked'":""?>>Follow-up VL after EAC (Previous VL >= 1000 cp/mL)
                          </label>
			 </td>
                      </tr>
                      <tr>
                        <td colspan="3">
			  <label for="defaulter">Defaulter/ LTFU/ Poor Adherer</label><br/>
			  <label class="radio-inline">
			  <input type="radio" id="defaulter" name="reasonForTest" value="VL (after 3 months EAC)" title="Check Defaulter/ LTFU/ Poor Adherer"<?php echo ($vlQueryInfo[0]['reason_testing_png']=='VL (after 3 months EAC)')?"checked='checked'":""?>>VL (after 3 months EAC)
			  </label>&nbsp;&nbsp;&nbsp;
			</td>
			<td colspan="3">
			  <label for="other">Other</label><br/>
			  <label class="radio-inline">
                             <input type="radio" id="other" name="reasonForTest" value="Re-collection requested by lab" title="Please check Other"<?php echo ($vlQueryInfo[0]['reason_testing_png']=='Re-collection requested by lab')?"checked='checked'":""?>>Re-collection requested by lab
                          </label>
			  <label for="reason">&nbsp;&nbsp;&nbsp;Reason</label>
                          <label class="radio-inline">
                            <input type="text" class="form-control" id="reason" name="reason" placeholder="Enter Reason" title="Enter Reason" style="width:100%;" />
                          </label>
			</td>
                      </tr>
		      <tr><td colspan="2" style="font-size: 18px; font-weight: bold;">Section 5: Specimen information </td> <td colspan="4" style="font-size: 18px; font-weight: bold;"> Type of sample to transport</td></tr>
                      <tr>
                        <td>
			  <label for="collectionDate">Collection date</label>
			</td>
			<td>
			  <label class="radio-inline">
			  <input type="text" class="form-control " name="collectionDate" id="collectionDate" placeholder="Collection Date" title="Enter Collection Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['sample_collection_date'];?>" >
			  </label>
			</td>
                         <td colspan="4" class="typeOfSample">
			  <label class="radio-inline">
			    <input type="radio" id="dbs" name="typeOfSample" value="DBS" title="Check DBS"<?php echo ($vlQueryInfo[0]['sample_to_transport']=='DBS')?"checked='checked'":""?>>DBS
			  </label>
                          <label class="radio-inline" style="width:46%;">
                             <input type="radio" id="wholeBlood" name="typeOfSample" value="Whole blood" title="Check Whole blood" style="margin-top:10px;" <?php echo ($vlQueryInfo[0]['sample_to_transport']=='Whole blood')?"checked='checked'":""?>>Whole Blood
			     <input type="text" name="wholeBloodOne" id="wholeBloodOne" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo[0]['whole_blood_ml'];?>"/>&nbsp; x &nbsp;<input type="text" name="wholeBloodTwo" id="wholeBloodTwo" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo[0]['whole_blood_vial'];?>"/>vial(s)
			  </label>
			     <label class="radio-inline" style="width:42%;">
                             <input type="radio" id="plasma" name="typeOfSample" value="Plasma" title="Check Plasma" style="margin-top:10px;"<?php echo ($vlQueryInfo[0]['sample_to_transport']=='Plasma')?"checked='checked'":""?>>Plasma
			     <input type="text" name="plasmaOne" id="plasmaOne" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo[0]['plasma_ml'];?>"/>&nbsp;ml x &nbsp;<input type="text" name="plasmaTwo" id="plasmaTwo" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo[0]['plasma_vial'];?>"/>vial(s)
                          </label>
                        </td>
		      </tr>
		      <tr>
                        <td>
			  <label for="collectedBy">Specimen collected by</label>
			</td>
			<td>
			  <label class="radio-inline">
			  <input type="text" class="form-control " name="collectedBy" id="collectedBy" placeholder="Collected By" title="Enter Collected By"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['collected_by'];?>" >
			  </label>
			</td>
			<td colspan="4" class="processTime"><label for="processTime">For onsite plasma processing only</label>
			 <label class="radio-inline" style="width: 20%;">
			    <input type="text" name="processTime" id="processTime" class="form-control" style="width: 100%;" placeholder="Time" title="Processing Time" value="<?php echo $vlQueryInfo[0]['plasma_process_time'];?>"/>
			 </label>&nbsp;
			 <label for="processTech">Processing tech</label>
			 <label class="radio-inline">
			    <input type="text" name="processTech" id="processTech" class="form-control" style="width: 100%;" placeholder="Processing Tech" title="Processing Tech" value="<?php echo $vlQueryInfo[0]['plasma_process_tech'];?>"/>
			 </label>
			</td>
                      </tr>
		      <tr><td colspan="6" style="font-size: 18px; font-weight: bold;">CPHL Use Only </td></tr>
                      <tr>
			<td colspan="2" class="sampleQuality"><label for="breastfeeding">Sample Quality</label>&nbsp;
			 <label class="radio-inline">
			    <input type="radio" id="sampleQtyAccept" name="sampleQuality" value="accept" title="Check Sample Quality" <?php echo ($vlQueryInfo[0]['rejection']=='accept')?"checked='checked'":""?>>Accept
			 </label>
			 <label class="radio-inline">
			    <input type="radio" id="sampleQtyReject" name="sampleQuality" value="reject" title="Check Sample Quality" <?php echo ($vlQueryInfo[0]['rejection']=='reject')?"checked='checked'":""?>>Reject
			 </label>
			</td>
			<td colspan="2" class="reason"><label for="rejectionReason">Reason</label>
			 <label class="radio-inline">
			  <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason">
			      <option value="">-- Select --</option>
				<?php
				foreach($rejectionResult as $reject){
				  ?>
				  <option value="<?php echo $reject['rejection_reason_id'];?>" <?php echo ($vlQueryInfo[0]['sample_rejection_reason']==$reject['rejection_reason_id'])?"selected='selected'":""?>><?php echo ucwords($reject['rejection_reason_name']);?></option>
				  <?php
				}
				?>
			      </select>
			 </label>
			</td>
			<td class="laboratoryId"><label for="laboratoryId">Laboratory ID</label></td>
			<td>
			 <label class="radio-inline">
			    <select name="laboratoryId" id="laboratoryId" class="form-control" title="Please choose lab name" style="width: 85%;">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($lResult as $labName){
                              ?>
                              <option value="<?php echo $labName['facility_id'];?>"<?php echo ($vlQueryInfo[0]['lab_id']==$labName['facility_id'])?"selected='selected'":""?> ><?php echo ucwords($labName['facility_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
			 </label>
			</td>
                      </tr>
                      <tr>
			<td class="sampleType"><label for="sampleType">Sample Type Received</label></td>
			<td>
			 <label class="radio-inline">
			    <select name="sampleType" id="sampleType" class="form-control" title="Please choose Specimen type">
                                <option value=""> -- Select -- </option>
                                <?php
                                foreach($sResult as $name){
                                 ?>
                                 <option value="<?php echo $name['sample_id'];?>"<?php echo ($vlQueryInfo[0]['sample_id']==$name['sample_id'])?"selected='selected'":""?> ><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
			 </label>
			</td>
			<td class="receivedDate"><label for="receivedDate">Date Received</label></td>
			<td>
			 <label class="radio-inline">
			    <input type="text" class="form-control " name="receivedDate" id="receivedDate" placeholder="Received Date" title="Enter Received Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_sample_received_at_testing_lab'];?>" >
			 </label>
			</td>
			<td class="techName"><label for="techName">Tech Name</label></td>
			<td>
			 <label class="radio-inline">
			    <input type="text" class="form-control " name="techName" id="techName" placeholder="Tech Name" title="Enter Tech Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['tech_name_png'];?>" >
			 </label>
			</td>
                      </tr>
		      <tr>
			<td class=""><label for="testDate">Test date</label></td>
			<td>
			  <label class="radio-inline">
			    <input type="text" class="form-control " name="testDate" id="testDate" placeholder="Test Date" title="Enter Testing Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_tested_date'];?>" >
			 </label>
			</td>
			<td class=""><label for="testingTech">Testing tech</label></td>
			<td>
			  <label class="radio-inline">
			    <select name="testingTech" id="testingTech" class="form-control" title="Please choose VL Testing Platform">
			      <option value="">-- Select --</option>
			      <?php foreach($importResult as $mName) { ?>
				<option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>" <?php echo ($vlQueryInfo[0]['vl_test_platform'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit']==$mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'])?"selected='selected'":""?>><?php echo $mName['machine_name'];?></option>
				<?php
			      }
			      ?>
			    </select>
			 </label>
			</td>
			<td class=""><label for="vlResult">VL result</label></td>
			<td>
			  <label class="radio-inline">
			    <input type="text" class="form-control " name="vlResult" id="vlResult" placeholder="VL Result" title="Enter VL Result"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_result'];?>" >
			 </label>
			</td>
		      </tr>
		      <tr>
			<td class=""><label for="batchQuality">Batch quality</label></td>
			<td>
			  <label class="radio-inline">
                             <input type="radio" id="passed" name="batchQuality" value="passed" title="Batch Quality" <?php echo ($vlQueryInfo[0]['batch_quality']=='passed')?"checked='checked'":""?>>Passed
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="failed" name="batchQuality" value="failed" title="Batch Quality" <?php echo ($vlQueryInfo[0]['batch_quality']=='failed')?"checked='checked'":""?>>Failed
                          </label>
			</td>
			<td class=""><label for="testQuality">Sample test quality</label></td>
			<td>
			  <label class="radio-inline">
                             <input type="radio" id="passed" name="testQuality" value="passed" title="Test Quality" <?php echo ($vlQueryInfo[0]['sample_test_quality']=='passed')?"checked='checked'":""?>>Passed
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="failed" name="testQuality" value="invalid" title="Test Quality" <?php echo ($vlQueryInfo[0]['sample_test_quality']=='invalid')?"checked='checked'":""?>>Invalid
                          </label>
			</td>
			<td class=""><label for="vlResult">Batch</label></td>
			<td>
			  <label class="radio-inline">
			    <select name="batchNo" id="batchNo" class="form-control" title="Please choose batch number">
			      <option value="">-- Select --</option>
			      <?php foreach($bResult as $bName) { ?>
				<option value="<?php echo $bName['batch_id'];?>"<?php echo ($vlQueryInfo[0]['batch_id']==$bName['batch_id'])?"selected='selected'":""?> ><?php echo $bName['batch_code'];?></option>
				<?php
			      }
			      ?>
			    </select>
			 </label>
			</td>
		      </tr>
		      <tr>
			<th colspan="6">For failed / invalid runs only</th>
		      </tr>
		      <tr>
			<td class=""><label for="testDate">Repeat Test date</label></td>
			<td>
			  <label class="radio-inline">
			    <input type="text" class="form-control " name="failedTestDate" id="failedTestDate" placeholder="Test Date" title="Enter Testing Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['failed_test_date'];?>" >
			 </label>
			</td>
			<td class=""><label for="testingTech">Testing tech</label></td>
			<td>
			  <label class="radio-inline">
			    <select name="failedTestingTech" id="failedTestingTech" class="form-control" title="Please choose VL Testing Platform">
			      <option value="">-- Select --</option>
			      <?php foreach($importResult as $mName) { ?>
				<option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"<?php echo ($vlQueryInfo[0]['vl_test_platform'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit']==$mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'])?"selected='selected'":""?>><?php echo $mName['machine_name'];?></option>
				<?php
			      }
			      ?>
			    </select>
			 </label>
			</td>
			<td class=""><label for="vlResult">VL result</label></td>
			<td>
			  <label class="radio-inline">
			    <input type="text" class="form-control " name="failedvlResult" id="failedvlResult" placeholder="VL Result" title="Enter VL Result"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['failed_vl_result'];?>" >
			 </label>
			</td>
		      </tr>
		      <tr>
			<td class=""><label for="failedbatchQuality">Batch quality</label></td>
			<td>
			  <label class="radio-inline">
                             <input type="radio" id="passed" name="failedbatchQuality" value="passed" title="Batch Quality" <?php echo ($vlQueryInfo[0]['failed_batch_quality']=='passed')?"checked='checked'":""?>>Passed
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="failed" name="failedbatchQuality" value="failed" title="Batch Quality" <?php echo ($vlQueryInfo[0]['failed_batch_quality']=='failed')?"checked='checked'":""?>>Failed
                          </label>
			</td>
			<td class=""><label for="failedtestQuality">Sample test quality</label></td>
			<td>
			  <label class="radio-inline">
                             <input type="radio" id="passed" name="failedtestQuality" value="passed" title="Test Quality" <?php echo ($vlQueryInfo[0]['failed_sample_test_quality']=='passed')?"checked='checked'":""?>>Passed
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="failed" name="failedtestQuality" value="invalid" title="Test Quality" <?php echo ($vlQueryInfo[0]['failed_sample_test_quality']=='invalid')?"checked='checked'":""?>>Invalid
                          </label>
			</td>
			<td class=""><label for="vlResult">Batch</label></td>
			<td>
			  <label class="radio-inline">
			    <select name="failedbatchNo" id="failedbatchNo" class="form-control" title="Please choose batch number">
			      <option value="">-- Select --</option>
			      <?php foreach($bResult as $bName) { ?>
				<option value="<?php echo $bName['batch_id'];?>"<?php echo ($vlQueryInfo[0]['failed_batch_id']==$bName['batch_id'])?"selected='selected'":""?> ><?php echo $bName['batch_code'];?></option>
				<?php
			      }
			      ?>
			    </select>
			 </label>
			</td>
		      </tr>
		      <tr>
			<td class=""><label for="finalViralResult">Final Viral Load Result</label></td>
			<td colspan="2">
			  <label class="radio-inline">
                            <input type="text" class="form-control" name="finalViralResult" id="finalViralResult" placeholder="Viral Load Result" title="Enter Viral Result"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['result'];?>" >copies/ml
                          </label>
			</td>
			<td class=""><label for="testQuality">QC Tech Name</label></td>
			<td colspan="2">
			  <label class="radio-inline">
                             <input type="text" class="form-control" name="qcTechName" id="qcTechName" placeholder="QC Tech Name" title="Enter QC Tech Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['qc_tech_name'];?>" >
                          </label>
			</td>
		      </tr>
		      <tr>
			<td class=""><label for="finalViralResult">Report Date</label></td>
			<td>
			  <label class="radio-inline">
                            <input type="text" class="form-control date" name="reportDate" id="reportDate" placeholder="Report Date" title="Enter Report Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['clinic_date']; ?>"  >
                          </label>
			</td>
			<td class=""><label for="finalViralResult">QC Tech Signature</label></td>
			<td>
			  <label class="radio-inline">
                            <input type="text" class="form-control" name="qcTechSign" id="qcTechSign" placeholder="QC Tech Signature" title="Enter QC Tech Signature"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['qc_tech_sign'];?>" >
                          </label>
			</td>
			<td class=""><label for="testQuality">QC Date</label></td>
			<td colspan="">
			  <label class="radio-inline">
                             <input type="text" class="form-control date" name="qcDate" id="qcDate" placeholder="QC Date" title="Enter QC Date"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['qc_date'];?>">
                          </label>
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
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
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
  <script>
    provinceName = true;
    facilityName = true;
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
      $('#collectionDate,#receivedDate,#testDate,#failedTestDate').mask('99-aaa-9999 99:99');
   
      $('#collectionDate,#receivedDate,#testDate,#failedTestDate').datetimepicker({
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
      
      $('#processTime').timepicker({
	changeMonth: true,
	changeYear: true,
	timeFormat: "HH:mm",
	yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
	}).click(function(){
	   $('.ui-datepicker-calendar').hide();
	});
    });
    
    function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
            ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
    });
    $("#saveNext").val('save');
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
  }
    
    function getfacilityDetails(obj)
  {
    $.blockUI();
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
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
    $.unblockUI();
  }
  function getfacilityDistrictwise(obj)
  {
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#clinicName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
	  if(data != ""){
            $("#clinicName").html(data);
	  }
      });
    }
    $.unblockUI();
  }
  function getfacilityProvinceDetails(obj)
  {
    $.blockUI();
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
    $.unblockUI();
  }
  function checkValue()
  {
    var artRegimen = $("#currentRegimen").val();
    if(artRegimen=='other'){
      $(".newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $(".newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
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
            }
        });
    }
  </script>