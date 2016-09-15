<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName1="activity_log";
$id=base64_decode($_GET['id']);
$vlQuery="SELECT * from vl_request_form where treament_id=$id";
$vlQueryInfo=$db->query($vlQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
$sQuery="SELECT * from r_sample_type where sample_id='".$vlQueryInfo[0]['sample_id']."'";
$sResult=$db->query($sQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);

//facility details
$facilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
$facilityResult=$db->query($facilityQuery);

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
if(isset($vlQueryInfo[0]['request_date']) && trim($vlQueryInfo[0]['request_date'])!='' && trim($vlQueryInfo[0]['request_date'])!='0000-00-00'){
 $vlQueryInfo[0]['request_date']=$general->humanDateFormat($vlQueryInfo[0]['request_date']);
}else{
 $vlQueryInfo[0]['request_date']='';
}
if(isset($vlQueryInfo[0]['date_sample_received_at_testing_lab']) && trim($vlQueryInfo[0]['date_sample_received_at_testing_lab'])!='' && trim($vlQueryInfo[0]['date_sample_received_at_testing_lab'])!='0000-00-00'){
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($vlQueryInfo[0]['date_sample_received_at_testing_lab']);
}else{
 $vlQueryInfo[0]['date_sample_received_at_testing_lab']='';
}
//Add event log
$eventType = 'view-vl-request-zm';
$action = ucwords($_SESSION['userName']).' have been viewed a request data with the sample code '.$vlQueryInfo[0]['sample_code'];
$resource = 'vl-request-zm';
$data=array(
'event_type'=>$eventType,
'action'=>$action,
'resource'=>$resource,
'date_time'=>$general->getDateTime()
);
$db->insert($tableName1,$data);
?>
<style>
  .form-control{border: none;margin-top: -3%;}
  .form-control1{border: none;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">View Vl Request</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <div class="form-inline">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="serialNo">Serial No</label>
                          <input type="text" class="form-control serialNo" value="<?php echo $vlQueryInfo[0]['serial_no'];?>"/>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province</label>
                          <input type="text" class="form-control1" style="width:100%;" value="<?php echo ucwords($facilityResult[0]['state']);?>"/>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  </label>
                        <input type="text" class="form-control1" style="width:100%;" value="<?php echo ucwords($facilityResult[0]['district']);?>"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="urgency">Urgency  </label>
                        <input type="text" class="form-control1" style="width:100%;" value="<?php echo ucwords($vlQueryInfo[0]['urgency']);?>"/>
                        </div>
                      </div>
                    </div>
                
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicName">Clinic Name </label>
                    <input type="text" class="form-control1" style="width:100%;" value="<?php echo ucwords($facilityResult[0]['facility_name']);?>"/>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicianName">Clinician Name </label>
                    <input type="text" class=" form-control1 " style="width:100%;"  value="<?php echo ucwords($vlQueryInfo[0]['lab_contact_person']);?>">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="sampleCollectionDate">Sample Collection Date</label>
                    <input type="text" class="form-control1" style="width:100%;" value="<?php echo $vlQueryInfo[0]['sample_collection_date'];?>">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="">Sample Received Date</label>
                    <input type="text" class="form-control1" style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_sample_received_at_testing_lab'];?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control1" style="width:100%;" value="<?php echo ucwords($vlQueryInfo[0]['collected_by']);?>">
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:18%">
                        <label for="sampleCode">Sample Code  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  "  style="width:100%;" value="<?php echo $vlQueryInfo[0]['sample_code'];?>">
                        </td>
                        <td style="width:16%">
                        <label for="patientFname">Patient First Name  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo ucwords($vlQueryInfo[0]['patient_name']);?>" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname </label>
                        </td>
                        <td style="width:18%">
                          <input type="text" class="form-control" style="width:100%;"  value="<?php echo ucwords($vlQueryInfo[0]['surname']);?>" >
                        </td>
                      </tr>
                      <tr>
                        <td style="width:18%">
                          <label for="gender">Gender</label>
                        </td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;"  value="<?php echo ucwords($vlQueryInfo[0]['gender']);?>" >
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_dob'];?>"  >
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_yrs'];?>">
                          
                        </td>
                      </tr>
                      <tr>
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" style="width:100%;" value="<?php echo $vlQueryInfo[0]['age_in_mnts'];?>" >
                        </td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>>
                        <input type="text" class="form-control" style="width:100%;"  value="<?php echo ucwords($vlQueryInfo[0]['is_patient_pregnant']);?>" >
                        </td>
                        
                        <td colspan="2"  class="femaleElements" <?php echo($vlQueryInfo[0]['gender'] == 'male')?'style="display:none;"':''; ?>>
                        <label for="breastfeeding">Is Patient Breastfeeding?</label>
                        <input type="text" class="form-control" style="width:100%;"  value="<?php echo ucwords($vlQueryInfo[0]['is_patient_breastfeeding']);?>" >
                        </td>
                      </tr>
                      
                      <tr>
                        <td><label for="patientArtNo">Patient OI/ART Number</label></td>
                        <td>
                          <input type="text" class="form-control" style="width: 100%;" value="<?php echo $vlQueryInfo[0]['art_no'];?>" >
                        </td>
                        <td><label for="dateOfArt">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen'];?>" >
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['current_regimen'];?>"/>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;"  value="<?php echo $vlQueryInfo[0]['patient_receive_sms'];?>" >
                        </td>
                        <td><label for="patientPhoneNumber">Mobile Number</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['patient_phone_number'];?>" /></td>
                      </tr>
                      <tr>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_date'];?>" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['last_viral_load_result'];?>" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" style="width:100%;"  value="<?php echo $vlQueryInfo[0]['viral_load_log'];?>"/></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL test</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo str_replace('_', ' ',  ucwords($vlQueryInfo[0]['vl_test_reason']));?>"/>
                          
                        </td>
                        <td><label for="drugSubstitution">Single Drug Substitution</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo str_replace('_', ' ',  ucwords($vlQueryInfo[0]['drug_substitution']));?>"/>
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
                        <td><label for="labNo">LAB No</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['lab_no'];?>" /></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo ucwords($vlQueryInfo[0]['vl_test_platform']);?>"/>
                        </td>
                        <td><label for="specimenType">Specimen type</label></td>
                        <td>
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo $sResult[0]['sample_name'];?>"/>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Date Of Result</label></td>
                        <td><input type="text" class="form-control date" style="width:100%;" value="<?php echo $vlQueryInfo[0]['request_date'];?>" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control"  style="width:100%;" value="<?php echo $vlQueryInfo[0]['result'];?>" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['log_value'];?>" /></td>
                      </tr>
                      <tr>
                        <td><label>If no result</label></td>
                        <td colspan="3">
                          <input type="text" class="form-control" style="width:100%;" value="<?php echo str_replace('_', ' ',  ucwords($vlQueryInfo[0]['rejection']));?>" />
                        </td>
                      </tr>
                      <tr>
                        <td><label for="labCommnets">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="5"><input type="text" class="form-control" style="width:100%;" value="<?php echo $vlQueryInfo[0]['comments'];?>"/> </td>
                      </tr>
                      <tr>
                        <td><label for="serialNo">Serial No.</label></td>
                        <td><input type="text" class="form-control " placeholder="Enter Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $vlQueryInfo[0]['serial_no'];?>" /></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </div>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  
 <?php
 include('footer.php');
 ?>
