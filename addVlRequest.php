<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
//get config values
$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    
$query="SELECT * from r_art_code_details where parent_art=0";
$qResult=$db->query($query);
$artCode=[];
foreach($qResult as $val){
 $aQuery="SELECT * from r_art_code_details where parent_art=".$val['art_id'];
 $aResult=$db->query($aQuery);
 foreach($aResult as $vl){
  $artCode[$val['art_code']][$vl['art_id']]=$vl['art_code'];
 }
}
$sampleTypeQuery="SELECT * FROM r_sample_type where form_identification='".$arr['vl_form']."'";
$sampleTypeResult = $db->rawQuery($sampleTypeQuery);
$sampleType='<option value="">-- Select --</option>';
foreach ($sampleTypeResult as $row) {
 $sampleType.='<option value="'.$row['sample_id'].'">'.ucwords($row['sample_name']).'</option>';
}
//sample code
$sQuery="select MAX(treament_id) FROM vl_request_form";
$sResult=$db->query($sQuery);
//print_r($sResult[0]['MAX(treament_id)']);die;
if($sResult[0]['MAX(treament_id)']!=''){
 $maxId = $sResult[0]['MAX(treament_id)']+1;
}else{
 $maxId = 1;
}
//get test status values
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$facility = '';
            $facility.="<option value=''>--select--</option>";
            foreach($fResult as $fDetails){
              $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
            }
            
$rQuery="SELECT * FROM r_sample_rejection_reasons where rejection_reason_status='active'";
$rResult = $db->rawQuery($rQuery);
$rejectReason = '';
            $rejectReason.="<option value=''>--select--</option>";
            foreach($rResult as $rDetails){
              $rejectReason .= "<option value='".$rDetails['rejection_reason_id']."'>".ucwords($rDetails['rejection_reason_name'])."</option>";
            }

?>
<link rel="stylesheet" href="assets/css/easy-autocomplete.min.css">
<script type="text/javascript" src="assets/js/jquery.easy-autocomplete.min.js"></script>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <style>
    <?php if($arr['show_date']=='no'){ ?>
         .ui-datepicker-calendar {
         display: none;
     }
     <?php } ?>
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
      #toogleResultDiv,#clearFInfo{
        display:none;
      }
   </style>
   
    <section class="content-header">
      <h1>Add VL Request</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Vl Request</li>
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
            <form class="form-horizontal" method='post'  name='addVlRequestForm' id='addVlRequestForm' autocomplete="off"  action="addVlRequestHelper.php">
              <div class="box-body">
               <div class="row">
                   <div class="col-md-12"><h4><a id="vlrfa" href="javascript:void(0);" onclick="formToggler('-');">VL Request Form Details <i class="fa fa-minus"></i></a></h4></div>
               </div>
             <div id="toogleFormDiv">
              <div class="box box-default">
            <div class="box-header with-border">
              <div class="pull-left"><h3 class="box-title">Facility Information</h3></div>
              <div class="pull-right"><a id="clearFInfo" href="javascript:void(0);" onclick="clearFacilitiesInfo();" class="btn btn-danger btn-sm" style="padding-right:10px;">Clear</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="showModal('facilitiesModal.php?type=all',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Facility <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="hidden" id="facilityId" name="facilityId"/>
                        <input type="text" class="form-control" id="facilityName" name="facilityName" placeholder="Facility" title="Please enter facility">
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State/Province</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State" />
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
                        </div>
                    </div>
                  </div> 
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="district" class="col-lg-4 control-label">District</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="district" name="district" placeholder="District" title="Please enter district" />
                        </div>
                    </div>
                  </div> 
                </div>
                <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender" class="col-lg-4 control-label">Urgency <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio" class="" id="urgencyNormal" name="urgency" value="normal" title="Please check urgency" checked="checked"> Normal
                        </label>
                        <label class="radio-inline">
                         <input type="radio" class=" " id="urgencyUrgent" name="urgency" value="urgent" title="Please check urgency" > Urgent
                        </label>
                        </div>
                    </div>
                </div>
                </div>
              </div>
            </div>
            <!-- /.box-footer-->
              
            <div class="box box-primary">
            <div class="box-header with-border">
              <div class="pull-left"><h3 class="box-title">Patient Details</h3></div>
              <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('vlRequestModal.php',1100,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="artNo" class="col-lg-4 control-label">Unique ART No. <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                          <input type="text" class="form-control" id="artNo" name="artNo" placeholder="ART Number" title="Please enter ART number"/>
                        </div>
                    </div>
                  </div>
                  
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleCode" class="col-lg-4 control-label">Sample Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Sample Code" title="Please enter the sample code" value="<?php echo "VL".date('dmY').$maxId; ?>"/>
                        </div>
                    </div>
                  </div>
                  
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="otrId" name="otrId" placeholder="Enter Other Id" title="Please enter Other Id" />
                        </div>
                    </div>
                   </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientName" name="patientName" placeholder="Patient Name" title="Please enter patient name"/>
                        </div>
                    </div>
                  </div>
                </div>
              
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="dob" name="dob" placeholder="Enter DOB" title="Enter patient date of birth"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender" class="col-lg-4 control-label">Gender <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"/> Male
                        </label>
                        <label class="radio-inline">
                         <input type="radio" class="isRequired" id="genderFemale" name="gender" value="female" title="Please check gender"/> Female
                        </label>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInYrs" class="col-lg-4 control-label">Age in years</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ageInYrs" name="ageInYrs" placeholder="Enter age in years" title="Please enter age in years" />
                        <p class="help-block"><small>If DOB Unkown</small></p>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInMtns" class="col-lg-4 control-label">Age in months</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ageInMtns" data-calendar="false" name="ageInMtns" placeholder="Enter Age in months" title="Please enter age in" />
                        <p class="help-block"><small>If age < 1 year </small></p>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="receiveSms" class="col-lg-4 control-label">Patient consent to receive SMS?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);"> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);"> No
                       </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Patient Phone No." title="Please enter patient Phone No" />
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="location" class="col-lg-4 control-label">Location/District Code</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientLocation" name="patientLocation" placeholder="Enter Patient location/district code" title="Please enter patient location/district code" />
                        </div>
                    </div>
                  </div>
                </div>
                
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestClinician" class="col-lg-4 control-label">Request Clinician</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="requestClinician" name="requestClinician" placeholder="Enter Clinician" title="Please enter clinician name"/>                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="clinicianPhone" class="col-lg-4 control-label">Phone No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="clinicianPhone" name="clinicianPhone" placeholder="Clinician Phone No." title="Please enter phone no." />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestDate" class="col-lg-4 control-label">Request Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="requestDate" name="requestDate" placeholder="Request Date" placeholder="Request Date" title="Please enter request date"/>                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlFocalPerson" class="col-lg-4 control-label">VL Focal Person</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter VL Focal Person" />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="vlPhoneNumber" name="vlPhoneNumber" placeholder="VL Focal Person Phone Number" title=" Please enter vl focal person phone number" />                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="emailHf" class="col-lg-4 control-label">Email for HF</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="justification" class="col-lg-4 control-label">Justification</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="justification" name="justification" placeholder="Enter Justification" title="Please enter justification"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="rejection" class="col-lg-4 control-label">Rejected by Clinic <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                           <input type="radio" class="isRequired" id="rejectionYes" name="rejection" value="yes" title="Please check rejection"> Yes
                        </label>
                        <label class="radio-inline">
                           <input type="radio" class="" id="rejectionNo" name="rejection" value="no" title="Please check rejection"> No
                        </label>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Rejection Facility <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired" name='rejectionFacility' id='rejectionFacility' title="Please select Facility">
                           <?php echo $facility; ?>
                         </select>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Rejection Reason <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired" name='rejectionReason' id='rejectionReason' title="Please select Reason">
                           <?php echo $rejectReason; ?>
                         </select>
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
                        <div class="col-lg-7">
                        <input type="text" class="form-control readonly" readonly='readonly' id="sampleCollectionDate" name="sampleCollectionDate" placeholder="Enter Sample Collection Date" title="Please enter hub name" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Sample Type <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired" name='sampleType' id='sampleType' title="Please select sample type">
                           <?php echo $sampleType; ?>
                         </select>
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
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="treatmentInitiatiatedOn" name="treatmentInitiatiatedOn" placeholder="Treatment Initiated On" title="Please enter treatment initiated date" />
                        </div>
                    </div>
                  </div> 
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatPeriod" class="col-lg-4 control-label">How long has this patient been on treatment ?</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="treatPeriod" name="treatPeriod" placeholder="Enter Treatment Period" title="Please enter how long has this patient been on treatment" />
                        </div>
                    </div>
                  </div>    
                                        
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7">
                        <select class="form-control" id="currentRegimen" name="currentRegimen" placeholder="Enter Current Regimen" title="Please enter current regimen">
                         <option value="">-- Select --</option>
                         <?php
                         foreach($artCode as $pKey=>$parentRow){
                         ?>
                         <optgroup label="<?php echo $pKey ?>">
                         <?php
                         foreach($parentRow as $key=>$val){
                         ?>
                          <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                         <?php
                         }
                         ?>
                         </optgroup>
                         <?php
                         }
                         ?>
                        </select>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Current Regimen Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="regimenInitiatedOn" name="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" />
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <div class="form-group">
                        <label for="treatmentDetails" class="col-lg-2 control-label">Which line of treatment is Patient on ?</label>
                        <div class="col-lg-10">
                            <textarea class="form-control" id="treatmentDetails" name="treatmentDetails" placeholder="Enter treatment details" title="Please enter treatment details"></textarea>
                        </div>
                    </div>
                  </div>   
                </div>
                
                <div class="row femaleElements">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="pregYes" class="col-lg-4 control-label">Is Patient Pregnant ?</label>
                        <div class="col-lg-7">                        
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> No
                          </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArcNo" class="col-lg-4 control-label">If Pregnant, ARC No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="arcNo" name="arcNo" placeholder="Enter ARC no." title="Please enter arc no" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                   <div class="col-md-6 femaleElements">
                    <div class="form-group">
                        <label for="breastfeeding" class="col-lg-4 control-label">Is Patient Breastfeeding?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                             <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> No
                       </label>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Patient Art No. Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control readonly hide-calendar" readonly='readonly' id="artnoDate" name="artnoDate" placeholder="Enter Patient Art No. Date" title="Please choose Art No. Date"/>
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
                            <label class="radio-inline">
                                <input type="radio" class="" id="RmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" onclick="showTesting('RmTesting');">
                                <strong>Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RmTesting hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <!--<input type="text" class="form-control" id="RmTestingSampleType" name="RmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />-->
                        <select class="form-control viralTestData" id="rmTestingSampleType" name="rmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <?php echo $sampleType; ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
                
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <input type="radio" class="" id="RepeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('RepeatTesting');">
                                <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RepeatTesting hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="repeatTestingSampleType" name="repeatTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
                        </select>
                        </div>
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
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="suspendTreatmentSampleType" name="suspendTreatmentSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
               <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <input type="radio" class="" id="switchToTDF" name="stViralTesting" value="switch" title="Switch to TDF" onclick="showTesting('switchToTDFTreatment');">
                                <strong>Switch to TDF</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="switchToTDFLastVLDate" name="switchToTDFLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="switchToTDFVlValue" name="switchToTDFVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="switchToTDFSampleType" name="switchToTDFSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
               <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <input type="radio" class="" id="missing" name="stViralTesting" value="missing" title="Missing" onclick="showTesting('missingTreatment');">
                                <strong>Missing</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row  hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="missingLastVLDate" name="missingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="missingVlValue" name="missingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="missingSampleType" name="missingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
               <div class="row">
              <div class="col-md-6">
                 <div class="form-group">
                     <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                     <div class="col-lg-7">
                     <!--<input type="text" class="form-control" id="arvAdherence" name="arvAdherence" placeholder="Enter ARV Adherence" title="Please enter ARV adherence" />-->
                     <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                      <option value="">--select--</option>
                      <option value="good">Good >= 95%</option>
                      <option value="fair">Fair (85-94%)</option>
                      <option value="poor">Poor < 85%</option>
                     </select>
                     </div>
                 </div>
               </div>
              <div class="col-md-6">
                 <div class="form-group">
                     <label for="enhanceSession" class="col-lg-4 control-label">Enhanced Sessions </label>
                     <div class="col-lg-7">
                     <select name="enhanceSession" id="enhanceSession" class="form-control" title="Please choose enhance session">
                      <option value="">--select--</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value=">3"> > 3</option>
                      <option value="missing"> Missing</option>
                     </select>
                     </div>
                 </div>
               </div>
             </div>
            </div>
            <!-- /.box-footer-->
          </div>
                
                
               </div>
                <div class="row">
                   <div class="col-md-12"><h4><a id="lra" href="javascript:void(0);" onclick="resultToggler('+');">Lab/Result Details <i class="fa fa-plus"></i></a></h4></div>
                </div>
                
                <div id="toogleResultDiv" class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Lab Details</h3>
                    <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('facilitiesModal.php?type=lab',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
                  </div>
                  
                  <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labName" class="col-lg-4 control-label">Lab Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labName" name="labName" placeholder="Enter Lab Name" title="Please enter lab name"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labContactPerson" class="col-lg-4 control-label">Lab Contact Person </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Enter Lab Contact Person Name" title="Please enter lab contact person name"/>
                        </div>
                    </div>
                   </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labPhoneNo" class="col-lg-4 control-label">Phone Number </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labPhoneNo" name="labPhoneNo" placeholder="Enter Lab Phone No." title="Please enter lab phone no."/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Sample Received at Testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Select Sample Received Date" title="Select sample received date"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Sample Testing Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleTestedOn" name="sampleTestedOn" placeholder="Select Sample Testing Date" title="Select sample testing date"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Results Dispatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Select Result Dispatched Date" title="Select result dispatched date"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="reviewedBy" class="col-lg-4 control-label">Reviewed By</label>
                        <div class="col-lg-7">
                        <input type="hidden" class="form-control readonly" id="reviewedBy" readonly='readonly' name="reviewedBy" placeholder="Enter Reviewed By Name" title="Please enter reviewed by name" value="<?php echo $_SESSION['userId'];?>"/>
                        <input type="text" class="form-control readonly" readonly="readonly" value="<?php echo $_SESSION['userName'];?>"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Reviewed Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="reviewedOn" name="reviewedOn" placeholder="Select Reviewed Date" title="Select reviewed date" value="<?php echo date('d-M-Y');?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                  <div class="form-group">
                      <label for="testMethods" class="col-lg-4 control-label">Test Methods <span class="mandatory">*</span></label>
                      <div class="col-lg-7">
                      <select name="testMethods" id="testMethods" class="form-control isRequired" title="Please choose test methods">
                       <option value="">--select--</option>
                       <option value="individual">Individual</option>
                       <option value="minipool">Minipool</option>
                       <option value="other pooling algorithm">Other Pooling Algorithm</option>
                      </select>
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
                          <input type="text" class="form-control" id="logValue" name="logValue" placeholder="Enter Log Value" title="Please enter log value"/>
                          </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="absoluteValue" class="col-lg-4 control-label">Absolute Value</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="absoluteValue" name="absoluteValue" placeholder="Enter Absolute Value" title="Please enter absolute value"/>
                          </div>
                      </div>
                    </div>
                  </div>
                   <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="textValue" class="col-lg-4 control-label">Text Value</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="textValue" name="textValue" placeholder="Enter Text Value" title="Please enter text value"/>
                          </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="result" class="col-lg-4 control-label">Result</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="result" name="result" placeholder="Enter Result" title="Please enter result"/>
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
                           <textarea class="form-control" id="comments" name="comments" row="4" placeholder="Enter Comments" title="Please enter comments"></textarea>
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
                               <option value="<?php echo $status['status_id']; ?>"><?php echo ucwords($status['status_name']);?></option>
                               <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                    </div>
                   </div>
                </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validate();return false;">Save and Next</a>
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
  
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addVlRequestForm'
    });
    $("#saveNext").val('save');
    if(flag){
     $.blockUI();
      document.getElementById('addVlRequestForm').submit();
    }
  }
  function validate(){
    flag = deforayValidator.init({
        formId: 'addVlRequestForm'
    });
    $("#saveNext").val('next');
    if(flag){
     $.blockUI();
      document.getElementById('addVlRequestForm').submit();
    }
  }
  
  $(document).ready(function() {
   <?php if($arr['show_date']=='yes'){ ?>
     $('#artnoDate').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "hh:mm TT",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
      }).click(function(){
    	$('.ui-datepicker-calendar').show();
     });
     <?php }else{ ?>
     $('.ui-datepicker-calendar').hide();
     $('#artnoDate').datepicker({
      changeMonth: true,
      changeYear: true,
      showButtonPanel:true,
      dateFormat: 'M-yy',
      timeFormat: "hh:mm TT",
      onChangeMonthYear: function(year, month, widget) {
            setTimeout(function() {
               $('.ui-datepicker-calendar').hide();
            });
    	},
      onClose: function(dateText, inst) {
       var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
    		var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
       },
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
      }).click(function(){
    	$('.ui-datepicker-calendar').hide();
    });
     <?php } ?>
     $('.date').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "hh:mm TT",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
    	$('.ui-datepicker-calendar').show();
    });
     $('#sampleCollectionDate').datetimepicker({
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
    
    function checkPatientIsPregnant(val){
     if(val=='yes'){
      $('#arcNo').addClass('isRequired');
     }else{
       $('#arcNo').removeClass('isRequired');
     }
    }
    function checkPatientIsBreastfeeding(val){
     if(val=='yes'){
      $('#arvAdherence').addClass('isRequired');
     }else{
       $('#arvAdherence').removeClass('isRequired');
     }
    }
    function showTesting(chosenClass){
     $(".viralTestData").val('');
     $(".hideTestData").hide();
     $("."+chosenClass).show();
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
    
    function setFacilityDetails(fDetails){
      $("#facilityId").val("");
      $("#facilityName").val("");
      $("#state").val("");
      $("#hubName").val("");
      facilityArray = fDetails.split("##");
      $("#facilityId").val(facilityArray[0]);
      $("#facilityName").val(facilityArray[1]);
      $("#state").val(facilityArray[2]);
      $("#hubName").val(facilityArray[3]);
      $("#district").val(facilityArray[6]);
      $("#facilityName,#state,#hubName,#district").prop('readonly',true);
      $("#clearFInfo").show();
    }
    
    function clearFacilitiesInfo(){
      $("#facilityId").val("");
      $("#facilityName").val("");
      $("#state").val("");
      $("#hubName").val("");
      $("#district").val("");
      $("#facilityName,#state,#hubName,#district").prop('readonly',false);
      $("#clearFInfo").hide();
    }
    
    function setPatientDetails(ptDetails){
      $("#artNo").val("");
      $("#sampleCode").val("");
      $("#otrId").val("");
      $("#patientName").val("");
      $("#dob").val("");
      $("#genderMale").prop('checked',false);
      $("#genderFemale").prop('checked',false);
      $(".femaleElements").show();
      $("#ageInYrs").val("");
      $("#ageInMtns").val("");
      $("#patientPhoneNumber").val("");
      $("#patientLocation").val("");
      patientArray = ptDetails.split("##");
      $("#artNo").val(patientArray[0]);
      $("#sampleCode").val(patientArray[1]);
      $("#otrId").val(patientArray[2]);
      $("#patientName").val(patientArray[3]);
      $("#dob").val(patientArray[4]);
      if(patientArray[5].toLowerCase() == 'male'){
        $("#genderMale").prop('checked',true);
        $(".femaleElements").hide();
      }else if(patientArray[5].toLowerCase() == 'female'){
        $("#genderFemale").prop('checked',true);
        $(".femaleElements").show();
      }
      $("#ageInYrs").val(patientArray[6]);
      $("#ageInMtns").val(patientArray[7]);
      $("#patientPhoneNumber").val(patientArray[8]);
      $("#patientLocation").val(patientArray[9]);
    }
    
    $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }
    });
    function setFacilityLabDetails(fDetails){
      $("#labName").val("");
      facilityArray = fDetails.split("##");
      $("#labName").val(facilityArray[1]);
    }
    function checkPatientReceivesms(val)
    {
     if(val=='yes'){
      $('#patientPhoneNumber').addClass('isRequired');
     }else{
       $('#patientPhoneNumber').removeClass('isRequired');
     }
    }
  </script>
 <?php
 include('footer.php');
 ?>
