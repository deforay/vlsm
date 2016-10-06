<?php
ob_start();
include('header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Add Import Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Configuration</li>
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
            <form class="form-horizontal" method='post'  name='addImportConfigForm' id='addImportConfigForm' autocomplete="off" action="addImportConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationName" class="col-lg-4 control-label">Configuration Name<span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="configurationName" name="configurationName" placeholder="eg. Roche or Abbott" title="Please enter configuration name" onblur="checkNameValidation('import_config','machine_name',this,null,'This configuration name already exists.Try another name',null);setConfigFileName();"onkeypress="setConfigFileName();"/>
                        </div>
                    </div>
                  </div>
                   
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationFileName" class="col-lg-4 control-label">Configuration File<span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="configurationFile" name="configurationFile" placeholder="eg. roche.php or abbott.php" title="Please enter machine name" onblur="checkNameValidation('import_config','machine_name',this,null,'This configuration name already exists.Try another name',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationFileName" class="col-lg-4 control-label">Lower Limit</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control checkNum" id="lowerLimit" name="lowerLimit" placeholder="eg. 20" title="Please enter lower limit" />
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationFileName" class="col-lg-4 control-label">Higher Limit</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control checkNum" id="higherLimit" name="higherLimit" placeholder="eg. 10000000" title="Please enter lower limit" />
                        </div>
                    </div>
                  </div>
                </div>
                
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="importConfig.php" class="btn btn-default"> Cancel</a>
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
        formId: 'addImportConfigForm'
    });
    
    if(flag){
      $.blockUI();
      document.getElementById('addImportConfigForm').submit();
    }
  }
  
  function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                document.getElementById(obj.id).value="";
            }
        });
  }
  
  $("input[type='radio']").click(function(){
    var id = $(this).attr('id');
    if(id == 'logAndAbsoluteInSameColumnYes'){
      $("#absRow").hide();
      $("#absoluteValCol,#absoluteValRow").val("");
      $("label[for*='logValCol']").html("Log/Absolute Value");
      $("#logValCol").attr("placeholder","Log/Absolute Val Column");
      $("#logValCol").attr("title","Please enter log/absolute val column");
      $("#logValRow").attr("placeholder","Log/Absolute Val Row");
      $("#logValRow").attr("title","Please enter log/absolute val row");
    }else{
      $("#absRow").show();
      $("label[for*='logValCol']").html("Log Value");
      $("#logValCol").attr("placeholder","Log Val Column");
      $("#logValCol").attr("title","Please enter log val column");
      $("#logValRow").attr("placeholder","Log Val Row");
      $("#logValRow").attr("title","Please enter log val row");
    }
  });
  
  function setConfigFileName(){
    var configName = $("#configurationName").val();
    if($.trim(configName)!= ''){
      configName = configName.replace(/[^a-zA-Z0-9 ]/g, "")
      if(configName.length >0){
        configName = configName.replace(/\s+/g, ' ');
        configName = configName.replace(/ /g, '-');
        configName = configName.replace(/\-$/, '');
        var configFileName = configName.toLowerCase()+".php";
        $("#configurationFile").val(configFileName);
      }
    }else{
      $("#configurationFile").val("");
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
