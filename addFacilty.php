<?php
include('header.php');
include('./includes/MysqliDb.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <style>
    .hide-calendar .ui-datepicker-calendar {
    display: none;
}
   </style>
    <section class="content-header">
      <h1>Add Facility</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Facility</li>
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
            <form class="form-horizontal" method='post'  name='addFacilityForm' id='addFacilityForm' autocomplete="off" action="addFacilityHelper.php">
              <div class="box-body">                 
              <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Facility Information</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Health Facility Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityName" name="facilityName" placeholder="Health Facility Name" title="Please enter facility name" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="country" class="col-lg-4 control-label">Country</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State" />
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Hub Name</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
                        </div>
                    </div>
                  </div>                   
                </div>              
              </div>
            </div>
            <!-- /.box-footer-->
          </div>
              
                  <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Patient Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="artNo" class="col-lg-4 control-label">Unique ART No. <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="artNo" name="artNo" placeholder="ART Number" title="Please enter art number" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="patientName" name="patientName" placeholder="patient Name" title="Please enter patient name"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="dob" class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="dob" name="dob" placeholder="Enter DOB" title="Enter patient date of birth"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="otrId" name="otrId" placeholder="Enter Other Id" title="Please enter Other Id" />
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
                        <p class="help-block"><small>If age < 2 years </small></p>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="genderMale" class="col-lg-4 control-label">Gender</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
							<input type="radio" class="isRequired" id="genderMale" name="gender" value="male" title="Please check gender"> Male
						</label>
						<label class="radio-inline">
							<input type="radio" id="genderFemale" name="gender" value="female" title="Please check gender"> Female
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
                        <label for="sampleDate" class="col-lg-4 control-label">Sample Collected On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="fromDate" name="sampleDate" placeholder="Choose Sample Collection" title="Please enter hub name" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
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
                        <label for="treatPeriod" class="col-lg-4 control-label">How long has this patient been on treatment ?</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="treatPeriod" name="treatPeriod" placeholder="Enter Treatment Period" title="Please enter how long has this patient been on treatment" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiatiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="treatmentInitiatiatedOn" name="treatmentInitiatiatedOn" placeholder="Treatment Initiatiated On" title="Please enter treatment initiatiated date" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control " id="currentRegimen" name="currentRegimen" placeholder="Enter Current Regimen" title="Please enter current regimen" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="regimenInitiatedOn" class="col-lg-4 control-label">Current Regimen Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="regimenInitiatedOn" name="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" />
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
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="pregYes" class="col-lg-4 control-label">Is Patient Pregnant ?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
							<input type="radio" class="isRequired" id="pregYes" name="pregnant" value="yes" title="Is Patient Pregnant"> Yes
						</label>
						<label class="radio-inline">
							<input type="radio" id="pregNo" name="pregnant" value="no" title="Is Patient Pregnant"> No
						</label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArcNo" class="col-lg-4 control-label">If Pregnant, ARC No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ArcNo" name="ArcNo" placeholder="Enter ARC no." title="Please enter arc no" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="breastfeeding" class="col-lg-4 control-label">Is Patient Breastfeeding?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
							<input type="radio" class="isRequired" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding"> Yes
						</label>
						<label class="radio-inline">
							<input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding"> No
						</label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ArvAdherence" name="ArvAdherence" placeholder="Enter ARV Adherence" title="Please enter ARV adherence" />
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
              <small>(please tick one):(To be completed by clinician)</small>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="RmTesting" class="col-lg-1 control-label"></label>
                            <div class="col-lg-7">
                            <label class="radio-inline">
                                <input type="radio" class="isRequired" id="RmTesting" name="viralTesting" value="Routine Monitoring" title="Please check routine monitoring" onclick="showTesting('RmTesting');"> <strong>Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RmTesting hide">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RmTestingLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="RmTestingLastVLDate" name="RmTestingLastVLDate" placeholder="Enter Last VL Date" title="Please enter Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="RmTestingVlValue" name="RmTestingLastValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="RmTestingSampleType" name="RmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />
                        </div>
                    </div>
                  </div>                   
                </div>
                
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="RepeatTesting" class="col-lg-1 control-label"></label>
                            <div class="col-lg-11">
                            <label class="radio-inline">
                                <input type="radio" class="isRequired" id="RepeatTesting" name="viralTesting" value="male" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('RepeatTesting');"> <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RepeatTesting hide">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RepeatTestingLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="RepeatTestingLastVLDate" name="RmTestingLastVLDate" placeholder="Enter Last VL Date" title="Please enter Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RepeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="RepeatTestingVlValue" name="RepeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="RepeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="RepeatTestingSampleType" name="RepeatTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />
                        </div>
                    </div>
                  </div>                   
                </div>
                
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="suspendTreatment" class="col-lg-1 control-label"></label>
                            <div class="col-lg-11">
                            <label class="radio-inline">
                                <input type="radio" class="isRequired" id="suspendTreatment" name="viralTesting" value="male" title="Suspect Treatment Failure" onclick="showTesting('suspendTreatment');"> <strong>Suspect Treatment Failure</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row suspendTreatment hide">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Enter Last VL Date" title="Please enter Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="suspendTreatmentSampleType" name="suspendTreatmentSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />
                        </div>
                    </div>
                  </div>                   
                </div>
            </div>
            <!-- /.box-footer-->
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
                        <input type="text" class="form-control" id="clinicianPhone" name="clinicianPhone" placeholder="Phone No." title="Please enter phone no." />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestDate" class="col-lg-4 control-label">Request Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="requestDate" name="requestDate" placeholder="requestDate" placeholder="Request Date" title="Please enter request date"/>                    
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
                        <label for="VLPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="VLPhoneNumber" name="VLPhoneNumber" placeholder="VL Focal Person Phone Number" title=" Please enter vl focal person phone number" />                    
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
                        <label for="sampleReceivedOn" class="col-lg-4 control-label">Date sample received at testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received On" title="Please enter sample received on" />                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="despachedOn" class="col-lg-4 control-label">Date Results Despatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="despachedOn" name="despachedOn" placeholder="Results Despatched" title="Please enter hub name" />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="rejection" class="col-lg-4 control-label">Rejection</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
							<input type="radio" class="isRequired" id="rejectionYes" name="rejection" value="yes" title="Please check rejection"> Yes
						</label>
						<label class="radio-inline">
							<input type="radio" id="rejectionNo" name="rejection" value="no" title="Please check rejection"> No
						</label>
                        </div>
                    </div>
                  </div>                                    
                </div>                
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="facilities.php" class="btn btn-default"> Cancel</a>
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
        formId: 'addFacilityForm'
    });
    
    if(flag){
      document.getElementById('addFacilityForm').submit();
    }
  }
  
    $(document).ready(function() {
        $('.dateTime').datetimepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-M-yy',
        timeFormat: "hh:mm TT",
        yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
        });
        
        
         $('#ageInMtns').datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'MM yy',
        onClose: function(dateText, inst) { 
            
            
            
            function isDonePressed(){
                            return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                        }

                        if (isDonePressed()){

                            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                            $(this).datepicker('setDate', new Date(year, month, 1));
                             console.log('Done is pressed')

                        }
            
            
          
        },
          beforeShow: function() { 
            
            
           $('#ui-datepicker-div').addClass('hide-calendar');
          
        }
        
    });
       
       });
    
    function showTesting(viralTestingClass){
      $('.RmTesting').addClass('hide');
       $('.RepeatTesting').addClass('hide');
        $('.suspendTreatment').addClass('hide');
      if(viralTestingClass=='RmTesting'){
        $('.RmTesting').removeClass('hide');
      }
       if(viralTestingClass=='RepeatTesting'){
        $('.RepeatTesting').removeClass('hide');
      }
       if(viralTestingClass=='suspendTreatment'){
        $('.suspendTreatment').removeClass('hide');
      }
    }
    
  </script>
 <?php
 include('footer.php');
 ?>
