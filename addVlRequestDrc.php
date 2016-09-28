  <?php
    ob_start();
    include('General.php');
    //get province list
    $pdQuery="SELECT * from province_details";
    $pdResult=$db->query($pdQuery);
    //get lab facility list
    $fQuery="SELECT * FROM facility_details where status='active'";
    $fResult = $db->rawQuery($fQuery);
    $province = "";
    $province.="<option value=''> -- Select -- </option>";
    foreach($pdResult as $provinceName){
      $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
    }
    $facility = "";
    $facility.="<option value=''> -- Select -- </option>";
    foreach($fResult as $fDetails){
      $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
    }
    ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM</h1>
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
            <form class="form-inline" method="post" name="vlRequestForm" id="vlRequestForm" autocomplete="off" action="">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">1. Réservé à la structure de soins</h3>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information on the care structure</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="province">Province </label></td>
                                <td>
                                    <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                        <?php echo $province; ?>
                                    </select>
                                </td>
                                <td><label for="clinicName">Health Zone </label></td>
                                <td>
                                    <select class="form-control isRequired" name="clinicName" id="clinicName" title="Please choose health zone" onchange="getfacilityProvinceDetails(this);" style="width:100%;">
                                        <?php echo $facility; ?>
                                    </select>
                                </td>
                                <td><label for="service">Structure/Service </label></td>
                                <td>
                                    <input type="text" class="form-control" id="service" name="service" placeholder="Structure/Service" title="Please enter structure/service" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="applicant">Applicant Name </label></td>
                                <td>
                                    <input type="text" class="form-control" id="applicant" name="applicant" placeholder="Applicant Name" title="Please enter applicant name" style="width:100%;"/>
                                </td>
                                <td><label for="telePhone">Telephone no. </label></td>
                                <td>
                                    <input type="text" class="form-control" id="telePhone" name="telePhone" placeholder="Telephone no." title="Please enter telephone no." style="width:100%;"/>
                                </td>
                                <td><label for="supportPartner">Support Partner</label></td>
                                <td>
                                    <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Support Partner" title="Please enter support partner name" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date of Demand </label></td>
                                <td colspan="5">
                                    <input type="text" class="form-control" id="dateOfDemand" name="dateOfDemand" placeholder="dd/mm/yyyy" title="Please enter date of demand" style="width:21%;"/>
                                </td>
                            </tr>
                        </table>
                        <div class="box-header with-border">
                            <h3 class="box-title">Patient information</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="">DOB </label></td>
                                <td>
                                    <input type="text" class="form-control" id="dob" name="dob" placeholder="dd/mm/yyyy" title="Please enter dob" style="width:100%;"/>
                                </td>
                                <td><label for="age">Age </label></td>
                                <td>
                                    <input type="text" class="form-control" id="age" name="age" placeholder="month/year" title="Please enter age" style="width:100%;"/>
                                </td>
                                <td><label for="sex">Sex </label></td>
                                <td style="width:20%;">
                                    <label class="radio-inline">M</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender">
                                    </label>
                                    <label class="radio-inline">F</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="patientArtNo">Patient Code </label></td>
                                <td>
                                    <input type="text" class="form-control" id="patientArtNo" name="patientArtNo" placeholder="Patient Code" title="Please enter patient code" style="width:100%;"/>
                                </td>
                                <td><label for="">Date of commencement of ARV </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="dd/mm/yy" title="Please enter date of arv commencement" style="width:60%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6"><label>ARV Current Regimen</label></td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline">AZT-3TC-NVP</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="azt-3tc-nvp" name="currentRegimen" value="azt-3tc-nvp" title="Please check current regimen">
                                    </label>
                                    <label class="radio-inline">TDF-3TC-NVP</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="tdf-3tc-nvp" name="currentRegimen" value="tdf-3tc-nvp" title="Please check current regimen">
                                    </label>
                                    <label class="radio-inline">AZT-3TC-EFV</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="azt-3tc-efv" name="currentRegimen" value="azt-3tc-efv" title="Please check current regimen">
                                    </label>
                                    <label class="radio-inline">TDF-3TC-EFV</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="tdf-3tc-efv" name="currentRegimen" value="tdf-3tc-efv" title="Please check current regimen">
                                    </label>
                                    <label class="radio-inline">ABC-DDI-LPV/r</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="abc-ddi-lpv" name="currentRegimen" value="abc-ddi-lpv" title="Please check current regimen">
                                    </label>
                                    <label class="radio-inline">AZT-3TC-LPV/r</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="azt-3tc-lpv" name="currentRegimen" value="azt-3tc-lpv" title="Please check current regimen">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="paitentOtherDetail">Other, Please specify </label></td>
                                <td colspan="5">
                                    <textarea class="form-control" id="paitentOtherDetail" name="paitentOtherDetail" placeholder="Other" title="Please enter other if available" style="width:60%;height:60px !important;"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="paitentOtherDetail">Has Patient Already Changed Treatment Regimen?  </label></td>
                                <td colspan="2">
                                    <label class="radio-inline">Yes</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="changedRegimenYes" name="changedRegimen" value="changedRegimenYes" title="Please check changed regimen">
                                    </label>
                                    <label class="radio-inline">No</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="changedRegimenNo" name="changedRegimen" value="changedRegimeYes" title="Please check changed regimen">
                                    </label>
                                </td>
                                <td><label for="">Reason for Change of ARV Regimen </label></td>
                                <td colspan="2">
                                    <input type="text" class="form-control" id="reasonForArvRegimenChange" name="reasonForArvRegimenChange" placeholder="Reason for ARV Regimen Change" title="Please enter reason for arv regimen change" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date of Change of ARV Regimen </label></td>
                                <td colspan="5">
                                    <input type="text" class="form-control" id="dateOfArvRegimenChange" name="dateOfArvRegimenChange" placeholder="dd/mm/yy" title="Please enter date of arv regimen change" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="reasonForRequest">Reason for the request </label></td>
                                <td colspan="3">
                                    <label class="radio-inline">Routine Check</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="routineCheck" name="reasonForRequest" value="routineCheck" title="Please check reason for the request">
                                    </label>
                                    <label class="radio-inline">Failure Suspicion Therapeutics</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="failureSuspicionTherapeutics" name="reasonForRequest" value="failureSuspicionTherapeutics" title="Please check reason for the request">
                                    </label>
                                </td>
                                <td><label for="viralLoad">Viral Load</label></td>
                                <td>
                                    <input type="text" class="form-control" id="viralLoad" name="viralLoad" placeholder="Viral Load" title="Please enter viral load" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="vlOtherDetail">Other, Please specify </label></td>
                                <td colspan="5">
                                    <textarea class="form-control" id="vlOtherDetail" name="vlOtherDetail" placeholder="Other" title="Please enter other if available" style="width:60%;height:60px !important;"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="lastVlResult">Last Viral Load Result</label></td>
                                <td colspan="5">
                                    <input type="text" class="form-control" id="lastVlResult" name="lastVlResult" placeholder="Last VL Result" title="Please enter last vl result" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date of Last Viral Load Result</label></td>
                                <td colspan="5">
                                    <input type="text" class="form-control" id="dateOfLastVlResult" name="dateOfLastVlResult" placeholder="dd/mm/yyyy" title="Please enter last vl result" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6"><label class="radio-inline" style="margin:0;padding:0;">To be completed by the requesting service in the care structure</label></td>
                            </tr>
                        </table>
                        <div class="box-header with-border">
                            <h3 class="box-title">Sampling Information</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="">Withdrawal Date</label></td>
                                <td>
                                    <input type="text" class="form-control" id="dateOfWithdrawal" name="dateOfWithdrawal" placeholder="dd/mm/yyyy" title="Please enter sample withdrawal date" style="width:100%;"/>
                                </td>
                                <td><label for="">Time of Sampling</label></td>
                                <td>
                                    <input type="text" class="form-control" id="timeOfSampling" name="timeOfSampling" placeholder="hh/mm" title="Please enter time of sampling" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Sample Type</label></td>
                                <td colspan="3">
                                    <label class="radio-inline">Whole Blood</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="wholeBlood" name="sampleType" value="wholeBlood" title="Please check sample type">
                                    </label>
                                    <label class="radio-inline">Plasma</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="plasma" name="sampleType" value="plasma" title="Please check sample type">
                                    </label>
                                    <label class="radio-inline">DBS</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="dbs" name="sampleType" value="dbs" title="Please check sample type">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">If Plasma, Storage Temperature</label></td>
                                <td>
                                    <input type="text" class="form-control" id="storageTemperature" name="storageTemperature" placeholder="Storage Temperature" title="Please enter storage temperature" style="width:100%;"/>
                                </td>
                                <td><label for="">Duration of the Conservation</label></td>
                                <td>
                                    <input type="text" class="form-control" id="duationOfConservation" name="duationOfConservation" placeholder="day/hour" title="Please enter duration of conservation" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Departure Date in Labo Biomol</label></td>
                                <td>
                                    <input type="text" class="form-control" id="departureDateInLaboBiomol" name="departureDateInLaboBiomol" placeholder="dd/mm/yyyy" title="Please enter departure date" style="width:100%;"/>
                                </td>
                                <td><label for="">Departure Time in Labo Biomol</label></td>
                                <td>
                                    <input type="text" class="form-control" id="departureTimeInLaboBiomol" name="departureTimeInLaboBiomol" placeholder="hh/mm" title="Please enter departure time" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">To be completed by the sampler</label></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">2. RESERVED FOR THE LABORATORY OF MOLECULAR BIOLOGY</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="">Date of Sample Receipt</label></td>
                                <td>
                                    <input type="text" class="form-control" id="dateOfSampleReceipt" name="dateOfSampleReceipt" placeholder="dd/mm/yyyy" title="Please enter sample receipt date" style="width:100%;"/>
                                </td>
                                <td><label for="">Sample Reception Time</label></td>
                                <td>
                                    <input type="text" class="form-control" id="timeOfSampleReceipt" name="timeOfSampleReceipt" placeholder="hh/mm" title="Please enter sample receipt time" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Status</label></td>
                                <td colspan="3">
                                    <label class="radio-inline">Sample Accepted</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="sampleAccepted" name="status" value="sampleAccepted" title="Please check sample status">
                                    </label>
                                    <label class="radio-inline">Sample Rejected</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;">
                                        <input type="radio" class="" id="sampleRejected" name="status" value="sampleRejected" title="Please check sample status">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="reasonForRejection">Reason for Rejection </label></td>
                                <td colspan="3">
                                    <textarea class="form-control" id="reasonForRejection" name="reasonForRejection" placeholder="Reason for Rejection" title="Please enter reason for rejection" style="width:60%;height:60px !important;"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Lab Code</label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control" id="labCode" name="labCode" placeholder="Lab Code" title="Please enter lab code" style="width:34%;"/>
                                </td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date of Completion of the Viral Load</label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control" id="dateOfViralLoadCompletion" name="dateOfViralLoadCompletion" placeholder="dd/mm/yyyy" title="Please enter date of viral load completion" style="width:34%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Testing Platform</label></td>
                                <td colspan="3">
                                    <select class="form-control" id="testingPlatform" name="testingPlatform" title="Please select testing platform">
                                        <option value=""> -- Select -- </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Result</label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control" id="result" name="result" placeholder="Result" title="Please enter result" style="width:34%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">To be completed by the service conducting the viral load</label></td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date of Result</label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control" id="dateOfResult" name="dateOfResult" placeholder="dd/mm/yyyy" title="Please enter date of result" style="width:34%;"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <input type="hidden" name="formId" id="formId" value="3"/>
                
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
     changeProvince = true;
     changeFacility = true;
     
     function getfacilityDetails(obj){
      var pName = $("#province").val();
      var cName = $("#clinicName").val();
      if($.trim(pName)!='' && changeProvince && changeFacility){
        changeFacility = false;
      }
      if($.trim(pName)!='' && changeProvince){
            $.post("getFacilityForClinic.php", { pName : pName},
            function(data){
                if(data!= ""){   
                  details = data.split("###");
                  $("#clinicName").html(details[0]);
                }
            });
      }else if($.trim(pName)=='' && $.trim(cName)==''){
        changeProvince = true;
        changeFacility = true; 
        $("#province").html("<?php echo $province;?>");
        $("#clinicName").html("<?php echo $facility;?>");
      }
    }
    
    function getfacilityProvinceDetails(obj){
        var pName = $("#province").val();
        var cName = $("#clinicName").val();
        if($.trim(cName)!='' && changeProvince && changeFacility){
          changeProvince = false;
        }
        if($.trim(cName)!='' && changeFacility){
          $.post("getFacilityForClinic.php", { cName : cName},
          function(data){
              if(data!= ""){
                details = data.split("###");
                $("#province").html(details[0]);
              }
          });
        }else if($.trim(pName)=='' && $.trim(cName)==''){
           changeFacility = true;
           changeProvince = true;
           $("#province").html("<?php echo $province;?>");
           $("#clinicName").html("<?php echo $facility;?>");
        }
    }
  </script>
  
 <?php
 //include('footer.php');
 ?>
