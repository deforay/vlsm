<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
//set sample code max length
$sampleClass = '';
$maxLength = '';
if($global['sample_code']=='auto' || $global['sample_code']=='alphanumeric' || $global['sample_code']=='MMYY' || $global['sample_code']=='YY'){
  if($global['max_length']!='' && $global['sample_code']=='alphanumeric'){
    $maxLength = "maxlength=".$global['max_length'];
  }
}else{
  $sampleClass = 'checkNum';
  if($global['max_length']!=''){
    $maxLength = "maxlength=".$global['max_length'];
  }
}
//generate sample code
$sKey = '';
$sFormat = '';
$start_date = date('Y-m-01');
$end_date = date('Y-m-31');
$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="4" AND vl.sample_code_title="'.$global['sample_code'].'" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$svlResult=$db->query($svlQuery);
if($global['sample_code']=='MMYY'){
    $mnthYr = date('my');
  }else if($global['sample_code']=='YY'){
    $mnthYr = date('y');
  }
  $prefix = $global['sample_code_prefix'];
  if($svlResult[0]['MAX(sample_code_key)']!='' && $svlResult[0]['MAX(sample_code_key)']!=NULL){
   $maxId = $svlResult[0]['MAX(sample_code_key)']+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
//set province
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }
  
$district = '';
$district.="<option value=''> -- Select -- </option>";

$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
//set lab facilities
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status='active'";
$lResult = $db->rawQuery($lQuery);
//get active sample types
$sampleTypeQuery="SELECT * from r_sample_type where status='active'";
$sampleTypeResult=$db->query($sampleTypeQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//get active users
$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
//get suspected treatment failure at
$suspectedTreatmentFailureAtQuery="SELECT DISTINCT vl_sample_suspected_treatment_failure_at FROM vl_request_form where vlsm_country_id='4'";
$suspectedTreatmentFailureAtResult = $db->rawQuery($suspectedTreatmentFailureAtQuery);
//get art regimen
$artRegimenQuery="SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='zam'";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$artQuery="SELECT * from r_art_code_details where nation_identifier='zam' AND art_status ='active'";
$artResult=$db->query($artQuery);
//get vl test reasons
$testReasonQuery="SELECT * FROM r_vl_test_reasons";
$testReasonResult = $db->rawQuery($testReasonQuery);
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
      .form-control,.form-group{
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
            <form class="form-inline" method="post" name="vlRequestFormZam" id="vlRequestFormZam" autocomplete="off" action="addVlRequestHelperZam.php">
              <div class="box-body">
                 <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Section 1: Health Facility Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                            <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                            <input type="text" class="form-control isRequired <?php echo $sampleClass;?>" id="sampleCode" name="sampleCode" <?php echo $maxLength;?> placeholder="Enter Sample ID" title="Please enter sample id" style="width:100%;"/>
                          </div>
                        </div>
                         <!-- BARCODESTUFF START -->
                        <?php
                          if(isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off"){
                        ?>
                          <div class="col-xs-4 col-md-4 pull-right">
                            <div class="form-group">
                              <label for="printBarCode">Print Barcode Label<span class="mandatory">*</span> </label>
                              <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked/>
                            </div>
                          </div>
                        <?php
                          }
                        ?>
                        <!-- BARCODESTUFF END -->
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="province">Province <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getProvinceDistricts(this);">
                              <?php echo $province;?>
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="district">District  <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getFacilities(this);">
                              <option value=""> -- Select -- </option>
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                            <label for="fName">Health Facility <span class="mandatory">*</span></label>
                              <select class="form-control isRequired" id="fName" name="fName" title="Please select health facility" style="width:100%;" onchange="fillFacilityDetails();">
                                <option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>
                              </select>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="fCode">Health Facility Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Health Facility Code" title="Please enter health facility code">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reqClinician">Requesting Officer </label>
                            <input type="text" class="form-control" style="width:100%;" name="reqClinician" id="reqClinician" placeholder="Requesting Officer" title="Please enter requesting officer">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reqClinicianPhoneNumber">Contact Number </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="reqClinicianPhoneNumber" id="reqClinicianPhoneNumber" placeholder="Contact Number" title="Please enter contact number" maxlength="15">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="labId">Referral Laboratory for Sample </label>
                            <select class="form-control" name="labId" id="labId" title="Please choose laboratory" style="width:100%;">
                              <option value=""> -- Select -- </option>
                                <?php
                                foreach($lResult as $lab){
                                  ?>
                                  <option value="<?php echo $lab['facility_id'];?>"><?php echo ucwords($lab['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              <option value="other"> Other </option>
                            </select>
                            <input class="form-control newLab" name="newLab" id="newLab" placeholder="Lab Name" title="Please enter lab name" style="width:100%;margin-top: 2px;display:none;" type="text">
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Section 2: Patient Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="surName">Surname </label>
                            <input type="text" class="form-control" style="width:100%;" name="surName" id="surName" placeholder="Patient Surname" title="Please enter patient surname">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientFname">First Name </label>
                            <input type="text" class="form-control" style="width:100%;" name="patientFname" id="patientFname" placeholder="Patient First Name" title="Please enter patient first name">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientArtNo">ART No. <span class="mandatory">*</span></label>
                            <input type="text" class="form-control isRequired" style="width:100%;" name="patientArtNo" id="patientArtNo" placeholder="ART Number" title="Please enter patient ART number">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientPhoneNumber">Contact No. </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="patientPhoneNumber" id="patientPhoneNumber" placeholder="Patient Contact No." title="Please enter patient contact no." maxlength="15">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="">DOB </label>
                            <input type="text" class="form-control date" style="width:100%;" name="dob" id="dob" placeholder="DOB" title="Please enter patient date of birth" onchange="getAgeInWeeks();">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="ageInWeeks">Age(weeks) </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="ageInWeeks" id="ageInWeeks" placeholder="Age in Weeks" title="Please enter age in weeks">
                            <input type="hidden" name="ageInYears" id="ageInYears">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="gender">Sex</label><br>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderMale" name="gender" value="male" title="Please check gender" type="radio">Male
                            </label>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderFemale" name="gender" value="female" type="radio">Female
                            </label>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderNotRecorded" name="gender" value="not_recorded" type="radio">Not Recorded
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Is Patient Pregnant? </label><br>
                            <label class="radio-inline">
                              <input class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one" type="radio"> Yes
                              </label>
                            <label class="radio-inline">
                              <input class="" id="pregNo" name="patientPregnant" value="no" type="radio"> No
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="lineOfTreatment">Treatment Stage </label>
                            <select name="lineOfTreatment" id="lineOfTreatment" class="form-control" title="Please choose treatment stage" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="1">1st Line</option>
                              <option value="2">2nd Line</option>
                              <option value="3">3rd Line</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date of ART Initiation </label><br>
                             <input type="text" class="form-control date" style="width:100%;" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiated" title="Please enter date of art initiated">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">ART Regimen </label><br>
                             <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php foreach($artRegimenResult as $heading) { ?>
                                  <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                                    <?php
                                    foreach($artResult as $regimen){
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
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Reason VL Requested </label><br>
                            <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:100%;" onchange="checkTestReason();">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($testReasonResult as $testReason){
                                ?>
                                <option value="<?php echo $testReason['test_reason_name'];?>"><?php echo ucwords($testReason['test_reason_name']);?></option>
                                <?php
                              }
                              ?>
                              <option value="other">Other</option>
                            </select>
                           <input type="text" class="form-control newVlTestReason" name="newVlTestReason" id="newVlTestReason" placeholder="VL Test Reason" title="Please enter VL Test Reason" style="width:100%;display:none;margin-top:2px;">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Of Last Viral Load Test </label><br>
                             <input type="text" class="form-control date" style="width:100%;" name="lastViralLoadTestDate" id="lastViralLoadTestDate" placeholder="Date Of Last Viral Load Test" title="Please enter date of last viral load test">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="lastViralLoadResult">Result Of Last Viral Load(copies/ml) </label><br>
                             <input type="text" class="form-control checkNum" style="width:100%;" name="lastViralLoadResult" id="lastViralLoadResult" placeholder="Result Of Last Viral Load" title="Please enter result of last viral load test">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="enhancedSession">Enhanced Sessions </label>
                            <select name="enhancedSession" id="enhancedSession" class="form-control" title="Please choose enhanced session" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="1">1</option>
                              <option value="2">2</option>
                              <option value="3">3</option>
                              <option value=">3"> > 3</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 3: Specimen Information</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($sampleTypeResult as $sampleType){
                               ?>
                               <option value="<?php echo $sampleType['sample_id'];?>"><?php echo ucwords($sampleType['sample_name']);?></option>
                               <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Repeat Sample? (due to rejection) </label><br>
                            <label class="radio-inline">
                              <input id="sampleReorderedYes" name="sampleReordered" value="yes" title="Please check one" type="radio"> Yes
                              </label>
                            <label class="radio-inline">
                              <input id="sampleReorderedNo" name="sampleReordered" value="no" type="radio"> No
                            </label>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Sample Collected <span class="mandatory">*</span></label><br>
                             <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collected Date" title="Please enter sample collected date">
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="visitType">Visit Type </label>
                            <select name="visitType" id="visitType" class="form-control" title="Please choose visit type" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="6month">6 Months</option>
                              <option value="12month">12 Months</option>
                              <option value="repeat">Repeat</option>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="suspectedTreatmentFailureAt">Suspected Treatment Failure at </label>
                            <select name="suspectedTreatmentFailureAt" id="suspectedTreatmentFailureAt" class="form-control" title="Please choose suspected treatment failure at" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($suspectedTreatmentFailureAtResult as $stfat){
                                if(trim($stfat['vl_sample_suspected_treatment_failure_at'])!= ''){
                              ?>
                                <option value="<?php echo $stfat['vl_sample_suspected_treatment_failure_at']; ?>"><?php echo ucwords($stfat['vl_sample_suspected_treatment_failure_at']); ?></option>
                              <?php } } ?>
                              <option value="other">Other(Specify)</option>
                            </select>
                            <input class="form-control newSuspectedTreatmentFailureAt" name="newSuspectedTreatmentFailureAt" id="newSuspectedTreatmentFailureAt" placeholder="Treatment Failure At" title="Please enter treatment failure at" style="width:100%;margin-top: 2px;display:none;" type="text">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="collectedBy">Collected By </label>
                            <select name="collectedBy" id="collectedBy" class="form-control" title="Please choose collected by" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($userResult as $user){
                                ?>
                                <option value="<?php echo $user['user_id'];?>"><?php echo ucwords($user['user_name']);?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-8 col-md-8">
                          <div class="form-group">
                          <label for="facilityComments">Facility Comments </label>
                            <textarea class="form-control" name="facilityComments" id="facilityComments" placeholder="Facility Comments" title="Please enter comments" style="width:100%"></textarea>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 4: For Laboratory Use Only</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Sample Received at the Lab </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="sampleReceivedOn" id="sampleReceivedOn" placeholder="Sample Received Date" title="Please enter sample received date at lab">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Test Performed </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="sampleTestingDateAtLab" id="sampleTestingDateAtLab" placeholder="Test Performed Date" title="Please enter test performed date">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                          <label for="sampleValidity">Sample Validity </label><br>
                            <label class="radio-inline">
                             <input class="" id="sampleValidityYes" name="sampleValidity" value="passed" title="Please check one" type="radio"> Accepted
                            </label>
                            <label class="radio-inline">
                             <input class="" id="sampleValidityNo" name="sampleValidity" value="invalid" title="Please check one" type="radio"> Rejected
                            </label>
                        </div>
                      </div>
                    </div>
                    <div class="row rejectionSection" style="display:none;">
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                         <label for="">If Rejected, Repeat Sample Collection </label><br>
                         <label class="radio-inline">
                           <input class="" id="repeatSampleCollectionYes" name="repeatSampleCollection" value="yes" title="Please check one" type="radio"> Yes
                           </label>
                         <label class="radio-inline">
                           <input class="" id="repeatSampleCollectionNo" name="repeatSampleCollection" value="no" type="radio"> No
                         </label>
                        </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                          <label for="rejectionReason">Reason For Rejection </label>
                          <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please choose rejection reason">
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
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 5: Results</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="result">Results </label>
                            <select name="result" id="result" class="form-control" title="Please choose test result" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="tnd">Target Not Detected</option>
                              <option value="actual_copies">Actual Copies</option>
                              <option value="invalid">Invalid</option>
                              <option value="repeat">Repeat Sample Collection</option>
                            </select>
                            <span class="vlResult" style="display:none;"><input class="form-control checkNum" name="vlResult" id="vlResult" placeholder="Viral Load Result" title="Please enter vl result" style="width:75% !important;margin-top: 2px;" type="text">&nbsp;(copiesl/ml)</span>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reviewedBy">Reviewed By </label>
                            <select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($userResult as $user){
                                ?>
                                <option value="<?php echo $user['user_id'];?>"><?php echo ucwords($user['user_name']);?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Reviewed By Datetime </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="reviewedByDatetime" id="reviewedByDatetime" placeholder="Reviewed by Date" title="Please enter reviewed by date">
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="labContactPerson">Lab Staff Name </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="labContactPerson" id="labContactPerson" placeholder="Lab Staff Name" title="Please enter lab staff name">
                          </div>
                      </div>
                      <div class="col-xs-8 col-md-8">
                          <div class="form-group">
                          <label for="labComments">Laboratory Comments </label>
                            <textarea class="form-control" name="labComments" id="labComments" placeholder="Lab Comments" title="Please enter comments" style="width:100%"></textarea>
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
                  <input type="hidden" name="saveNext" id="saveNext"/>
                  <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $global['sample_code'];?>"/>
                  <?php if($global['sample_code']=='auto' || $global['sample_code']=='YY' || $global['sample_code']=='MMYY'){ ?>
                    <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                    <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                  <?php } ?>
                  <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
                  <a class="btn btn-primary" href="javascript:void(0);" onclick="validateSaveNow();return false;">Save and Next</a>&nbsp;
                  <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
                </div>
              </div>
            </form>
        </div>
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
    $(document).ready(function() {
      // BARCODESTUFF START
	<?php
          if(isset($_GET['barcode']) && $_GET['barcode'] == 'true'){
            echo "printBarcodeLabel('".$_GET['s']."','".$_GET['f']."');";
          }
	?>
     //BARCODESTUFF END
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
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#reviewedByDatetime').datetimepicker({
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
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#reviewedByDatetime').mask('99-aaa-9999 99:99');
    });
    
    function getProvinceDistricts(){
      $.blockUI();
      var pName = $("#province").val();
      if($.trim(pName)!=''){
        $.post("../includes/getFacilityForClinic.php", { pName : pName},
        function(data){
          $.unblockUI();
          if(data != ""){
            details = data.split("###");
            $("#district").html(details[1]);
            $("#fName").html("<?php echo $facility;?>");
            $("#fCode").val("");
          }
        });
      }else{
        $.unblockUI();
        $("#district").html("<?php echo $district;?>");
        $("#fName").html("<?php echo $facility;?>");
        $("#fCode").val("");
      }
      <?php
      if($global['sample_code']=='auto'){ ?>
        var pNameVal = pName.split("##");
        var sCode = '<?php echo date('Ymd');?>';
        var sCodeKey = '<?php echo $maxId;?>';
        $("#sampleCode").val(pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val(pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
      <?php } else if($global['sample_code']=='YY' || $global['sample_code']=='MMYY'){ ?>
        $("#sampleCode").val('<?php echo $prefix.$mnthYr.$maxId;?>');
        $("#sampleCodeFormat").val('<?php echo $prefix.$mnthYr;?>');
        $("#sampleCodeKey").val('<?php echo $maxId;?>');
      <?php } ?>
    }
    
    function getFacilities(obj){
      $.blockUI();
      var dName = $("#district").val();
      var cName = $("#fName").val();
      if($.trim(dName)!=''){
        $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
        function(data){
             $.unblockUI();
            if(data != ""){
              $("#fName").html(data);
              $("#fCode").val("");
            }
        });
      }else{
        $.unblockUI();
        $("#fName").html("<?php echo $facility;?>");
        $("#fCode").val("");
      }
    }
    
    function fillFacilityDetails(){
      $("#fCode").val($('#fName').find(':selected').data('code'));
    }
    
    $('#labId').on('change',function(){
      if(this.value == 'other'){
        $("#newLab").show();
        $("#newLab").addClass("isRequired");
        $("#newLab").focus();
      }else{
        $("#newLab").hide();
        $("#newLab").removeClass("isRequired");
        $('#newLab').val("");
      }
    });
    
    function getAgeInWeeks(){
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#age").val("");
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
      var weeks = moment().diff(dob, 'weeks',false);
      $("#ageInYears").val(years); // Gives difference as years
      $("#ageInWeeks").val(weeks); // Gives difference as weeks
    }
    
    $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male' || $(this).val() == 'not_recorded'){
        $('input[name="patientPregnant"]').prop('checked', false);
        $('#lineOfTreatment').val('');
        $('#pregYes,#pregNo,#lineOfTreatment').prop('disabled',true);
      }else if($(this).val() == 'female'){
        $('#pregYes,#pregNo,#lineOfTreatment').prop('disabled',false);
      }
    });
    
    $('#rejectionReason').on('change',function(){
      if(this.value == "other"){
        $("#newRejectionReason").show();
        $("#newRejectionReason").addClass("isRequired");
        $("#newRejectionReason").focus();
      }else{
        $("#newRejectionReason").hide();
        $("#newRejectionReason").removeClass("isRequired");
        $('#newRejectionReason').val("");
      }
    });
    
    
    $("input:radio[name=sampleValidity]").click(function() {
      if($(this).val() == 'passed'){
        $('input[name="repeatSampleCollection"]').prop('checked', false);
        $('#rejectionReason').val('');
        $('#newRejectionReason').hide();
        $('#newRejectionReason').val('');
        $('.rejectionSection').hide();
      }else if($(this).val() == 'invalid'){
        $('.rejectionSection').show();
      }
    });
    
    $('#suspectedTreatmentFailureAt').on('change',function(){
      if(this.value == "other"){
        $("#newSuspectedTreatmentFailureAt").show();
        $("#newSuspectedTreatmentFailureAt").addClass("isRequired");
        $("#newSuspectedTreatmentFailureAt").focus();
      }else{
        $("#newSuspectedTreatmentFailureAt").hide();
        $("#newSuspectedTreatmentFailureAt").removeClass("isRequired");
        $('#newSuspectedTreatmentFailureAt').val("");
      }
    });
    
    $('#result').on('change',function(){
      if(this.value == "actual_copies"){
        $(".vlResult").show();
        $("#vlResult").addClass("isRequired");
        $("#vlResult").focus();
      }else{
        $(".vlResult").hide();
        $("#vlResult").removeClass("isRequired");
        $('#vlResult').val("");
      }
    });
    
    function checkARTValue(){
      var artRegimen = $("#artRegimen").val();
      if(artRegimen=='other'){
        $("#newArtRegimen").show();
        $("#newArtRegimen").addClass("isRequired");
        $("#newArtRegimen").focus();
      }else{
        $("#newArtRegimen").hide();
        $("#newArtRegimen").removeClass("isRequired");
        $('#newArtRegimen').val("");
      }
    }
  
    function checkTestReason(){
      var reason = $("#vlTestReason").val();
      if(reason=='other'){
        $("#newVlTestReason").show();
        $("#newVlTestReason").addClass("isRequired");
        $("#newVlTestReason").focus();
      }else{
        $("#newVlTestReason").hide();
        $("#newVlTestReason").removeClass("isRequired");
      } 
    }
    
    function validateNow(){
      var format = '<?php echo $global['sample_code'];?>';
      var sCodeLentgh = $("#sampleCode").val();
      var minLength = '<?php echo $global['min_length'];?>';
      if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
        alert("Sample id length must be a minimum length of "+minLength+" characters");
        return false;
      }
    
      flag = deforayValidator.init({
          formId: 'vlRequestFormZam'
      });
      
      $('.isRequired').each(function () {
        ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
      });
      $("#saveNext").val('save');
      if(flag){
        $.blockUI();
        document.getElementById('vlRequestFormZam').submit();
      }
    }
  
    function validateSaveNow(){
      var format = '<?php echo $global['sample_code'];?>';
      var sCodeLentgh = $("#sampleCode").val();
      var minLength = '<?php echo $global['min_length'];?>';
      if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
        alert("Sample id length must be a minimum length of "+minLength+" characters");
        return false;
      }
      flag = deforayValidator.init({
          formId: 'vlRequestFormZam'
      });
      
      $('.isRequired').each(function () {
          ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
      });
      $("#saveNext").val('next');
      if(flag){
          $.blockUI();
          document.getElementById('vlRequestFormZam').submit();
         }
    }
  </script>