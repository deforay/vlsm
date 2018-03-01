<?php
ob_start();
if($arr['sample_code']=='auto' || $arr['sample_code']=='alphanumeric' || $arr['sample_code']=='MMYY' || $arr['sample_code']=='YY'){
  $numeric = '';
  $maxLength = '';
  if($arr['max_length']!='' && $arr['sample_code']=='alphanumeric'){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}else{
  $numeric = 'checkNum';
  $maxLength = '';
  if($arr['max_length']!=''){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}
//check remote user
$rKey = '';
$pdQuery="SELECT * from province_details";
if($sarr['user_type']=='remoteuser'){
  $sampleCodeKey = 'remote_sample_code_key';
  $sampleCode = 'remote_sample_code';
  //check user exist in user_facility_map table
  $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='".$_SESSION['userId']."'";
  $chkUserFcMapResult = $db->query($chkUserFcMapQry);
  if($chkUserFcMapResult){
  $pdQuery="SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='".$_SESSION['userId']."'";
  }
  $rKey = 'R';
}else{
  $sampleCodeKey = 'sample_code_key';
  $sampleCode = 'sample_code';
  $rKey = '';
}
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
            foreach($pdResult as $provinceName){
              $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
            } 
$facility = '';
$facility.="<option data-code='' value=''> -- Select -- </option>";
$aQuery="SELECT * from r_art_code_details where nation_identifier='who'";
$aResult=$db->query($aQuery);

  $end_date = date('Y-12-31');
  $start_date = date('Y-01-01');  
//$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="1" AND vl.sample_code_title="'.$arr['sample_code'].'" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$svlQuery='SELECT '.$sampleCodeKey.' FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" AND '.$sampleCode.'!="" ORDER BY vl_sample_id DESC LIMIT 1';

$svlResult=$db->query($svlQuery);
  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0][$sampleCodeKey]!='' && $svlResult[0][$sampleCodeKey]!=NULL){
   $maxId = $svlResult[0][$sampleCodeKey]+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
$sKey = '';$sFormat = '';
?>
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
            <form class="form-inline" method='post' name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="addVlRequestHelperWho.php">
              <div class="box-body">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Specimen identification information: To be completed by laboratory staff</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample Code <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired <?php echo $numeric;?>" id="sampleCode" name="sampleCode" <?php echo $maxLength;?> placeholder="Enter Sample Code" title="Please enter sample code" style="width:100%;" onblur="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>',this.id,null,'This sample number already exists.Try another number',null)"/>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
                            <?php echo $province;?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="district">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                            <option value=""> -- Select -- </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fName">Facility Name <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" id="fName" name="fName" title="Please select facility name" style="width:100%;" onchange="autoFillFacilityCode();">
                              <?php echo $facility;  ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fCode">Facility Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Facility Code" title="Please enter facility code">
                          </div>
                      </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="sampleCollectionDate">Date Specimen Collected <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired dateTime" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" onchange="sampleCodeGeneration();">
                          </div>
                        </div>
                        <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="specimenType">Specimen Type</label>
                          <select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type" style="width:100%;">
                                <option value=""> -- Select -- </option>
                                <?php foreach($sResult as $name){ ?>
                                 <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                                 <?php } ?>
                            </select>
                          </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-body">
                      <div class="box-header with-border">
                        <h3 class="box-title">Paitent information: To be completed by clinician</h3>
                      </div>
                    </div>
                    <div class="box-body">
                        <table class="table">
                            <tr>
                              <td><label for="patientFirstName">Patient First Name</label></td>
                              <td>
                                <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter First Name" title="Enter patient first name"/>
                              </td>
                              <td><label for="patientMiddleName">Patient Middle Name</label></td>
                              <td>
                                <input type="text" name="patientMiddleName" id="patientMiddleName" class="form-control" placeholder="Enter Middle Name" title="Enter patient middle name"/>
                              </td>
                              <td><label for="patientLastName">Patient Last Name</label></td>
                              <td>
                                <input type="text" name="patientLastName" id="patientLastName" class="form-control" placeholder="Enter Last Name" title="Enter patient last name"/>
                              </td>
                            </tr>
                            <tr>
                              <td><label for="uniqueId">Unique identifier</label></td>
                              <td>
                                <input type="text" name="uniqueId" id="uniqueId" class="uniqueId form-control" placeholder="Enter Unique Id" title="Enter unique identifier"/>
                              </td>
                              <td><label for="dob">Date Of Birth</label></td>
                              <td>
                                <input type="text" name="dob" id="dob" class="date form-control" placeholder="Enter DOB" title="Enter dob" onchange="getAge();checkARTInitiationDate();" />
                              </td>
                              <td><label for="artNo">Art Number</label></td>
                              <td>
                                <input type="text" name="artNo" id="artNo" class="form-control" placeholder="Enter ART Number" title="Enter art number"/>
                              </td>
                            </tr>
                            <tr>
                              <td><label for="ageInYears">If unknown, age in years</label></td>
                              <td>
                                <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter age in years" style="width:100%;" >
                              </td>
                              <td><label for="ageInMonths">If age < 1, age in months</label></td>
                              <td>
                                <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" style="width:100%;" >
                              </td>
                              <td colspan="2">
                                <label for="gender">Gender &nbsp;&nbsp;</label>
                                 <label class="radio-inline">
                                  <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                                  </label>
                                <label class="radio-inline">
                                  <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender"> Not Recorded
                                </label>
                              </td>
                            </tr>
                            <tr>
                                <td><label for="artRegimen">Current Regimen</label></td>
                                <td>
                                    <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTRegimenValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php foreach($aResult as $parentRow){ ?>
                                  <option value="<?php echo $parentRow['art_code']; ?>"><?php echo $parentRow['art_code']; ?></option>
                                 <?php } if($sarr['user_type']!='vluser'){  ?>
                                 <option value="other">Other</option>
                                 <?php } ?>
                                </select>
                                <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New ART Regimen" title="Please enter new art regimen" style="width:100%;display:none;margin-top:2px;" >
                                </td>
                                <td><label for="dateOfArtInitiation">Date treatment initiated</td>
                                <td colspan="3">
                                  <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of treatment initiated" title="Date Of treatment initiated" style="width:36%;" onchange="checkARTInitiationDate();">
                                </td>
                            </tr>
                            <tr>
                                <td><label for="lineOfTreatment">Line of Treatment </label></td>
                                <td>
                                  <select name="lineOfTreatment" id="lineOfTreatment" class="form-control" title="Please choose line of treatment" style="width:100%;">
                                    <option value=""> -- Select -- </option>
                                    <option value="1">First Line</option>
                                    <option value="2">Second Line</option>
                                    <option value="3">Third Line</option>
                                   </select>
                                </td>
                                <td colspan="4"><label for="therapy">Is the Patient receiving second-line theraphy? </label>
                                    <label class="radio-inline">
                                        <input type="radio" class="" id="theraphyYes" name="theraphy" value="yes" title="Is the Patient receiving second-line theraphy? "> Yes
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" class=" " id="theraphyNo" name="theraphy" value="no" title="Is the Patient receiving second-line theraphy?"> No
                                    </label>
                                </td>
                            </tr>
                            <tr class="femaleSection">
                                <td colspan="3" class=""><label for="breastfeeding" class="">Is the Patient Pregnant or Breastfeeding?</label>
                                  <label class="radio-inline">
                                     <input type="radio" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Pregnant or Breastfeeding">Yes
                                  </label>
                                  <label class="radio-inline">
                                    <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Pregnant or Breastfeeding">No
                                  </label>
                                </td>
                                <td colspan="3" class=""><label for="drugTransmission" class="">Is the Patient receiving ARV drugs for <br>preventing mother-to-child transmission?</label>
                                  <label class="radio-inline">
                                     <input type="radio" id="transmissionYes" name="drugTransmission" value="yes" title="Is the Patient receiving ARV drugs for preventing mother-to-child transmission?">Yes
                                  </label>
                                  <label class="radio-inline">
                                    <input type="radio" id="transmissionNo" name="drugTransmission" value="no" title="Is the Patient receiving ARV drugs for preventing mother-to-child transmission?">No
                                  </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><label for="patientTB">Does the patient have active TB?</label>
                                    <label class="radio-inline">
                                        <input type="radio" class="" id="patientTBYes" name="patientTB" value="yes" title="Does the patient have active TB?"> Yes
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" class=" " id="patientTBNo" name="patientTB" value="no" title="Does the patient have active TB?"> No
                                    </label>
                                </td>
                                <td colspan=""><label for="patientPhoneNumber">Patient's telephone number</td>
                                <td colspan="2">
                                  <input type="text" class="form-control " name="patientPhoneNumber" id="patientPhoneNumber" placeholder="Phone Number" title="Enter telephone number" style="width:100%;" >
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><label for="patientTB">If Yes, is he or she on</label>
                                    <label class="radio-inline">
                                        <input type="radio" class="" id="patientTBInitiation" name="patientTBActive" value="yes" title="Does the patient have active TB? Yes"> Initiation
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" class=" " id="patientTBPhase" name="patientTBActive" value="no" title="Does the patient have active TB? Yes"> Continuation Phase
                                    </label>
                                </td>
                                <td colspan=""><label for="arvAdherence">ARV adherence</td>
                                <td colspan="2">
                                  <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence" style="width: 100%">
                                    <option value=""> -- Select -- </option>
                                    <option value="good">Good >= 95%</option>
                                    <option value="fair">Fair (85-94%)</option>
                                    <option value="poor">Poor < 85%</option>
                                   </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Indication for viral load testing</h3>
                    <small>(Please tick one):(To be completed by clinician)</small>
                </div>
                <div class="box-body">
                    <div class="row">                
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <input type="radio" class="" id="RmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" onclick="showTesting('RmTesting');">
                                    <strong>Routine Monitoring</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                    </div><br/>
                    <div class="row RmTesting hideTestData" style="display: none;">
                       <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                      </div>
                       <div class="col-md-6">
                            <label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                            (copies/ml)
                        </div>
                      </div>                 
                    </div>
                    <div class="row">                
                        <div class="col-md-8">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <input type="radio" class="" id="RepeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('RepeatTesting');">
                                    <strong>Repeat VL test after detectable viraemia and six months of adherence counselling </strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                    </div><br/>
                    <div class="row RepeatTesting hideTestData" style="display: none;">
                       <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                            </div>
                      </div>
                       <div class="col-md-6">
                            <label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
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
                    </div><br/>
                    <div class="row suspendTreatment hideTestData" style="display: none;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                             </div>
                       </div>
                        <div class="col-md-6">
                             <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                             (copies/ml)
                             </div>
                       </div>                 
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="reqClinician" class="col-lg-4 control-label">Request Clinician</label>
                        <div class="col-lg-7">
                           <input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" />
                        </div>
                   </div>
                    <div class="col-md-6">
                        <label class="col-lg-4 control-label" for="requestDate">Requested Date </label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control date readonly" readonly='readonly' id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date"/>
                        </div>
                    </div>
                </div><br/>
                </div>
              </div>
              <div class="box box-primary">
                  <div class="box-body">
                    <div class="box-header with-border">
                    <h3 class="box-title">Laboratory Information</h3>
                    </div>
                    <table class="table">
                      <tr>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" style="width: 100%">
                            <option value="">-- Select --</option>
                            <?php foreach($importResult as $mName) { ?>
                              <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                            <?php } ?>
                          </select>
                        </td>
                        <td><label for="testMethods">Test Methods</label></td>
                        <td>
                          <select name="testMethods" id="testMethods" class="form-control " title="Please choose test methods" style="width: 100%">
                          <option value=""> -- Select -- </option>
                          <option value="individual">Individual</option>
                          <option value="minipool">Minipool</option>
                          <option value="other pooling algorithm">Other Pooling Algorithm</option>
                         </select>
                        </td>
                        <td><label for="rejectionReason">Reason For Failure </label></td>
                        <td>
                            <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" style="width: 100%">
                                <option value="">-- Select --</option>
                               <?php foreach($rejectionResult as $reject){ ?>
                                 <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                <?php } ?>
                            </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" onchange="checkSampleTestingDate();" style="width:100%;"/></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" /></td>
                        <td><label for="labId">Lab Name</label></td>
                        <td>
                          <select name="labId" id="labId" class="form-control" title="Please choose lab name" style="width: 100%">
                            <option value=""> -- Select -- </option>
                            <?php foreach($lResult as $labName){ ?>
                              <option value="<?php echo $labName['facility_id'];?>"><?php echo ucwords($labName['facility_name']);?></option>
                            <?php } ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Approved By</label></td>
                        <td>
                          <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by" style="width: 100%">
                            <option value="">-- Select --</option>
                            <?php foreach($userResult as $uName){  ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                            <?php } ?>
                          </select>
                         </td>
                        <td><label for="labComments">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="3"><textarea class="form-control" name="labComments" id="labComments" title="Enter lab comments" style="width:100%"></textarea></td>
                      </tr>
                    </table>
                  </div>
              </div>
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <input type="hidden" name="formId" id="formId" value="6"/>
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
<script>
  provinceName = true;
  facilityName = true;
  $("input:radio[name=gender]").click(function() {
    if($(this).val() == 'male' || $(this).val() == 'not_recorded'){
      $('.femaleSection').hide();
      $('input[name="breastfeeding"]').prop('checked', false);
      $('input[name="drugTransmission"]').prop('checked', false);
    }else if($(this).val() == 'female'){
      $('.femaleSection').show();
    }
  });
  $("input:radio[name=patientTB]").click(function() {
    if($(this).val() == 'no'){
      $('input[name="patientTBActive"]').prop('checked', false);
      $('input[name="patientTBActive"]').prop('disabled', true);
    }else if($(this).val() == 'yes'){
      $('input[name="patientTBActive"]').prop('disabled', false);
    }
  });
  function validateNow(){
    var format = '<?php echo $arr['sample_code'];?>';
    var sCodeLentgh = $("#sampleCode").val();
    var minLength = '<?php echo $arr['min_length'];?>';
    if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
      alert("Sample code length atleast "+minLength+" characters");
      return false;
    }
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
  function validateSaveNow(){
    var format = '<?php echo $arr['sample_code'];?>';
    var sCodeLentgh = $("#sampleCode").val();
    var minLength = '<?php echo $arr['min_length'];?>';
    if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
      alert("Sample code length atleast "+minLength+" characters");
      return false;
    }
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
      ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
    });
    $("#saveNext").val('next');
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
  }
  function showTesting(chosenClass){
   $(".viralTestData").val('');
   $(".hideTestData").hide();
   $("."+chosenClass).show();
  }
  function getfacilityDetails(obj)
  {
    $.blockUI();
    var cName = $("#fName").val();
    var pName = $("#province").val();
    if(pName!='' && provinceName && facilityName){ facilityName = false; }
    if(pName!=''){
      if(provinceName){
        $.post("../includes/getFacilityForClinic.php", { pName : pName},
        function(data){
          if(data != ""){
            details = data.split("###");
            $("#district").html(details[1]);
            $("#fName").html("<option data-code='' value=''> -- Select -- </option>");
          }
        });
      }
      sampleCodeGeneration();
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#fName").html("<?php echo $facility;?>");
    }
    $.unblockUI();
  }
  function sampleCodeGeneration()
  {
    var pName = $("#province").val();
    var sDate = $("#sampleCollectionDate").val();
    if(pName!='' && sDate!=''){
      $.post("../includes/sampleCodeGeneration.php", { sDate : sDate},
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
  function getfacilityDistrictwise(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#fName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
        if(data != ""){ 
          details = data.split("###");
          $("#fName").html(details[0]);
          $("#labId").html(details[1]);
        }
      });
    }
    $.unblockUI();
  }
  function autoFillFacilityCode(){
    $("#fCode").val($('#fName').find(':selected').data('code'));
  }
  function checkRejectedReason(){
    $("#rejectionReason").addClass("isRequired");
  }
</script>