<?php
ob_start();
include('../header.php');
//include('../includes/MysqliDb.php');
define('UPLOAD_PATH','../uploads');
$formQuery ="SELECT * from form_details";
$formResult=$db->query($formQuery);
$globalConfigQuery ="SELECT * from global_config";
$configResult=$db->query($globalConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$mFieldArray = array();
if(isset($arr['r_mandatory_fields']) && trim($arr['r_mandatory_fields'])!= ''){
    $mFieldArray = explode(',',$arr['r_mandatory_fields']);
}
?>
<link href="../assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
<link href="../assets/css/multi-select.css" rel="stylesheet" />
<style>
  .select2-selection__choice{
    color:#000000 !important;
  }
  .boxWidth {width:10%;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-gears"> Edit General Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage General Config</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <!--<div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"> </div>
        </div>-->
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post' name='editGlobalConfigForm' id='editGlobalConfigForm' enctype="multipart/form-data" autocomplete="off" action="globalConfigHelper.php">
              <div class="box-body">
                
                             
                
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Global Settings</h3>
  </div>
  <div class="panel-body">
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="default_time_zone" class="col-lg-4 control-label">Default Time Zone </label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="default_time_zone" name="default_time_zone" placeholder="eg: Africa/Harare" title="Please enter default time zone" value="<?php echo $arr['default_time_zone']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="header" class="col-lg-4 control-label">Header </label>
                      <div class="col-lg-8">
                        <textarea class="form-control" id="header" name="header" placeholder="Header" title="Please enter header" style="width:100%;min-height:80px;max-height:100px;"><?php echo $arr['header']; ?></textarea>
                      </div>
                    </div>
                   </div>
                </div>
                


                
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="" class="col-lg-4 control-label">Logo Image </label>
                      <div class="col-lg-8">
                       <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width:200px; height:150px;">
                          <?php
                          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                          ?>
                           <img src=".././uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo image">
                          <?php } else { ?>
                           <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=No image">
                          <?php } ?>
                        </div>
                        <div>
                          <span class="btn btn-default btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span>
                          <input type="file" id="logo" name="logo" title="Please select logo image" onchange="getNewImage('<?php echo $arr['logo']; ?>');">
                          </span>
                          <?php
                          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                          ?>
                            <a id="clearImage" href="javascript:void(0);" class="btn btn-default" data-dismiss="fileupload" onclick="clearImage('<?php echo $arr['logo']; ?>')">Clear</a>
                          <?php } ?>
                          <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                        </div>
                        </div>
                        <div class="box-body">
                            Please make sure logo image size of: <code>80x80</code>
                        </div>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="show_date" class="col-lg-4 control-label">Date For Patient ART NO. </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="show_full_date_yes" name="show_date" value="yes" <?php echo($arr['show_date'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Full Date&nbsp;&nbsp;
                        <input type="radio" class="" id="show_full_date_no" name="show_date" value="no" <?php echo($arr['show_date'] == 'no' || $arr['show_date'] == '')?'checked':''; ?>>&nbsp;&nbsp;Month and Year
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="auto_approval" class="col-lg-4 control-label">Auto Approval </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="auto_approval_yes" name="auto_approval" value="yes" <?php echo($arr['auto_approval'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="auto_approval_no" name="auto_approval" value="no" <?php echo($arr['auto_approval'] == 'no' || $arr['auto_approval'] == '')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="viral_load_threshold_limit" class="col-lg-4 control-label">Viral Load Threshold Limit <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric isRequired" id="viral_load_threshold_limit" name="viral_load_threshold_limit" placeholder="Viral Load Threshold Limit" title="Please enter VL threshold limit" value="<?php echo $arr['viral_load_threshold_limit']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="vl_form" class="col-lg-4 control-label">Viral Load Form <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="vl_form" id="vl_form" title="Please select the viral load form">
                            <?php
                            foreach($formResult as $val){
                            ?>
                            <option value="<?php echo $val['vlsm_country_id']; ?>" <?php echo ($val['vlsm_country_id']==$arr['vl_form'])?"selected='selected'":""?>><?php echo $val['form_name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-10" style="height:28px;margin-left:5%;">
                    <div class="form-group" style="height:28px;">
                      <label for="auto_approval" class="col-lg-2 control-label">Sample Code </label>
                      <div class="col-lg-8">
                        <?php
                        $sPrefixMMYY = '';
                        $sPrefixYY = '';
                        $sPrefixMMYYDisplay = 'disabled="disabled"';
                        $sPrefixYYDisplay = 'disabled="disabled"';
                        if($arr['sample_code']=='MMYY'){
                        $sPrefixMMYY = $arr['sample_code_prefix'];
                        $sPrefixMMYYDisplay = '';
                        }else if($arr['sample_code']=='YY'){
                            $sPrefixYY = $arr['sample_code_prefix'];
                            $sPrefixYYDisplay = '';
                        }
                        ?>
                        <input type="radio" class="" id="auto_generate_yy" name="sample_code" value="YY" <?php echo($arr['sample_code'] == 'YY')?'checked':''; ?> onclick="makeReadonly('prefixMMYY','prefixYY')">&nbsp;<input <?php echo $sPrefixYYDisplay;?> type="text" class="boxWidth prefixYY" id="prefixYY" name="sample_code_prefix" title="Enter Prefix" value="<?php echo $sPrefixYY;?>"/>
                        YY&nbsp;&nbsp;
                        <input type="radio" class="" id="auto_generate_mmyy" name="sample_code" value="MMYY" <?php echo($arr['sample_code'] == 'MMYY')?'checked':''; ?> onclick="makeReadonly('prefixYY','prefixMMYY')">&nbsp;<input <?php echo $sPrefixMMYYDisplay;?>  type="text" class="boxWidth prefixMMYY" id="prefixMMYY" name="sample_code_prefix" title="Enter Prefix" value="<?php echo $sPrefixMMYY;?>"/>
                        MMYY&nbsp;&nbsp;
                        <input type="radio" class="" id="auto_generate" name="sample_code" value="auto" <?php echo($arr['sample_code'] == 'auto')?'checked':''; ?>>Auto&nbsp;&nbsp;
                        <input type="radio" class="" id="numeric" name="sample_code" value="numeric" <?php echo($arr['sample_code'] == 'numeric')?'checked':''; ?>>Numeric&nbsp;&nbsp;
                        <input type="radio" class="" id="alpha_numeric" name="sample_code" value="alphanumeric" <?php echo($arr['sample_code']=='alphanumeric')?'checked':''; ?>>Alpha Numeric
                      </div>
                    </div>
                   </div>
                </div>
                <div id="auto-sample-eg" class="row" style="display:<?php echo($arr['sample_code'] == 'auto' || 'MMYY' || 'YY')?'block':'none'; ?>;">
                  <div class="col-md-7" style="text-align:center;">
                    <code id="auto-sample-code" class="autoSample" style="display:<?php echo($arr['sample_code'] == 'auto')?'block':'none'; ?>;">
                        eg. Province Code+Year+Month+Date+Increment Counter
                    </code>
                    <code id="auto-sample-code-MMYY" class="autoSample" style="display:<?php echo($arr['sample_code'] == 'MMYY')?'block':'none'; ?>;">
                        eg. Prefix+Month+Year+Increment Counter (VL0517999)
                    </code>
                    <code id="auto-sample-code-YY" class="autoSample" style="display:<?php echo($arr['sample_code'] == 'YY')?'block':'none'; ?>;">
                        eg. Prefix+Year+Increment Counter (VL17999)
                    </code>
                  </div>
                </div><br/>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="min_length" class="col-lg-4 control-label">Minimum Length<span class="mandatory minlth" style="display:<?php echo($arr['sample_code'] == 'auto')?'none':'block'; ?>">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric <?php echo($arr['sample_code'] == 'auto' || 'MMYY' || 'YY')?'':'isRequired'; ?>" id="min_length" name="min_length" <?php echo($arr['sample_code'] == 'auto' || 'MMYY' || 'YY')?'readonly':''; ?> placeholder="Sample Code Min. Length" title="Please enter sample code min length" value="<?php echo ($arr['sample_code'] == 'auto')?'':$arr['min_length']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="min_length" class="col-lg-4 control-label">Maximum Length<span class="mandatory maxlth" style="display:<?php echo($arr['sample_code'] == 'auto')?'none':'block'; ?>">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric <?php echo($arr['sample_code'] == 'auto' || 'MMYY' || 'YY')?'':'isRequired'; ?>" id="max_length" name="max_length" <?php echo($arr['sample_code'] == 'auto' || 'MMYY' || 'YY')?'readonly':''; ?> placeholder="Sample Code Max. Length" title="Please enter sample code max length" value="<?php echo ($arr['sample_code'] == 'auto')?'':$arr['max_length']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row" style="margin-top:10px;">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="auto_approval" class="col-lg-4 control-label">Same user can Review and Approve </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="user_review_yes" name="user_review_approve" value="yes" <?php echo($arr['user_review_approve'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="user_review_no" name="user_review_approve" value="no" <?php echo($arr['user_review_approve'] == 'no')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7" style="height:38px;">
                    <div class="form-group" style="height:38px;">
                      <label for="manager_email" class="col-lg-4 control-label">Manager Email</label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="manager_email" name="manager_email" placeholder="Manager Email" title="Please enter manager email" value="<?php echo $arr['manager_email']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7" style="text-align:center;">
                      <code>You can enter multiple email by separating them with comma</code>
                  </div>
                </div><br/>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="instance_type" class="col-lg-4 control-label">Instance Type <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="instance_type" id="instance_type" title="Please select the instance type">
                            <option value="Viral Load Lab" <?php echo ('Viral Load Lab'==$arr['instance_type'])?"selected='selected'":""?>>Viral Load Lab</option>
                            <option value="Clinic/Lab" <?php echo ('Clinic/Lab'==$arr['instance_type'])?"selected='selected'":""?>>Clinic/Lab</option>
                            <option value="Both" <?php echo ('Both'==$arr['instance_type'])?"selected='selected'":""?>>Both</option>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="instance_type" class="col-lg-4 control-label">Barcode Printing  (Experimental) <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="bar_code_printing" id="bar_code_printing" title="Please select the barcode printing">
                            <option value="off" <?php echo ('off'==$arr['bar_code_printing'])?"selected='selected'":""?>>Off</option>
                            <option value="zebra-printer" <?php echo ('zebra-printer'==$arr['bar_code_printing'])?"selected='selected'":""?>>Zebra Printer</option>
                            <option value="dymo-labelwriter-450" <?php echo ('dymo-labelwriter-450'==$arr['bar_code_printing'])?"selected='selected'":""?>>Dymo LabelWriter 450</option>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row" style="margin-top:10px;">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="import_non_matching_sample" class="col-lg-4 control-label">Import Non matching Sample Results from Machine generated file </label>
                      <div class="col-lg-8">
                        <input type="radio" id="import_non_matching_sample_yes" name="import_non_matching_sample" value="yes" <?php echo($arr['import_non_matching_sample'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" id="import_non_matching_sample_no" name="import_non_matching_sample" value="no" <?php echo($arr['import_non_matching_sample'] == 'no')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-10" style="text-align:left;">
                      <code>"This option is used to check how to handle Sample IDs which do not match the VLSM Sample IDs, while importing results manually from a machine generated CSV/Excel/Text file"</code>
                  </div>
                </div><br/>
              </div>
        </div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">VLSM Connect</h3>
  </div>
  <div class="panel-body">
    
    
                <div class="row">
                  <div class="col-md-7" style="height:38px;">
                    <div class="form-group" style="height:38px;">
                      <label for="sync_path" class="col-lg-4 control-label">Sync Path (Dropbox or Shared folder)</label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="sync_path" name="sync_path" placeholder="Sync Path" title="Please enter sync path" value="<?php echo $arr['sync_path']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7 col-md-offset-2" style="text-align:center;">
                      <code>Used for Dropbox or shared folder sync using the vlsm-connect module</code>
                  </div>
                </div><br/>
                
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="enable_qr_mechanism" class="col-lg-4 control-label">Enable QR Code Mechanism </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="enable_qr_mechanism_yes" name="enable_qr_mechanism" value="yes" <?php echo($arr['enable_qr_mechanism'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="enable_qr_mechanism_no" name="enable_qr_mechanism" value="no" <?php echo($arr['enable_qr_mechanism'] == 'no' || $arr['enable_qr_mechanism'] == '')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                  </div>
                </div>    
    
  </div>
</div>
                
                
                
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Result PDF Settings</h3>
  </div>
  <div class="panel-body">
            <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="show_smiley" class="col-lg-4 control-label">Show Emoticon </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="show_smiley_yes" name="show_smiley" value="yes" <?php echo($arr['show_smiley'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="show_smiley_no" name="show_smiley" value="no" <?php echo($arr['show_smiley'] == 'no' || $arr['show_smiley'] == '')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="h_vl_msg" class="col-lg-4 control-label">High Viral Load Message </label>
                      <div class="col-lg-8">
                        <textarea class="form-control" id="h_vl_msg" name="h_vl_msg" placeholder="High Viral Load message that will appear for results >= the VL threshold limit" title="Please enter high viral load message" style="width:100%;min-height:80px;max-height:100px;"><?php echo $arr['h_vl_msg']; ?></textarea>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="l_vl_msg" class="col-lg-4 control-label">Low Viral Load Message </label>
                      <div class="col-lg-8">
                        <textarea class="form-control" id="l_vl_msg" name="l_vl_msg" placeholder="Low Viral Load message that will appear for results lesser than the VL threshold limit" title="Please enter low viral load message" style="width:100%;min-height:80px;max-height:100px;"><?php echo $arr['l_vl_msg']; ?></textarea>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="patient_name_pdf" class="col-lg-4 control-label">Patient Name </label>
                      <div class="col-lg-8">
                        <select type="text" class="form-control" id="patient_name_pdf" name="patient_name_pdf" title="Choose one option" value="<?php echo $arr['patient_name_pdf']; ?>">
                            <option value="flname" <?php echo ('flname'==$arr['patient_name_pdf'])?"selected='selected'":""?>>First Name + Last Name</option>
                            <option value="fullname" <?php echo ('fullname'==$arr['patient_name_pdf'])?"selected='selected'":""?>>Full Name</option>
                            <option value="hidename" <?php echo ('hidename'==$arr['patient_name_pdf'])?"selected='selected'":""?>>Hide Patient Name</option>
                        </select>
                      </div>
                    </div>
                   </div>
            </div>
                
   
            <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="r_mandatory_fields" class="col-lg-4 control-label">Mandatory Fields for COMPLETED Result PDF: </label>
                      <div class="col-lg-8">
                        
                        <div class="form-group">
                            
                            

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12" style="text-align:justify;">
                                            <code>If any of the selected fields are incomplete, the Result PDF appears with a <strong>DRAFT</strong> watermark. Leave right block blank (Deselect All) to disable this.</code>
                                        </div>
                                    </div>
                                    <div style="width:100%;margin:10px auto;clear:both;">
                                        <a href="#" id="select-all-field" style="float:left;" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-field" style="float:right;" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                                    </div><br/><br/>
                                    <select id="r_mandatory_fields" name="r_mandatory_fields[]" multiple="multiple" class="search">
                                     <option value="facility_code" <?php echo (in_array('facility_code',$mFieldArray))?'selected="selected"':''; ?>>Facility Code</option>
                                     <option value="facility_state" <?php echo (in_array('facility_state',$mFieldArray))?'selected="selected"':''; ?>>Facility Province</option>
                                     <option value="facility_district" <?php echo (in_array('facility_district',$mFieldArray))?'selected="selected"':''; ?>>Facility District</option>
                                     <option value="facility_name" <?php echo (in_array('facility_name',$mFieldArray))?'selected="selected"':''; ?>>Facility Name</option>
                                     <option value="sample_code" <?php echo (in_array('sample_code',$mFieldArray))?'selected="selected"':''; ?>>Sample Code</option>
                                     <option value="sample_collection_date" <?php echo (in_array('sample_collection_date',$mFieldArray))?'selected="selected"':''; ?>>Sample Collection Date</option>
                                     <option value="patient_art_no" <?php echo (in_array('patient_art_no',$mFieldArray))?'selected="selected"':''; ?>>Patient ART No.</option>
                                     <option value="sample_received_at_vl_lab_datetime" <?php echo (in_array('sample_received_at_vl_lab_datetime',$mFieldArray))?'selected="selected"':''; ?>>Date Sample Received at Testing Lab</option>
                                     <option value="sample_tested_datetime" <?php echo (in_array('sample_tested_datetime',$mFieldArray))?'selected="selected"':''; ?>>Sample Tested Date</option>
                                     <option value="sample_name" <?php echo (in_array('sample_name',$mFieldArray))?'selected="selected"':''; ?>>Sample Type</option>
                                     <option value="vl_test_platform" <?php echo (in_array('vl_test_platform',$mFieldArray))?'selected="selected"':''; ?>>VL Testing Platform</option>
                                     <option value="result" <?php echo (in_array('result',$mFieldArray))?'selected="selected"':''; ?>>VL Result</option>
                                     <option value="approvedBy" <?php echo (in_array('approvedBy',$mFieldArray))?'selected="selected"':''; ?>>Approved By</option>
                                   </select>
                                   
                                </div>
                                
                                
                            
                        </div>

                      </div>

                    </div>
                    
                   </div>
                  
                  
                  
                  
                  
                </div>                
                
  </div>
</div>                
                
                
                
                
                
                
                
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="removedLogoImage" id="removedLogoImage"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="globalConfig.php" class="btn btn-default"> Cancel</a>
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
  <script type="text/javascript" src="../assets/js/jasny-bootstrap.js"></script>
  <script src="../assets/js/jquery.multi-select.js"></script>
  <script src="../assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript">
  $(document).ready(function() {
      $('.search').multiSelect({
       selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Field Name'>",
       selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Field Name'>",
       afterInit: function(ms){
	 var that = this,
	     $selectableSearch = that.$selectableUl.prev(),
	     $selectionSearch = that.$selectionUl.prev(),
	     selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
	     selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';
     
	 that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
	 .on('keydown', function(e){
	   if (e.which === 40){
	     that.$selectableUl.focus();
	     return false;
	   }
	 });
     
	 that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
	 .on('keydown', function(e){
	   if (e.which == 40){
	     that.$selectionUl.focus();
	     return false;
	   }
	 });
       },
       afterSelect: function(){
	    this.qs1.cache();
	    this.qs2.cache();
       },
       afterDeselect: function(){
	  this.qs1.cache();
	  this.qs2.cache();
       }
     });
      
      $('#select-all-field').click(function(){
       $('#r_mandatory_fields').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-field').click(function(){
       $('#r_mandatory_fields').multiSelect('deselect_all');
       return false;
     });
   });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editGlobalConfigForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('editGlobalConfigForm').submit();
    }
  }
  
  function clearImage(img){
    $(".fileinput").fileinput("clear");
    $("#clearImage").addClass("hide");
    $("#removedLogoImage").val(img);
  }
  
  function getNewImage(img){
    $("#clearImage").addClass("hide");
    $("#removedLogoImage").val(img);
  }
  
  $("input:radio[name=sample_code]").click(function() {
        if (this.value == 'MMYY' || this.value == 'YY') {
            $('#auto-sample-eg').show(); 
            $('.autoSample').hide();
            if (this.value == 'MMYY') {
                $('#auto-sample-code-MMYY').show();
            }else{
                $('#auto-sample-code-YY').show();
            }
            $('#min_length').val(''); 
            $('.minlth').hide();
            $('#min_length').removeClass('isRequired'); 
            $('#min_length').prop('readonly',true); 
            $('#max_length').val('');
            $('.maxlth').hide();
            $('#max_length').removeClass('isRequired'); 
            $('#max_length').prop('readonly',true);
        }
        else if(this.value == 'auto'){
            $('.autoSample').hide(); 
           $('#auto-sample-eg').show();
           $('#auto-sample-code').show();
           $('#min_length').val(''); 
           $('.minlth').hide();
           $('#min_length').removeClass('isRequired'); 
           $('#min_length').prop('readonly',true); 
           $('#max_length').val('');
           $('.maxlth').hide();
           $('#max_length').removeClass('isRequired'); 
           $('#max_length').prop('readonly',true);
           $('.boxWidth').removeClass('isRequired').attr('disabled',true).val('');
        }else{
           $('#auto-sample-eg').hide();
           $('.minlth').show();
           $('#min_length').addClass('isRequired');
           $('#min_length').prop('readonly',false);
           $('.maxlth').show();
           $('#max_length').addClass('isRequired');
           $('#max_length').prop('readonly',false);
           $('.boxWidth').removeClass('isRequired').attr('disabled',true).val('');
        }
  });
  function makeReadonly(id1,id2) {
    $("#"+id1).val('');
    $("#"+id1).attr("disabled",'disabled').removeClass('isRequired');
    $("#"+id2).attr("disabled",false).addClass('isRequired');
    
  }
</script>
  
 <?php
 include('../footer.php');
 ?>
