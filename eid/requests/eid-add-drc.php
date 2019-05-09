<?php
// imported in eid-add-request.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);

// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * from province_details";
if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Sélectionner -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}
//$facility = "";
$facility = "<option value=''> -- Sélectionner -- </option>";
foreach ($fResult as $fDetails) {
    $facility .= "<option value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . "</option>";
}

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> EARLY INFANT DIAGNOSIS (EID) LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add EID Request</li>
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
            <form class="form-horizontal" method="post" name="addEIDRequestForm" id="addEIDRequestForm" autocomplete="off" action="eid-add-request-helper.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                          <h3 class="box-title">A. Réservé à la structure de soins</h3>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur la structure de soins</h3>

                        </div>
                        <!-- <h4>exemple de code</h4> -->
                        <!--<h4 style="display:none;" id="sampleCodeValue"></h4>-->
                        <table class="table" style="width:100%">
                            <tr>
                              <?php if ($sarr['user_type'] == 'remoteuser') {?>
                                <td><label for="sampleCode">Échantillon ID </label></td>
                                <td>
                                  <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                  <input type="hidden" id="sampleCode" name="sampleCode"/>
                                </td>
                              <?php } else {?>
                                <td><label for="sampleCode">Échantillon ID </label><span class="mandatory">*</span></td>
                                <td>
                                  <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Échantillon id" title="Please enter échantillon id" style="width:100%;" onchange="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode; ?>',this.id,null,'The échantillon id that you entered already exists. Please try another échantillon id',null)"/>
                                </td>
                              <?php }?>
                                <td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                        <?php echo $province; ?>
                                    </select>
                                </td>
                                <td><label for="district">Zone de Santé </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                      <option value=""> -- Sélectionner -- </option>
                                    </select>
                                </td>
                                <td><label for="clinicName">Nom de l'installation </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired " name="clinicName" id="clinicName" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                      <?php echo $facility; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="supportPartner">Partnaire d'appui </label></td>
                                <td>
                                  <!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" style="width:100%;"/> -->
                                  <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php
                                        foreach ($implementingPartnerList as $implementingPartner) {
                                      ?>
                                        <option value="<?php echo base64_encode($implementingPartner['i_partner_id']); ?>"><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                      <?php }?>
                                   </select>
                                </td>
                                <td><label for="fundingSource">Source de Financement</label></td>
                                <td>
                                    <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                        <?php
                                            foreach ($fundingSourceList as $fundingSource) {
                                        ?>
                                        <option value="<?php echo base64_encode($fundingSource['funding_source_id']); ?>"><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                        <?php }?>
                                    </select>
                                </td>
                                <?php if ($sarr['user_type'] == 'remoteuser') {?>
                              <!-- <tr> -->
                                  <td><label for="labId">Nom du Laboratoire <span class="mandatory">*</span></label> </td>
                                  <td>
                                      <select name="labId" id="labId" class="form-control isRequired" title="Nom du Laboratoire" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach ($lResult as $labName) {?>
                                        <option value="<?php echo $labName['facility_id']; ?>" ><?php echo ucwords($labName['facility_name']); ?></option>
                                        <?php }?>
                                    </select>
                                  </td>
                              <!-- </tr> -->
                            <?php }?>
                            </tr>
                        </table>
                        <br><br>
                        <table class="table" style="width:100%">
                            <tr>
                               <th colspan=8><h4>1. Données démographiques mère / enfant</h4></th>
                            </tr>
                            <tr>
                            <th colspan=8><h5 style="font-weight:bold;font-size:1.1em;">ID de la mère</h5></th>
                            </tr>
                            <tr>
                               <th><label for="mothersId">Code (si applicable) </label></th>
                               <td>
                                    <input type="text" class="form-control " id="mothersId" name="mothersId" placeholder="Code du mère" title="Please enter code du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersName">Nom </label></th>
                               <td>
                                    <input type="text" class="form-control " id="mothersName" name="mothersName" placeholder="Nom du mère" title="Please enter nom du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersDob">Date de naissance </label></th>
                               <td>
                                    <input type="text" class="form-control " id="mothersDob" name="mothersDob" placeholder="Date de naissance" title="Please enter Date de naissance" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="mothersMaritalStatus">Etat civil </label></th>
                               <td>
                                    <select class="form-control " name="mothersMaritalStatus" id="mothersMaritalStatus">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value='single'> Single </option>
                                    <option value='married'> Married </option>
                                    <option value='cohabitating'> Cohabitating </option>
                                    
                                    </select>
                               </td>
                            </tr>

                            <tr>
                                <th colspan=8><h5 style="font-weight:bold;font-size:1.1em;">ID de l'enfant</h5></th>
                            </tr>
                            <tr>
                               <th><label for="childId">Code de l’enfant (Patient) </label></th>
                               <td>
                                    <input type="text" class="form-control " id="childId" name="childId" placeholder="Code (Patient)" title="Please enter code du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childName">Nom </label></th>
                               <td>
                                    <input type="text" class="form-control " id="childName" name="childName" placeholder="Nom" title="Please enter nom du mère" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childDob">Date de naissance </label></th>
                               <td>
                                    <input type="text" class="form-control " id="childDob" name="childDob" placeholder="Date de naissance" title="Please enter Date de naissance" style="width:100%;"  onchange=""/>
                               </td>
                               <th><label for="childGender">Gender </label></th>
                               <td>
                                <select class="form-control " name="childGender" id="childGender">
                                    <option value=''> -- Sélectionner -- </option>
                                    <option value='male'> Male </option>
                                    <option value='female'> Female </option>
                                    
                                    </select>
                               </td>
                            </tr>   
                            <tr>
                                        <th>Age</th>
                                        <td><input type="number" max=9 maxlength="1" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="childAge" name="childAge" placeholder="Age" title="Age" style="width:100%;"  onchange=""/></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                                        <th></th>
                                        <td></td>
                            </tr>                         

                        </table>



                        <br><br>
                        <table class="table" style="width:100%">
                            <tr>
                               <th colspan=8><h4>2. Management de la mère</h4></th>
                            </tr>   
                            <tr>
                              <th>ARV donnés à la maman pendant la grossesse:</th>
                              <td>
                            </tr>  
                        </table>                   

                    </div>
                </div>
                <?php if ($sarr['user_type'] != 'remoteuser') {?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">B. Réservé au Laboratoire de biologie moléculaire </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:25%;"><label for="">Date de réception de l'échantillon <span class="mandatory">*</span> </label></td>
                                <td style="width:25%;">
                                    <input type="text" class="form-control dateTime isRequired" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de léchantillon" <?php echo $labFieldDisabled; ?> onchange="checkSampleReceviedDate();" style="width:100%;"/>
                                </td>
                                <td style="width:25%;"></td><td style="width:25%;"></td>
                            </tr>
                            <?php if (isset($arr['testing_status']) && trim($arr['testing_status']) == "enabled" && $_SESSION['userType'] == '') {?>
                              <tr>
                                <td><label for="">Décision prise </label></td>
                                <td>
                                    <select class="form-control" id="status" name="status" title="Please select décision prise" <?php echo $labFieldDisabled; ?> onchange="checkTestStatus();" style="width:100%;">
                                    <option value=""> -- Sélectionner -- </option>
                                      <option value="6"> En attente d'approbation Clinique </option>
                                      <option value="7">Echantillon accepté</option>
                                      <option value="4">Echantillon rejeté</option>
                                    </select>
                                </td>
                                <td></td><td></td>
                              </tr>
                            <?php }?>
                            <tr class="rejectionReason" style="display:none;">
                                <td><label for="rejectionReason">Motifs de rejet <span class="mandatory">*</span></label></td>
                                <td>
                                    <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please select motifs de rejet" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach ($rejectionResult as $rjctReason) {?>
                                       <option value="<?php echo $rjctReason['rejection_reason_id']; ?>"><?php echo ucwords($rjctReason['rejection_reason_name']); ?></option>
                                      <?php }if ($sarr['user_type'] != 'vluser') {?>
                                       <option value="other">Autre</option>
                                       <?php }?>
                                    </select>
                                </td>
                                <td style="text-align:center;"><label for="newRejectionReason" class="newRejectionReason" style="display:none;">Autre, à préciser <span class="mandatory">*</span></label></td>
                                <td><input type="text" class="form-control newRejectionReason" id="newRejectionReason" name="newRejectionReason" placeholder="Motifs de rejet" title="Please enter motifs de rejet" <?php echo $labFieldDisabled; ?> style="width:100%;display:none;"/></td>
                            </tr>
                            <!-- <tr>
                                <td><label for="sampleCode">Code Labo </label> <span class="mandatory">*</span></td>
                                <td>
                                    <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Code Labo" title="Please enter code labo" style="width:100%;" onchange="checkSampleNameValidation('vl_request_form','< ?php echo $sampleCode;?>',this.id,null,'The sample number that you entered already exists. Please try another number',null)"/>
                                </td>
                               <td></td><td></td>
                            </tr> -->
                            <tr>
                                  <td><label for="labId">Nom du laboratoire </label> </td>
                                  <td>
                                      <select name="labId" id="labId" class="form-control" title="Please choose laboratoire" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach ($lResult as $labName) {?>
                                        <option value="<?php echo $labName['facility_id']; ?>" ><?php echo ucwords($labName['facility_name']); ?></option>
                                        <?php }?>
                                    </select>
                                  </td>
                                  <td></td><td></td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de réalisation de la charge virale </label></td>
                                <td>
                                    <input type="text" class="form-control dateTime" id="dateOfCompletionOfViralLoad" name="dateOfCompletionOfViralLoad" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réalisation de la charge virale" <?php echo $labFieldDisabled; ?> style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="testingPlatform">Technique utilisée </label></td>
                                <td>
                                    <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" <?php echo $labFieldDisabled; ?> style="width:100%;">
                                      <option value="">-- Sélectionner --</option>
                                      <?php foreach ($importResult as $mName) {?>
                                        <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']; ?>"><?php echo $mName['machine_name']; ?></option>
                                        <?php }?>
                                    </select>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr class="resultSection">
                                <td class="vlResult"><label for="vlResult">Résultat </label></td>
                                <td>
                                    <input type="text" class="vlResult form-control checkNum" id="vlResult" name="vlResult" placeholder="Résultat (copies/ml)" title="Please enter résultat" <?php echo $labFieldDisabled; ?> onchange="calculateLogValue(this)" style="width:100%;"/>
                                    <input type="checkbox" class="specialResults" id="vlLt20" name="vlLt20" value="yes" title="Please check VL value"> < 20<br>
                                    <input type="checkbox" class="specialResults" id="vlLt40" name="vlLt40" value="yes" title="Please check VL value"> < 40<br>
                                    <input type="checkbox" class="specialResults" id="vlLt40" name="vlLt400" value="yes" title="Please check VL value"> < 400<br>
                                    <input type="checkbox" class="specialResults" id="vlTND" name="vlTND" value="yes" title="Please check VL value"> Target Not Detected / Non Détecté
                                </td>
                                <td style="text-align:center;"><label for="vlLog">Log </label></td>
                                <td>
                                    <input type="text" class="vlLog form-control checkNum" id="vlLog" name="vlLog" placeholder="Log" title="Please enter log" <?php echo $labFieldDisabled; ?> onchange="calculateLogValue(this)" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                              <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service effectuant la charge virale </label></td>
                            </tr>
                            <!--<tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de remise du résultat </label></td>
                                <td>
                                    <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de remise du résultat" < ?php echo $labFieldDisabled; ?> onchange="checkSampleTestingDate();" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>-->
                        </table>
                    </div>
                </div>
                <?php }?>
                <div class="box-header with-border">
                  <label class="radio-inline" style="margin:0;padding:0;">1. Biffer la mention inutile <br>2. Sélectionner un seul régime de traitement </label>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>"/>
                <?php }?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="formId" id="formId" value="3"/>
                <input type="hidden" name="vlSampleId" id="vlSampleId" value=""/>
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>"/>
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
  provinceName = true;
  facilityName = true;
  machineName = true;
  function getfacilityDetails(obj){
    $.blockUI();
    var cName = $("#clinicName").val();
    var pName = $("#province").val();
    if(pName!='' && provinceName && facilityName){
      facilityName = false;
    }
    if($.trim(pName)!=''){
      if(provinceName){
          $.post("/includes/getFacilityForClinic.php", { pName : pName},
          function(data){
              if(data!= ""){
                details = data.split("###");
                $("#clinicName").html(details[0]);
                $("#district").html(details[1]);
                $("#clinicianName").val(details[2]);
              }
          });
      }
      sampleCodeGeneration();
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province; ?>");
      $("#clinicName").html("<?php echo $facility; ?>");
    }else{
      $("#district").html("<option value=''> -- Sélectionner -- </option>");
    }
    $.unblockUI();
  }

  function sampleCodeGeneration() {
    var pName = $("#province").val();
    var sDate = $("#sampleCollectionDate").val();
    if(pName!='' && sDate!=''){
      $.post("../includes/sampleCodeGeneration.php", { sDate : sDate,pName:pName},
      function(data){
        var sCodeKey = JSON.parse(data);
        <?php if ($arr['sample_code'] == 'auto') {?>
          pNameVal = pName.split("##");
          sCode = sCodeKey.auto;
          $("#sampleCode").val('<?php echo $rKey; ?>'+pNameVal[1]+sCode+sCodeKey.maxId);
          $("#sampleCodeInText").html('<?php echo $rKey; ?>'+pNameVal[1]+sCode+sCodeKey.maxId);
          //$("#sampleCodeValue").html('exemple de code:'+'<?php echo $rKey; ?>'+pNameVal[1]+sCode+sCodeKey.maxId).css('display','block');
          $("#sampleCodeFormat").val('<?php echo $rKey; ?>'+pNameVal[1]+sCode);
          $("#sampleCodeKey").val(sCodeKey.maxId);
          checkSampleNameValidation('vl_request_form','<?php echo $sampleCode; ?>','sampleCode',null,'The sample number that you entered already exists. Please try another number',null);
          <?php } else if ($arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
          $("#sampleCode").val('<?php echo $rKey . $prefix; ?>'+sCodeKey.mnthYr+sCodeKey.maxId);
          $("#sampleCodeInText").html('<?php echo $rKey . $prefix; ?>'+sCodeKey.mnthYr+sCodeKey.maxId);
          //$("#sampleCodeValue").html('exemple de code:'+'<?php echo $rKey . $prefix; ?>'+sCodeKey.mnthYr+sCodeKey.maxId).css('display','block');
          $("#sampleCodeFormat").val('<?php echo $rKey . $prefix; ?>'+sCodeKey.mnthYr);
          $("#sampleCodeKey").val(sCodeKey.maxId);
          checkSampleNameValidation('vl_request_form','<?php echo $sampleCode; ?>','sampleCode',null,'The sample number that you entered already exists. Please try another number',null)
        <?php }?>
      });
    }
  }

  function getfacilityDistrictwise(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#clinicName").val();
    if(dName!=''){
      $.post("/includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
          if(data != ""){
            details = data.split("###");
            $("#clinicName").html(details[0]);
          }
      });
    }else{
       $("#clinicName").html("<option value=''> -- Sélectionner -- </option>");
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
      $.post("/includes/getFacilityForClinic.php", { cName : cName},
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
      $("#province").html("<?php echo $province; ?>");
      $("#clinicName").html("<?php echo $facility; ?>");
    }
    $.unblockUI();
  }
  $("input:radio[name=isPatientNew]").click(function() {
    if($(this).val() == 'yes'){
      $(".du").css("visibility","visible");
    }else if($(this).val() == 'no'){
      $(".du").css("visibility","hidden");
    }
  });
  $("input:radio[name=gender]").click(function() {
    if($(this).val() == 'female'){
       $("#femaleElements").show();
    }else if($(this).val() == 'male'){
      $("#femaleElements").hide();
    }
  });
  $("input:radio[name=hasChangedRegimen]").click(function() {
    if($(this).val() == 'yes'){
      $(".arvChangedElement").show();
    }else if($(this).val() == 'no'){
      $(".arvChangedElement").hide();
    }
  });
  function checkVLTestReason(){
    var vlTestReason = $("#vlTestReason").val();
    if(vlTestReason == "other"){
      $(".newVlTestReason").show();
      $("#newVlTestReason").addClass("isRequired");
    }else{
      $(".newVlTestReason").hide();
      $("#newVlTestReason").removeClass("isRequired");
    }
  }
  function checkSpecimenType(){
    var specimenType = $("#specimenType").val();
    if(specimenType == 2){
      $(".plasmaElement").show();
    }else{
      $(".plasmaElement").hide();
    }
  }

  function checkTestStatus(){
    var status = $("#status").val();
    if(status == 4){
      $(".rejectionReason").show();
      $(".resultSection").hide();
      $("#rejectionReason").addClass('isRequired');
      $("#vlResult").val('').css('pointer-events','none');
      $("#vlLog").val('').css('pointer-events','none');
      $('.specialResults').prop('checked', false).removeAttr('checked');

    }else{
      $(".resultSection").show();
      $(".rejectionReason").hide();
      $("#rejectionReason").removeClass('isRequired');
      $("#vlResult").css('pointer-events','auto');
      $("#vlLog").css('pointer-events','auto');

    }
  }

  function checkRejectionReason(){
    var rejectionReason = $("#rejectionReason").val();
    if(rejectionReason == "other"){
      $(".newRejectionReason").show();
      $("#newRejectionReason").addClass('isRequired');
    }else{
      $(".newRejectionReason").hide();
      $("#newRejectionReason").removeClass('isRequired');
    }
  }

  function checkLastVLTestDate(){
    var artInitiationDate = $("#dateOfArtInitiation").val();
    var dateOfLastVLTest = $("#lastViralLoadTestDate").val();
    if($.trim(artInitiationDate)!= '' && $.trim(dateOfLastVLTest)!= '') {
      if(moment(artInitiationDate).isAfter(dateOfLastVLTest)) {
        alert("Dernier test de charge virale Les données ne peuvent pas être antérieures à la date d'initiation de l'ARV!");
        $("#lastViralLoadTestDate").val("");
      }
    }
  }
  function calculateLogValue(obj){
    if(obj.id=="vlResult") {
      absValue = $("#vlResult").val();
      if(absValue!='' && absValue!=0){
        $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
      }else{
        $("#vlLog").val("");
      }
    }
    if(obj.id=="vlLog") {
      logValue = $("#vlLog").val();
      if(logValue!='' && logValue!=0){
        var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
        if(absVal!='Infinity'){
        $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
        }else{
          $("#vlResult").val('');
        }
      }
    }
  }
  function validateNow(){
    flag = deforayValidator.init({
      formId: 'addVlRequestForm'
    });
    if(flag){
      $.blockUI();
      <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') {?>
      insertSampleCode('addVlRequestForm','vlSampleId','sampleCode','sampleCodeKey','sampleCodeFormat',3,'sampleCollectionDate');
      <?php } else {?>
          document.getElementById('addVlRequestForm').submit();
      <?php }?>
    }
  }

  function setPatientDetails(pDetails){
    patientArray = pDetails.split("##");
    if($.trim(patientArray[3])!=''){
      $("#dob").val(patientArray[3]);
      getAge();
    }else if($.trim(patientArray[4])!='' && $.trim(patientArray[4]) != 0){
      $("#ageInYears").val(patientArray[4]);
    }else if($.trim(patientArray[5])!=''){
      $("#ageInMonths").val(patientArray[5]);
    }
    if($.trim(patientArray[2])!=''){
      if(patientArray[2] == 'male'){
      $("#genderMale").prop('checked', true);
      }else if(patientArray[2] == 'female'){
        $("#genderFemale").prop('checked', true);
      }
    }
    if($.trim(patientArray[15])!=''){
      $("#patientArtNo").val($.trim(patientArray[15]));
    }
  }


  $(document).ready(function(){


      

    $('#vlResult, #vlLog').on('input',function(e){
      if(this.value != ''){
        $('.specialResults').attr('disabled',true);
      }else{
        $('.specialResults').attr('disabled',false);
      }
    });

    $('.specialResults').change(function() {
        if($(this).is(':checked')){
          $('#vlResult, #vlLog').val('');
          $('#vlResult,#vlLog').attr('readonly',true);
          $(".specialResults").not(this).attr('disabled',true);
          //$('.specialResults').not(this).prop('checked', false).removeAttr('checked');
        }else{
          $('#vlResult,#vlLog').attr('readonly',false);
          $(".specialResults").not(this).attr('disabled',false);
        }
    });


    $('#clinicName').select2({placeholder:"Select Clinic/Health Center"});
    $('#district').select2({placeholder:"District"});
    $('#province').select2({placeholder:"Province"});
  });

  </script>