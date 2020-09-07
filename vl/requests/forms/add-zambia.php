<?php
ob_start();
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
$sKey = ''; $sFormat = '';
$start_date = date('Y-01-01');
$end_date = date('Y-12-31');
if($global['sample_code']=='MMYY'){
    $mnthYr = date('my');
    $start_date = date('Y-m-01');
  $end_date = date('Y-m-31');
  }else if($global['sample_code']=='YY'){
    $mnthYr = date('y');
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
  }
//check remote user
  if($sarr['user_type']=='remoteuser'){
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    $rKey = 'R';
  }else{
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
  }
//$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="4" AND vl.sample_code_title="'.$global['sample_code'].'" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$svlQuery='SELECT '.$sampleCodeKey.' FROM vl_request_form as vl WHERE DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'" AND '.$sampleCode.'!="" ORDER BY '.$sampleCodeKey.' DESC LIMIT 1';
$svlResult=$db->query($svlQuery);
  $prefix = $global['sample_code_prefix'];
  if($svlResult[0][$sampleCodeKey]!='' && $svlResult[0][$sampleCodeKey]!=NULL){
   $maxId = $svlResult[0][$sampleCodeKey]+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }

$province = '';
$province.="<option value=''> -- Select -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }
$district = '';
$district.="<option value=''> -- Select -- </option>";
$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";

//get art regimen
$artRegimenQuery="SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='zam'";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$artQuery="SELECT * from r_art_code_details where nation_identifier='zam' AND art_status ='active'";
$artResult=$db->query($artQuery);
?>
<style>
  .table > tbody > tr > td{ border-top:none; }
  .form-control,.form-group{ width:100% !important;}
  .row{ margin-top:6px; }
</style>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
      <ol class="breadcrumb">
        <li><a href="/dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
                            <input type="text" class="form-control isRequired <?php echo $sampleClass;?>" id="sampleCode" name="sampleCode" <?php echo $maxLength;?> placeholder="Enter Sample ID" title="Please enter sample id" style="width:100%;" onblur="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>',this.id,null,'This sample number already exists.Try another number',null)"/>
                          </div>
                        </div>
                         <!-- BARCODESTUFF START -->
                        <?php if(isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off"){ ?>
                          <div class="col-xs-4 col-md-4 pull-right">
                            <div class="form-group">
                              <label for="printBarCode">Print Barcode Label<span class="mandatory">*</span> </label>
                              <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked/>
                            </div>
                          </div>
                        <?php } ?>
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
                                <?php foreach($lResult as $lab){ ?>
                                  <option value="<?php echo $lab['facility_id'];?>"><?php echo ucwords($lab['facility_name']);?></option>
                                  <?php } ?>
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
                        <h3 class="box-title">Section 2: Patient Information</h3>&nbsp;&nbsp;&nbsp;
                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Enter ART Number or Patient Name" title="Enter art number or patient name" />&nbsp;&nbsp;
                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>
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
                            <input type="text" class="form-control isRequired" style="width:100%;" name="patientArtNo" id="patientArtNo" placeholder="ART Number" title="Please enter patient ART number" onchange="checkPatientDetails('vl_request_form','patient_art_no',this,null)">
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
                            <input type="text" class="form-control date" style="width:100%;" name="dob" id="dob" placeholder="DOB" title="Please enter patient date of birth" onchange="getAgeInWeeks();checkARTInitiationDate();">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="ageInWeeks">Age(weeks) </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="ageInWeeks" id="ageInWeeks" placeholder="Age in Weeks" title="Please enter age in weeks">
                            <input type="hidden" name="ageInYears" id="ageInYears">
                            <input type="hidden" name="ageInMonths" id="ageInMonths">
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
                             <input type="text" class="form-control date" style="width:100%;" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiated" title="Please enter date of art initiated" onchange="checkARTInitiationDate();">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">ART Regimen </label><br>
                             <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTRegimenValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php foreach($artRegimenResult as $heading) { ?>
                                  <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                                    <?php
                                    foreach($artResult as $regimen){
                                      if($heading['headings'] == $regimen['headings']){ ?>
                                      <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                      <?php } } ?>
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
                              <?php foreach($testReason as $testReason){ ?>
                                <option value="<?php echo $testReason['test_reason_id'];?>"><?php echo ucwords($testReason['test_reason_name']);?></option>
                                <?php } ?>
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
                              <?php foreach($sResult as $sampleType){ ?>
                               <option value="<?php echo $sampleType['sample_id'];?>"><?php echo ucwords($sampleType['sample_name']);?></option>
                               <?php } ?>
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
                             <input type="text" class="form-control isRequired dateTime" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collected Date" title="Please enter sample collected date" onchange="checkSampleReceviedDate();checkSampleTestingDate();sampleCodeGeneration();">
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
                              <?php foreach($userResult as $user){ ?>
                                <option value="<?php echo $user['user_id'];?>"><?php echo ucwords($user['user_name']);?></option>
                                <?php } ?>
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
                <?php if($sarr['user_type']!= 'remoteuser') { ?>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 4: For Laboratory Use Only</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Sample Received at the Lab </label><br>
                             <input type="text" class="form-control dateTime" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" placeholder="Sample Received Date" title="Please enter sample received date at lab" onchange="checkSampleReceviedDate()">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Test Performed </label><br>
                             <input type="text" class="form-control dateTime" style="width:100%;" name="sampleTestingDateAtLab" id="sampleTestingDateAtLab" placeholder="Test Performed Date" title="Please enter test performed date" onchange="checkSampleTestingDate();">
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
                              <?php foreach($rejectionResult as $reject){
                                  if($type['rejection_type'] == $reject['rejection_type']){ ?>
                                <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                <?php } } ?>
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
                              <?php foreach($userResult as $user){ ?>
                                <option value="<?php echo $user['user_id'];?>"><?php echo ucwords($user['user_name']);?></option>
                                <?php } ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Reviewed By Datetime </label><br>
                             <input type="text" class="form-control dateTime" style="width:100%;" name="reviewedByDatetime" id="reviewedByDatetime" placeholder="Reviewed by Date" title="Please enter reviewed by date">
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
                              <?php } ?>
                <div class="box-footer">
                <!-- BARCODESTUFF START -->
                <?php if(isset($global['bar_code_printing']) && $global['bar_code_printing'] == 'zebra-printer'){ ?>
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
                  <?php } ?>                    
               <!-- BARCODESTUFF END -->
                  <input type="hidden" name="saveNext" id="saveNext"/>
                  <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $global['sample_code'];?>"/>
                  <?php if($global['sample_code']=='auto' || $global['sample_code']=='YY' || $global['sample_code']=='MMYY'){ ?>
                    <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                    <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                  <?php } ?>
                  <input type="hidden" name="vlSampleId" id="vlSampleId" value=""/>
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
                                  <script src="../assets/js/DYMO.Label.Framework.js"></script>
                                  <script src="../configs/dymo-format.js"></script>    
                                  <script src="../assets/js/dymo-print.js"></script>    
                          <?php
                  }else if($global['bar_code_printing'] == 'zebra-printer'){
                          ?>
                                  <script src="../assets/js/zebra-browserprint.js.js"></script>
                                  <script src="../configs/zebra-format.js"></script>
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
  });
  function getProvinceDistricts(){
    $.blockUI();
    var pName = $("#province").val();
    if($.trim(pName)!=''){
      $.post("/includes/getFacilityForClinic.php", { pName : pName},
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
    sampleCodeGeneration()
  }
  function sampleCodeGeneration()
  {
    var pName = $("#province").val();
    var sDate = $("#sampleCollectionDate").val();
    if(pName!='' && sDate!=''){
      $.post("/vl/requests/sampleCodeGeneration.php", { sDate : sDate},
      function(data){
        var sCodeKey = JSON.parse(data);
        <?php if($arr['sample_code']=='auto'){ ?>
          pNameVal = pName.split("##");
          sCode = sCodeKey.auto;
          $("#sampleCode").val('<?php echo $rKey;?>'+pNameVal[1]+sCode+sCodeKey.maxId);
          $("#sampleCodeFormat").val('<?php echo $rKey;?>'+pNameVal[1]+sCode);
          $("#sampleCodeKey").val(sCodeKey.maxId);
          checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null);
          <?php } else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
          $("#sampleCode").val('<?php echo $rKey.$prefix;?>'+sCodeKey.mnthYr+sCodeKey.maxId);
          $("#sampleCodeFormat").val('<?php echo $rKey.$prefix;?>'+sCodeKey.mnthYr);
          $("#sampleCodeKey").val(sCodeKey.maxId);
          checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null)
        <?php } ?>
      });
    }
  }
  function getFacilities(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#fName").val();
    if($.trim(dName)!=''){
      $.post("/includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
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
      $("#ageInWeeks").val("");
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
    var months = moment().diff(dob, 'months',false);
    var weeks = moment().diff(dob, 'weeks',false);
    $("#ageInYears").val(years); // Gives difference as years
    $("#ageInMonths").val(months); // Gives difference as months
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
      <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        insertSampleCode('vlRequestFormZam','vlSampleId','sampleCode','sampleCodeKey','sampleCodeFormat',4,'sampleCollectionDate');
      <?php } else { ?>
      document.getElementById('vlRequestFormZam').submit();
      <?php } ?>
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
      <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        insertSampleCode('vlRequestFormZam','vlSampleId','sampleCode','sampleCodeKey','sampleCodeFormat',4,'sampleCollectionDate');
      <?php } else { ?>
      document.getElementById('vlRequestFormZam').submit();
      <?php } ?>
    }
  }
  function setPatientDetails(pDetails){
      patientArray = pDetails.split("##");
      $("#patientFname").val(patientArray[0]);
      $("#surName").val(patientArray[1]);
      $("#patientPhoneNumber").val(patientArray[8]);
      if($.trim(patientArray[3])!=''){
        $("#dob").val(patientArray[3]);
        getAgeInWeeks();
      }else if($.trim(patientArray[4])!='' && $.trim(patientArray[4]) != 0){
        $("#ageInYears").val(patientArray[4]);
      }else if($.trim(patientArray[5])!=''){
        $("#ageInMonths").val(patientArray[5]);
      }
      if($.trim(patientArray[2])!=''){
        if(patientArray[2] == 'male' || patientArray[2] == 'not_recorded'){
        $('input[name="patientPregnant"]').prop('checked', false);
        $('#lineOfTreatment').val('');
        $('#pregYes,#pregNo,#lineOfTreatment').prop('disabled',true);
          if(patientArray[2] == 'male'){
            $("#genderMale").prop('checked', true);
          }else{
            $("#genderNotRecorded").prop('checked', true);
          }
        }else if(patientArray[2] == 'female'){
          $('#pregYes,#pregNo,#lineOfTreatment').prop('disabled',false);
          $("#genderFemale").prop('checked', true);
          if($.trim(patientArray[6])!=''){
            if($.trim(patientArray[6])=='yes'){
              $("#pregYes").prop('checked', true);
            }else if($.trim(patientArray[6])=='no'){
              $("#pregNo").prop('checked', true);
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
      $("#patientArtNo").val($.trim(patientArray[15]));
      }
  }
  </script>