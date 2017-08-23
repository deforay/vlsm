<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}

if($arr['sample_code']=='auto' || $arr['sample_code']=='alphanumeric' || $arr['sample_code']=='MMYY' || $arr['sample_code']=='YY'){
  $sampleClass = '';
  $maxLength = '';
  if($arr['max_length']!='' && $arr['sample_code']=='alphanumeric'){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}else{
  $sampleClass = 'checkNum';
  $maxLength = '';
  if($arr['max_length']!=''){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}
//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status='active'";
$lResult = $db->rawQuery($lQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }

$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";

//get active sample types
$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);
//regimen heading
$artRegimenQuery="SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='rwd'";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$aQuery="SELECT * from r_art_code_details where nation_identifier='rwd' AND art_status ='active'";
$aResult=$db->query($aQuery);
if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
}else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
}
$start_date = date('Y-01-01');
$end_date = date('Y-12-31');
//$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="7" AND vl.sample_code_title="'.$arr['sample_code'].'" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
$svlResult=$db->query($svlQuery);
  
  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
   $maxId = $svlResult[0]['sample_code_key']+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
$sKey = '';
$sFormat = '';
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
      .table > tbody > tr > td{
        border-top:none;
      }
      .form-control{
        width:100% !important;
      }
      .row{
        margin-top:6px;
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
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="addVlRequestHelperRwd.php">
              <div class="box-body">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired <?php echo $sampleClass;?>" id="sampleCode" name="sampleCode" <?php echo $maxLength;?> placeholder="Enter Sample ID" title="Please enter sample id" style="width:100%;"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleReordered">
                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" title="Please check sample reordered"> Sample Reordered
                          </label>
                        </div>
                      </div>
                       <!-- BARCODESTUFF START -->
                      <?php
                        if(isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off"){
                      ?>
                        <div class="col-xs-3 col-md-3 pull-right">
                          <div class="form-group">
                            <label for="sampleCode">Print Barcode Label<span class="mandatory">*</span> </label>
                            <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked/>
                          </div>
                        </div>
                      <?php
                        }
                      ?>
                      <!-- BARCODESTUFF END -->
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getProvinceDistricts(this);">
                            <?php echo $province;?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="district">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getFacilities(this);">
                            <option value=""> -- Select -- </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fName">Clinic/Health Center <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" id="fName" name="fName" title="Please select clinic/health center name" style="width:100%;" onchange="fillFacilityDetails();">
                              <?php echo $facility;  ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fCode">Clinic/Health Center Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code">
                          </div>
                      </div>
                    </div>
                    <div class="row facilityDetails" style="display:none;">
                      <div class="col-xs-2 col-md-2 femails" style="display:none;"><strong>Clinic Email(s) -</strong></div>
                      <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:none;"></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:none;"><strong>Clinic Mobile No.(s) -</strong></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:none;"></div>
                      <div class="col-xs-2 col-md-2 fContactPerson" style="display:none;"><strong>Clinic Contact Person -</strong></div>
                      <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:none;"></div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Patient Information</h3>&nbsp;&nbsp;&nbsp;
                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Enter ART Number or Patient" title="Enter art number" onchange="checkNameValidation('vl_request_form','patient_art_no',this,null)"/>&nbsp;&nbsp;
                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="artNo">ART (TRACNET) No. <span class="mandatory">*</span></label>
                          <input type="text" name="artNo" id="artNo" class="form-control isRequired" placeholder="Enter ART Number" title="Enter art number" onchange="checkNameValidation('vl_request_form','patient_art_no',this,null)"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="dob">Date of Birth </label>
                          <input type="text" name="dob" id="dob" class="form-control date" placeholder="Enter DOB" title="Enter dob" onchange="getAge();"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInYears">If DOB unknown, Age in Years </label>
                            <input type="text" name="ageInYears" id="ageInYears" class="form-control checkNum" maxlength="2" placeholder="Age in Year" title="Enter age in years"/>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInMonths">If Age < 1, Age in Months </label>
                            <input type="text" name="ageInMonths" id="ageInMonths" class="form-control checkNum" maxlength="2" placeholder="Age in Month" title="Enter age in months"/>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="patientFirstName">Patient Name </label>
                            <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name"/>
                          </div>
                      </div>  
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="gender">Gender</label><br>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender">Male
                          </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender">Female
                          </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender">Not Recorded
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPhoneNumber">Phone Number</label>
                          <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control checkNum" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number"/>
                        </div>
                      </div>
                   </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Sample Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Sample Collection <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" >
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                          <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type">
                                <option value=""> -- Select -- </option>
                                <?php
                                foreach($sResult as $name){
                                 ?>
                                 <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
                          </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Treatment Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Treatment Initiation</label>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of Treatment Initiated" title="Date Of treatment initiated" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="artRegimen">Current Regimen</label>
                            <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTValue();">
                                 <option value="">-- Select --</option>
                                  <?php foreach($artRegimenResult as $heading) { ?>
                                  <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                                    <?php
                                    foreach($aResult as $regimen){
                                      if($heading['headings'] == $regimen['headings']){
                                      ?>
                                      <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                      <?php
                                      }
                                    }
                                    ?>
                                  </optgroup>
                                  <?php } ?>
                            </select>
                            <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;" >
                          </div>
                       </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Initiation of Current Regimen </label>
                          <input type="text" class="form-control date" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="arvAdherence">ARV Adherence </label>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose adherence">
                            <option value=""> -- Select -- </option>
                            <option value="good">Good >= 95%</option>
                            <option value="fair">Fair (85-94%)</option>
                            <option value="poor">Poor < 85%</option>
                           </select>
                        </div>
                      </div>
                    </div>
                    <div class="row femaleSection">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPregnant">Is Patient Pregnant? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one"> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregNo" name="patientPregnant" value="no"> No
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="breastfeeding">Is Patient Breastfeeding? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check one"> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no"> No
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                       <h3 class="box-title">Indication for Viral Load Testing</h3><small> (Please tick one):(To be completed by clinician)</small>
                    </div>
                    <div class="box-body">
                      <div class="row">                
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <input type="radio" class="" id="rmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" onclick="showTesting('rmTesting');">
                                    <strong>Routine Monitoring</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="row rmTesting hideTestData" style="display:none;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                         </div>
                        </div>
                        <div class="col-md-6">
                             <label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                             (copies/ml)
                         </div>
                       </div>                 
                      </div>
                      <div class="row">                
                        <div class="col-md-8">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <input type="radio" class="" id="repeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('repeatTesting');">
                                    <strong>Repeat VL test after suspected treatment failure adherence counselling </strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row repeatTesting hideTestData" style="display:none;">
                       <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                            </div>
                      </div>
                       <div class="col-md-6">
                            <label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control checkNum viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                            (copies/ml)
                            </div>
                      </div>                 
                     </div>
                     <div class="row">         
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="suspect" title="Suspect Treatment Failure" onclick="showTesting('suspendTreatment');">
                                    <strong>Suspect Treatment Failure</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row suspendTreatment hideTestData" style="display: none;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                             </div>
                       </div>
                        <div class="col-md-6">
                             <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                             (copies/ml)
                             </div>
                       </div>                 
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="reqClinician" class="col-lg-5 control-label">Request Clinician</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter request clinician phone number" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="requestDate">Request Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control date" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date"/>
                            </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="vlFocalPerson" class="col-lg-5 control-label">VL Focal Person </label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter vl focal person name" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Focal Person Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter vl focal person phone number" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="emailHf">Email for HF </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control isEmail" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf"/>
                            </div>
                        </div>
                     </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Laboratory Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-md-4">
                            <label for="labId" class="col-lg-5 control-label">Lab Name </label>
                            <div class="col-lg-7">
                              <select name="labId" id="labId" class="form-control" title="Please choose lab">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($lResult as $labName){
                                  ?>
                                  <option value="<?php echo $labName['facility_id'];?>"><?php echo ucwords($labName['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform </label>
                            <div class="col-lg-7">
                              <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                                <option value="">-- Select --</option>
                                <?php foreach($importResult as $mName) { ?>
                                  <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <!--<div class="col-md-4">
                            <label for="testMethods" class="col-lg-5 control-label">Test Methods</label>
                            <div class="col-lg-7">
                              <select name="testMethods" id="testMethods" class="form-control" title="Please choose test methods">
                                <option value=""> -- Select -- </option>
                                <option value="individual">Individual</option>
                                <option value="minipool">Minipool</option>
                                <option value="other pooling algorithm">Other Pooling Algorithm</option>
                               </select>
                            </div>
                        </div>-->
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleReceivedOn">Date Sample Received at Testing Lab </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received Date" title="Please select sample received date"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date"/>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="noResult">Sample Rejection </label>
                            <div class="col-lg-7">
                              <label class="radio-inline">
                               <input class="" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio"> Yes
                              </label>
                              <label class="radio-inline">
                               <input class="" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio"> No
                              </label>
                            </div>
                        </div>
                        <div class="col-md-4 rejectionReason" style="display:none;">
                            <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason </label>
                            <div class="col-lg-7">
                              <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" onchange="checkRejectionReason();">
                                <option value="">-- Select --</option>
                                <?php foreach($rejectionTypeResult as $type) { ?>
                                <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                  <?php
                                  foreach($rejectionResult as $reject){
                                    if($type['rejection_type'] == $reject['rejection_type']){
                                    ?>
                                    <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                    <?php
                                    }
                                  }
                                  ?>
                                </optgroup>
                                <?php } ?>
                                <option value="other">Other (Please Specify) </option>
                              </select>
                              <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
                            </div>
                        </div>
                        <div class="col-md-4 vlResult">
                            <label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copiesl/ml) </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" style="width:100%;" />
                              <input type="checkbox" class="" id="tnd" name="tnd" value="yes" title="Please check tnd"> Target Not Detected<br>
                              <input type="checkbox" class="" id="bdl" name="bdl" value="yes" title="Please check bdl"> Below Detection Level
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                            <div class="col-lg-7">
                              <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($userResult as $uName){
                                  ?>
                                  <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="col-lg-2 control-label" for="labComments">Laboratory Scientist Comments </label>
                            <div class="col-lg-10">
                              <textarea class="form-control" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"></textarea>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
               </div>
              <div class="box-footer">
                <!-- BARCODESTUFF START -->                    
        <?php
					if(isset($global['bar_code_printing']) && $global['bar_code_printing'] == 'zebra-printer'){
					?>
							
							<div id="printer_data_loading" style="display:none"><span id="loading_message">Loading Printer Details...</span><br/>
								<div class="progress" style="width:100%">
									<div class="progress-bar progress-bar-striped active"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
									</div>
								</div>
							</div> <!-- /printer_data_loading -->
							<div id="printer_details" style="display:none">
								<span id="selected_printer">No printer selected!</span> 
								<button type="button" class="btn btn-success" onclick="changePrinter()">Change/Retry</button>
							</div><br /> <!-- /printer_details -->
							<div id="printer_select" style="display:none">
								Zebra Printer Options<br />
								Printer: <select id="printers"></select>
							</div> <!-- /printer_select -->
						
						<?php
						}
						?>                    
             <!-- BARCODESTUFF END -->
             
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code'];?>"/>
                <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                <?php } ?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateSaveNow();return false;">Save and Next</a>
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
              </div>
            </form>
      </div>
    </section>
  </div>
  <!-- BARCODESTUFF START -->
	<?php
		if(isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off"){
			if($global['bar_code_printing'] == 'dymo-labelwriter-450'){
				?>
					<script src="../assets/js/DYMO.Label.Framework.2.0.2.js"></script>
					<script src="../assets/js/dymo-print.js"></script>    
				<?php
			}else if($global['bar_code_printing'] == 'zebra-printer'){
				?>
					<script src="../assets/js/BrowserPrint-1.0.4.min.js"></script>
					<script src="../assets/js/zebra-print.js"></script>				
				<?php				
			}
		}
	?>

  <!-- BARCODESTUFF END -->
  <script>
    provinceName = true;
    facilityName = true;
    $(document).ready(function() {
      // BARCODESTUFF START
			
	<?php
          if(isset($_GET['barcode']) && $_GET['barcode'] == 'true'){
            echo "printBarcodeLabel('".$_GET['s']."','".$_GET['f']."');";
          }
	?>
  // BARCODESTUFF END
        $('.date').datepicker({
           changeMonth: true,
           changeYear: true,
           dateFormat: 'dd-M-yy',
           timeFormat: "hh:mm TT",
           maxDate: "Today",
           yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
          }).click(function(){
              $('.ui-datepicker-calendar').show();
         });
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-M-yy',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                  setTimeout(function() {
                     $('.ui-datepicker-calendar').show();
                  });
            },
            yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
            }).click(function(){
               $('.ui-datepicker-calendar').show();
            });
        $('.date').mask('99-aaa-9999');
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').mask('99-aaa-9999 99:99');
    });
    
    function showTesting(chosenClass){
      $(".viralTestData").val('');
      $(".hideTestData").hide();
      $("."+chosenClass).show();
    }
    
    function getProvinceDistricts(obj){
      $.blockUI();
      var cName = $("#fName").val();
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
            $("#district").html(details[1]);
            $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
            $(".facilityDetails").hide();
            $(".facilityEmails").html('');
            $(".facilityMobileNumbers").html('');
            $(".facilityContactPerson").html('');
	  }
      });
      }
      <?php
      if($arr['sample_code']=='auto'){
        ?>
        pNameVal = pName.split("##");
        sCode = '<?php echo date('ymd');?>';
        sCodeKey = '<?php echo $maxId;?>';
        $("#sampleCode").val(pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val(pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
        <?php
      }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        $("#sampleCode").val('<?php echo $prefix.$mnthYr.$maxId;?>');
        $("#sampleCodeFormat").val('<?php echo $prefix.$mnthYr;?>');
        $("#sampleCodeKey").val('<?php echo $maxId;?>');
        <?php
      }
      ?>
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#fName").html("<?php echo $facility;?>");
    }
    $.unblockUI();
  }
  
  function getFacilities(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#fName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
	  if(data != ""){
            $("#fName").html(data);
            $(".facilityDetails").hide();
            $(".facilityEmails").html('');
            $(".facilityMobileNumbers").html('');
            $(".facilityContactPerson").html('');
	  }
      });
    }
    $.unblockUI();
  }
  
  function fillFacilityDetails(){
    $("#fCode").val($('#fName').find(':selected').data('code'));
    var femails = $('#fName').find(':selected').data('emails');
    var fmobilenos = $('#fName').find(':selected').data('mobile-nos');
    var fContactPerson = $('#fName').find(':selected').data('contact-person');
    if($.trim(femails) !='' || $.trim(fmobilenos) !='' || fContactPerson != ''){
      $(".facilityDetails").show();
    }else{
      $(".facilityDetails").hide();
    }
    ($.trim(femails) !='')?$(".femails").show():$(".femails").hide();
    ($.trim(femails) !='')?$(".facilityEmails").html(femails):$(".facilityEmails").html('');
    ($.trim(fmobilenos) !='')?$(".fmobileNumbers").show():$(".fmobileNumbers").hide();
    ($.trim(fmobilenos) !='')?$(".facilityMobileNumbers").html(fmobilenos):$(".facilityMobileNumbers").html('');
    ($.trim(fContactPerson) !='')?$(".fContactPerson").show():$(".fContactPerson").hide();
    ($.trim(fContactPerson) !='')?$(".facilityContactPerson").html(fContactPerson):$(".facilityContactPerson").html('');
  }
  
  $("input:radio[name=gender]").click(function() {
    if($(this).val() == 'male' || $(this).val() == 'not_recorded'){
      $('.femaleSection').hide();
      $('input[name="breastfeeding"]').prop('checked', false);
      $('input[name="patientPregnant"]').prop('checked', false);
    }else if($(this).val() == 'female'){
      $('.femaleSection').show();
    }
  });
  
  $("input:radio[name=noResult]").click(function() {
    if($(this).val() == 'yes'){
      $('.rejectionReason').show();
      $('.vlResult').css('visibility','hidden');
      $('#rejectionReason').addClass('isRequired');
    }else{
      $('.vlResult').css('visibility','visible');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
    }
  });
  
  $('#tnd').change(function() {
    if($('#tnd').is(':checked')){
      $('#vlResult').attr('readonly',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#vlResult').attr('readonly',false);
      $('#bdl').attr('disabled',false);
    }
  });
  $('#bdl').change(function() {
    if($('#bdl').is(':checked')){
      $('#vlResult').attr('readonly',true);
      $('#tnd').attr('disabled',true);
    }else{
      $('#vlResult').attr('readonly',false);
      $('#tnd').attr('disabled',false);
    }
  });
  
  $('#vlResult').on('input',function(e){
    if(this.value != ''){
      $('#tnd').attr('disabled',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#tnd').attr('disabled',false);
      $('#bdl').attr('disabled',false);
    }
  });
  
  function checkARTValue(){
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $("#newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $("#newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
      $('#newArtRegimen').val("");
    }
  }
  
  function getAge(){
    var dob = $("#dob").val();
    if($.trim(dob) == ""){
      $("#ageInMonths").val("");
      $("#ageInYears").val("");
      return false;
    }
    //calculate age
    splitDob = dob.split("-");
    var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
    var monthDigit = dobDate.getMonth();
    var dobMonth = isNaN(monthDigit) ? 1 : (parseInt(monthDigit)+parseInt(1));
    dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
    dob = splitDob[2]+'-'+dobMonth+'-'+splitDob[0];
    var years = moment().diff(dob, 'years',false);
    var months = (years == 0)?moment().diff(dob, 'months',false):'';
    $("#ageInYears").val(years); // Gives difference as years
    $("#ageInMonths").val(months); // Gives difference as months
  }
  
  function checkRejectionReason(){
    var rejectionReason = $("#rejectionReason").val();
    if(rejectionReason == "other"){
      $("#newRejectionReason").show();
      $("#newRejectionReason").addClass("isRequired");
    }else{
      $("#newRejectionReason").hide();
      $("#newRejectionReason").removeClass("isRequired");
      $('#newRejectionReason').val("");
    }
  }
  
  function validateNow(){
      var format = '<?php echo $arr['sample_code'];?>';
      var sCodeLentgh = $("#sampleCode").val();
      var minLength = '<?php echo $arr['min_length'];?>';
      if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
        alert("Sample id length must be a minimum length of "+minLength+" characters");
        return false;
      }
    
    flag = deforayValidator.init({
        formId: 'vlRequestFormRwd'
    });
    
    $('.isRequired').each(function () {
      ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
    });
    $("#saveNext").val('save');
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestFormRwd').submit();
    }
  }
  
  function validateSaveNow(){
      var format = '<?php echo $arr['sample_code'];?>';
      var sCodeLentgh = $("#sampleCode").val();
      var minLength = '<?php echo $arr['min_length'];?>';
      if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
        alert("Sample id length must be a minimum length of "+minLength+" characters");
        return false;
      }
      flag = deforayValidator.init({
          formId: 'vlRequestFormRwd'
      });
      
    $('.isRequired').each(function () {
        ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
    });
    $("#saveNext").val('next');
    if(flag){
        $.blockUI();
        document.getElementById('vlRequestFormRwd').submit();
      }
  }
  function checkNameValidation(tableName,fieldName,obj,fnct)
    {
      if($.trim(obj.value)!=''){
        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : obj.value,fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                showModal('patientModal.php?artNo='+obj.value,900,520);
            }
        });
      }
    }
  function setPatientDetails(pDetails){
    patientArray = pDetails.split("##");
    $("#patientFirstName").val(patientArray[0]+" "+patientArray[1]);
    $("#patientPhoneNumber").val(patientArray[8]);
    if($.trim(patientArray[3])!=''){
      $("#dob").val(patientArray[3]);
      getAge();
    }
    if($.trim(patientArray[2])!=''){
      if(patientArray[2] == 'male' || patientArray[2] == 'not_recorded'){
      $('.femaleSection').hide();
      $('input[name="breastfeeding"]').prop('checked', false);
      $('input[name="patientPregnant"]').prop('checked', false);
        if(patientArray[2] == 'male'){
          $("#genderMale").prop('checked', true);
        }else{
          $("#genderNotRecorded").prop('checked', true);
        }
      }else if(patientArray[2] == 'female'){
        $('.femaleSection').show();
        $("#genderFemale").prop('checked', true);
        if($.trim(patientArray[6])!=''){
          if($.trim(patientArray[6])=='yes'){
            $("#pregYes").prop('checked', true);
          }else if($.trim(patientArray[6])=='no'){
            $("#pregNo").prop('checked', true);
          }
        }
        if($.trim(patientArray[7])!=''){
          if($.trim(patientArray[7])=='yes'){
            $("#breastfeedingYes").prop('checked', true);
          }else if($.trim(patientArray[7])=='no'){
            $("#breastfeedingNo").prop('checked', true);
          }
        }
      }
    }
    if($.trim(patientArray[9])!=''){
      if(patientArray[9] == 'yes'){
        $("#receivesmsYes").prop('checked', true);
      }else if(patientArray[9] == 'no'){
        $("#receivesmsNo").prop('checked', true);
      }
    }
    if($.trim(patientArray[15])!=''){
      $("#artNo").val($.trim(patientArray[15]));
    }
  }
  function showPatientList()
  {
      if($.trim($("#artPatientNo").val())!=''){
        $.post("checkPatientExist.php", { artPatientNo : $("#artPatientNo").val()},
        function(data){
            if(data >= '1'){
                showModal('patientModal.php?artNo='+$.trim($("#artPatientNo").val()),900,520);
            }
        });
      }
  }
  </script>