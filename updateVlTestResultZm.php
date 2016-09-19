<?php
ob_start();
//include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
$vlQuery="SELECT * from vl_request_form where treament_id=$id";
$vlQueryInfo=$db->query($vlQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
$sQuery="SELECT * from r_sample_type where form_identification='2'";
$sResult=$db->query($sQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);

//facility details
$facilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
$facilityResult=$db->query($facilityQuery);

$stateName = $facilityResult[0]['state'];
$stateQuery="SELECT * from province_details where province_name='".$stateName."'";
$stateResult=$db->query($stateQuery);

//district details
$districtQuery="SELECT * from facility_details where state='".$stateName."'";
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
if(isset($vlQueryInfo[0]['sample_testing_date']) && trim($vlQueryInfo[0]['sample_testing_date'])!='' && trim($vlQueryInfo[0]['sample_testing_date'])!='0000-00-00'){
 $vlQueryInfo[0]['sample_testing_date']=$general->humanDateFormat($vlQueryInfo[0]['sample_testing_date']);
}else{
 $vlQueryInfo[0]['sample_testing_date']='';
}
if(isset($vlQueryInfo[0]['date_sample_received_at_testing_lab']) && trim($vlQueryInfo[0]['date_sample_received_at_testing_lab'])!='' && $vlQueryInfo[0]['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['date_sample_received_at_testing_lab']);
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']='';
}
$disable = "disabled = 'disabled'";
//get test status values
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);
?>
<style>
 :disabled {background:white;}
 .form-control{background: #fff !important;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM</h1>
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
                          <input type="text" class="form-control serialNo checkNum removeValue" id="" <?php echo $disable; ?> name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "treament_id##".$id;?>','This serial number already exists.Try another number',null)"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3 col-sm-offset-2 col-md-offset-2" style="padding:10px;">
                        <div class="form-group">
                        <label for="urgency">Urgency&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <label class="radio-inline">
                             <input type="radio" class="" id="urgencyNormal" name="urgency" value="normal" <?php echo $disable; ?> title="Please check urgency" <?php echo ($vlQueryInfo[0]['urgency']=='normal')?"checked='checked'":""?>> Normal
                        </label>
                        <label class="radio-inline">
                             <input type="radio" class=" " id="urgencyUrgent" name="urgency" value="urgent" <?php echo $disable; ?> title="Please check urgency" <?php echo ($vlQueryInfo[0]['urgency']=='urgent')?"checked='checked'":""?>> Urgent
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
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
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
                    <input type="text" class="form-control" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" <?php echo $disable; ?> placeholder="Sample Received Date" value="<?php echo $vlQueryInfo[0]['date_sample_received_at_testing_lab']; ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control" name="collectedBy" id="collectedBy" style="width:100%;" <?php echo $disable; ?> title="Enter Collected by (Initials)" placeholder="Enter Collected by (Initials)" value="<?php echo $vlQueryInfo[0]['collected_by'];?>">
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
                          <input type="text" class="form-control  " name="patientFname" id="patientFname" <?php echo $disable; ?> placeholder="First Name" title="Enter First Name"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_name'];?>" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname </label>
                        </td>
                        <td style="width:18%">
                          <input type="text" class="form-control" name="surName" id="surName" placeholder="Surname" <?php echo $disable; ?> title="Enter Surname"  style="width:100%;"  value="<?php echo $vlQueryInfo[0]['surname'];?>" >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender">Gender &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['gender']=='male')?"checked='checked'":""?>> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['gender']=='female')?"checked='checked'":""?>> Female
                          </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo $disable; ?> <?php echo ($vlQueryInfo[0]['gender']=='not_recorded')?"checked='checked'":""?>> Not Recorded
                          </label>
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_dob'];?>" onchange="getDateOfBirth('ch');return false;">
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_yrs'];?>">
                          
                        </td>
                      </tr>
                      <tr>
                        
                        
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" <?php echo $disable; ?> style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_mnts'];?>" >
                        </td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" <?php echo $disable; ?> title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?> > Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo $disable; ?> title="Please check Is Patient Pregnant" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='no')?"checked='checked'":""?> > No
                          </label>
                        </td>
                        
                        <td colspan="2"  class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>><label for="breastfeeding">Is Patient Breastfeeding?</label>
                        
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
                          <input type="text" class="form-control" name="patientArtNo" id="patientArtNo" <?php echo $disable; ?> placeholder="Patient OI/ART Number" title="Enter Patient OI/ART Number" style="width:100%;" value="<?php echo $vlQueryInfo[0]['art_no'];?>" >
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
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" <?php echo $disable; ?> title="Patient consent to receive SMS" <?php echo ($vlQueryInfo[0]['patient_receive_sms']=='yes')?"checked='checked'":""?> onclick="checkPatientReceivesms(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" <?php echo $disable; ?> title="Patient consent to receive SMS" <?php echo ($vlQueryInfo[0]['patient_receive_sms']=='no')?"checked='checked'":""?> onclick="checkPatientReceivesms(this.value);"> No
                          </label>
                        </td>
                        <td><label for="patientPhoneNumber" class="">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" readonly="readonly" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_phone_number'];?>" /></td>
                      </tr>
                      <tr class="newArtRegimen" style="display: none;">
                        
                      </tr>
                      <tr>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" <?php echo $disable; ?> placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_date'];?>" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" <?php echo $disable; ?> placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_result'];?>" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" <?php echo $disable; ?> placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;"  value="<?php echo $vlQueryInfo[0]['viral_load_log'];?>"/></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" <?php echo $disable; ?> style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <option value="routine_VL" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='routine_VL')?"selected='selected'":""?>>Routine VL</option>
                            <option value="confirmation_of_treatment_failure" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='confirmation_of_treatment_failure')?"selected='selected'":""?>>Confirmation Of Treatment Failure(repeat VL at 3M)</option>
                            <option value="clinical_failure" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='clinical_failure')?"selected='selected'":""?>>Clinical Failure</option>
                            <option value="immunological_failure" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='immunological_failure')?"selected='selected'":""?>>Immunological Failure</option>
                            <option value="single_drug_substitution" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='single_drug_substitution')?"selected='selected'":""?>>Single Drug Substitution</option>
                            <option value="pregnant_other" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='pregnant_other')?"selected='selected'":""?>>Pregnant Mother</option>
                            <option value="lactating_mother" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='lactating_mother')?"selected='selected'":""?>>Lactating Mother</option>
                            <option value="baseline_VL" <?php echo ($vlQueryInfo[0]['vl_test_reason']=='baseline_VL')?"selected='selected'":""?>>Baseline VL</option>                            
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
                        <td><input type="text" class="form-control serialNo1 checkNum isRequired removeValue" id="" name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "treament_id##".$id;?>','This serial number already exists.Try another number',null)" /></td>
                        <td><label for="sampleCode">Request Barcode <span class="mandatory">*</span></label></td>
                        <td>
                          <input type="text" class="form-control reqBarcode checkNum isRequired removeValue" name="reqBarcode" id="reqBarcode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" onblur="checkNameValidation('vl_request_form','serial_no',this,'<?php echo "treament_id##".$id;?>','This barcode already exists.Try another barcode',null)"/>
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
                        <td><input type="text" class="form-control checkNum" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_no'];?>" /></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                              <option value=""> -- Select -- </option>
                              <option value="roche" <?php echo ($vlQueryInfo[0]['vl_test_platform']=='roche')?"selected='selected'":""?>>ROCHE</option>
                              <option value="abbott" <?php echo ($vlQueryInfo[0]['vl_test_platform']=='abbott')?"selected='selected'":""?>>ABBOTT</option>
                              <option value="poor" <?php echo ($vlQueryInfo[0]['vl_test_platform']=='poor')?"selected='selected'":""?>>BIOMEREUX</option>
                              <option value="poc"<?php echo ($vlQueryInfo[0]['vl_test_platform']=='poc')?"selected='selected'":""?>>POC</option>
                              <option value="other">OTHER</option>
                          </select>
                        </td>
                        <td><label for="specimenType">Specimen type</label></td>
                        <td>
                          <select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($sResult as $name){
                               ?>
                               <option value="<?php echo $name['sample_id'];?>" <?php echo ($vlQueryInfo[0]['sample_id']==$name['sample_id'])?"selected='selected'":""?>><?php echo ucwords($name['sample_name']);?></option>
                               <?php
                              }
                              ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control date" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['sample_testing_date'];?>" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" value="<?php echo $vlQueryInfo[0]['absolute_value'];?>" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;" value="<?php echo $vlQueryInfo[0]['log_value'];?>" /></td>
                      </tr>
                      <tr class="">
                        <td><label class="noResult">If no result</label></td>
                        <td colspan="3">
                          <label class="radio-inline noResult">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result" <?php echo ($vlQueryInfo[0]['rejection']=='sample_rejected')?"checked='checked'":""?>> Sample Rejected
                          </label>
                          <label class="radio-inline noResult">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result"<?php echo ($vlQueryInfo[0]['rejection']=='technical_error')?"checked='checked'":""?>> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Approved By</label></td>
                        <td>
                          <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose Approved By">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($userResult as $uName){
                               ?>
                               <option value="<?php echo $uName['user_id'];?>"<?php echo ($vlQueryInfo[0]['result_approved_by']==$uName['user_id'])?"selected='selected'":""?>><?php echo ucwords($uName['user_name']);?></option>
                               <?php
                              }
                              ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="labCommnets">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="5"><textarea class="form-control" name="labCommnets" id="labComments" title="Enter lab comments" style="width:100%"> <?php echo $vlQueryInfo[0]['comments'];?></textarea></td>
                      </tr>
                      <tr>
                       <td><label for="labCommnets">Status</label></td>
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
                <input type="hidden" name="treamentId" id="treamentId" value="<?php echo $vlQueryInfo[0]['treament_id'];?>"/>
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
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
            ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
    });
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
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
      $.post("getFacilityForClinic.php", { pName : pName},
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
      $.post("getFacilityForClinic.php", { cName : cName},
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
   $('#sampleCollectionDate,#sampleReceivedDate').mask('99-aaa-9999 99:99');
   
   $('#sampleCollectionDate,#sampleReceivedDate').datetimepicker({
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
       getDateOfBirth("ld");
     <?php }
     ?>
  });
  $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }else if($(this).val() == 'not_recorded'){
        $(".femaleElements").show();
      }
    });
  
  
  if($("input:radio[name=receiveSms]:checked") && $("input:radio[name=receiveSms]:checked").val() =='yes'){
    $("#patientPhoneNumber").removeAttr("disabled");
  }else{
    $("#patientPhoneNumber").attr("disabled","disabled");
  }

  //$(".patientMob").hide();
  
  $("input:radio[name=receiveSms]").click(function() {
      if($(this).val() == 'no'){
         $("#patientPhoneNumber").attr("disabled","disabled");
      }else if($(this).val() == 'yes'){
        $("#patientPhoneNumber").removeAttr("disabled");
      }
  });  
  
  function checkValue()
  {
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $(".newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $(".newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
    }
  }
  function checkPatientReceivesms(val)
  {
   if(val=='yes'){
    $('#patientPhoneNumber').addClass('isRequired');
   }else{
     $('#patientPhoneNumber').removeClass('isRequired');
   }
  }
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
  
  function getDateOfBirth(formSource){
      var today = new Date();
      if(formSource == "ld") {
        var dob = "<?php echo $vlQueryInfo[0]['patient_dob']; ?>";
      }else{
        var dob = $("#dob").val();
      }
      if($.trim(dob) == ""){
        $("#ageInMonths").val("");
        $("#ageInYears").val("");
        return false;
      }
      var dd = today.getDate();
      var mm = today.getMonth();
      var yyyy = today.getFullYear();
      if(dd<10) {
        dd='0'+dd
      } 
      
      if(mm<10) {
       mm='0'+mm
      }
     
      splitDob = dob.split("-");
      var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
      var monthDigit = dobDate.getMonth();
      var dobYear = splitDob[2];
      var dobMonth = isNaN(monthDigit) ? 0 : (monthDigit);
      var dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      if((diff.getUTCFullYear() - 1970) == 0){
        $("#ageInMonths").val(diff.getUTCMonth()); // Gives month count of difference
      }else{
        $("#ageInMonths").val("");
      }
      $("#ageInYears").val((diff.getUTCFullYear() - 1970)); // Gives difference as year
      //console.log(diff.getUTCDate() - 1); // Gives day count of difference
    }
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

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                duplicateName=false;
                $(".removeValue").val('');
            }
        });
    }
</script>
  
 <?php
 //include('footer.php');
 ?>
