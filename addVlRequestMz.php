<?php
ob_start();
//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}
//sample type
$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);
//user details
$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons";
$rejectionResult = $db->rawQuery($rejectionQuery);
//get vltest reason details
$testRQuery="SELECT * FROM r_vl_test_reasons";
$testReason = $db->rawQuery($testRQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$aQuery="SELECT * from r_art_code_details where nation_identifier='mz'";
$aResult=$db->query($aQuery);
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
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
            <form class="form-inline" method='post'  name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="addVlRequestHelperMz.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="serialNo">Order Number <span class="mandatory">*</span></label>
                          <input type="text" class="form-control checkNum isRequired" id="orderNo" name="orderNo" placeholder="Enter Form Order No." title="Please enter Order Number" style="width:100%;" onblur="checkNameValidation('vl_request_form','serial_no',this,null,'This order number already exists.Try another number',null)" />
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3 col-sm-offset-2 col-md-offset-2" style="padding:10px;">
                        <div class="form-group">
                        <label for="urgency">Lab Number&nbsp;&nbsp;&nbsp;&nbsp;<span class="mandatory">*</span></label>
                        <input type="text" class="form-control isRequired" id="" name="labNumber" placeholder="Enter Form Lab No." title="Please enter lab number" style="width:100%;"/>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" ><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                            <option value=""> -- Select -- </option>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                      <label for="clinicName">Health Centre <span class="mandatory">*</span></label>
                        <select class="form-control isRequired" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                          <option value=""> -- Select -- </option>
                        </select>
                      </div>
                    </div>
                      <div class="col-xs-3 col-md-3">
                      <div class="form-group">
                      <label for="clinicName">Consultation</label>
                        <input type="text" class="form-control " id="consulation" name="consulation" placeholder="Enter Consultation" title="" style="width:100%;"/>
                      </div>
                    </div>
                    </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:16%"><label for="patientFname">Patient First Name <span class="mandatory">*</span></label></td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired" name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" >
                        </td>
                        <td style="width:10%"><label for="surName">Surname  <span class="mandatory">*</span></label></td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" >
                        </td>
                        <td><label for="patientNo">Patient Number</label></td>
                        <td>
                          <input type="text" class="form-control" name="patientNo" id="patientNo" placeholder="Patient Number" title="Enter Patient Number" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender">Gender &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                          </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender"> Not Recorded
                          </label>
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;">
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender"> < 5 Years &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="lessFiveYes" name="lessThanFiveYears" value="yes" title="Please check "> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="lessFiveYes" name="lessThanFiveYears" value="no" title="Please check"> No
                          </label>
                        </td>
                        <td class="femaleElements"><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements">
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant"> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant"> No
                          </label>
                        </td>
                         <td class="femaleElements"><label for="breastfeeding">Is Patient Breastfeeding?</label></td>
                         <td class="femaleElements">
                          <label class="radio-inline">
                             <input type="radio" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding">Yes
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding">No
                          </label>
                         </td>
                      </tr>
                      <tr>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS"> No
                          </label>
                        </td>
                        <td><label for="patientPhoneNumber" class="">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label for="dateOfArtInitiation">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiation" title="Date Of ART Initiation" onchange="checkARTInitiationDate();checkLastVLTestDate();" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for="firstLine">1st Line</label>
                          <label class="radio-inline">
                             <input type="radio" class="" id="firstLineYes" name="firstLine" value="yes" title="Choose first line"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="firstLineNo" name="firstLine" value="no" title="Choose first line"> No
                          </label>
                        </td>
                        <td>
                          <input type="text" class="form-control" name="firstLineWhich" id="firstLineWhich" placeholder="Which" title="Enter Which" style="width:100%;" >
                        </td>
                        <td><label for="firstVl" class="">1st Viral Load</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="firstVlYes" name="firstVl" value="yes" title="Patient consent to receive SMS"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="firstVlNo" name="firstVl" value="no" title="Patient consent to receive SMS"> No
                          </label>
                        </td>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td>
                          <label for="secondLine">2nd Line</label>
                          <label class="radio-inline">
                             <input type="radio" class="" id="secondLineYes" name="secondLine" value="yes" title="Choose second line"/> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="secondLineNo" name="secondLine" value="no" title="Choose second line"/> No
                          </label>
                        </td>
                        <td>
                          <input type="text" class="form-control" name="secondLineWhich" id="secondLineWhich" placeholder="Which" title="Enter Which" style="width:100%;" >
                        </td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL Request</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($testReason as $reason){
                              ?>
                              <option value="<?php echo $reason['test_reason_name'];?>"><?php echo ucwords($reason['test_reason_name']);?></option>
                              <?php
                            }
                            ?>
                           </select>
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                            <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" onchange="checkArtRegimenValue();">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($aResult as $parentRow){
                              ?>
                               <option value="<?php echo $parentRow['art_code']; ?>"><?php echo $parentRow['art_code']; ?></option>
                              <?php
                              }
                              ?>
                              <option value="other">Other</option>
                            </select>
                        </td>
                        <td class="newArtRegimen" style="display: none;"><label for="newArtRegimen">New ART Regimen</label><span class="mandatory">*</span></td>
                        <td class="newArtRegimen" style="display: none;">
                          <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New Art Regimen" title="New Art Regimen" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleCollectionDate">Sample Collection Date </label></td>
                        <td><input type="text" class="form-control " style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date"></td>
                        <td><label for="collectionPlace">Place Of Collection</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" name="collectionPlace" id="collectionPlace" placeholder="Collection place" title="Enter sample collection place"></td>
                        <td><label for="collectionType">Collection type </label></td>
                        <td>
                          <select name="collectionType" id="collectionType" class="form-control" title="Please choose collection type" style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <option value="venous">Venous</option>
                            <option value="finger print">Finger Print</option>
                           </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="technologistName">Technologist Name </label></td>
                        <td><input type="text" class="form-control" style="width:100%;" name="technologistName" id="technologistName" placeholder="Technologist Name" title="Technologist Name" ></td>
                      </tr>
                      <tr>
                        <td><label for="sampleReceivedBy">Sample Received by </label></td>
                        <td>
                          <input type="text" name="sampleReceivedBy" style="width:100%;" id="sampleReceivedBy" class="form-control" title="Please enter sample received by" placeholder="Received By"/>
                        </td>
                        <td><label for="sampleReceivedDate">Sample Received On</label></td>
                        <td><input type="text" class="form-control" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" placeholder="Sample Received Date" title="Please select sample received date"></td>
                        <td><label for="receiveSms">Sample Process </label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS"> No
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="dateOfProcessing">Date Of Processing </label></td>
                        <td><input type="text" class="form-control" style="width:100%;" name="dateOfProcessing" id="dateOfProcessing" placeholder="Date Of Processing" title="Please select processing date"></td>
                        <td colspan="2"><label>If Sample not processed what is the reason</label></td>
                        <td colspan="2">
                          <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($rejectionResult as $reject){
                              ?>
                              <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                              <?php
                            }
                            ?>
                           </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Sample Type</label></td>
                        <td>
                          <select name="sampleType" id="sampleType" class="form-control" title="Please choose sample type" style="width:200px;">
                            <option value=""> -- Select -- </option>
                              <?php
                              foreach($sResult as $name){
                               ?>
                               <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                               <?php
                              }
                              ?>
                           </select>
                        </td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;"/></td>
                      </tr>
                      <tr>
                        <td><label>Approved By</label></td>
                         <td>
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
                         </td>
                         <td><label for="labComments">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="4"><textarea class="form-control" name="labComments" id="labComments" title="Enter lab comments" style="width:100%"></textarea></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <input type="hidden" name="formId" id="formId" value="4"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateSaveNow();return false;">Save and Next</a>
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
   $('#sampleCollectionDate,#sampleReceivedDate,#dateOfProcessing').mask('99-aaa-9999 99:99');
   
   $('#sampleCollectionDate,#sampleReceivedDate,#dateOfProcessing').datetimepicker({
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
      <?php
      if($cSampleResult[0]['value']=='auto'){
        ?>
        pNameVal = pName.split("##");
        sCode = '<?php echo date('Ymd');?>';
        sCodeKey = '<?php echo $maxId;?>';
        $(".serialNo1,.serialNo,.reqBarcode").val(pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val(pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
        <?php
      }
      ?>
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
      $.post("getFacilityForClinic.php", {dName:dName,cliName:cName},
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
    $.unblockUI();
  }
  function checkArtRegimenValue()
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
  $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }else if($(this).val() == 'not_recorded'){
        $(".femaleElements").show();
      }
  });
  $("#patientPhoneNumber").attr("disabled","disabled");
  $("input:radio[name=receiveSms]").click(function() {
      if($(this).val() == 'no'){
         $("#patientPhoneNumber").attr("disabled","disabled");
      }else if($(this).val() == 'yes'){
        $("#patientPhoneNumber").removeAttr("disabled");
      }
  });
</script>
  
 <?php
 //include('footer.php');
 ?>
