<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
$query="SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where batch_id is NULL OR batch_id='' ORDER BY f.facility_name ASC";
$result = $db->rawQuery($query);
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
<style>
    .ms-container{
        width:100%;
    }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-envelope"> Request E-mail</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Request E-mail</li>
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
            <form class="form-horizontal" method="post" name="mailForm" id="mailForm" autocomplete="off" action="mailConfigHelper.php">
              <div class="box-body">
                 <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="type" class="col-lg-3 control-label">Choose Type <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <select id="type" name="type" class="form-control isRequired" title="Please select type">
                            <option value="">Select</option>
                            <option value="request">Request</option>
                            <option value="result">Result</option>
                           </select>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="subject" class="col-lg-3 control-label">Subject <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <input type="text" id="subject" name="subject" class="form-control isRequired" placeholder="Subject" title="Please enter subject"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="toEmail" class="col-lg-3 control-label">To Email <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <input type="text" id="toEmail" name="toEmail" class="form-control isRequired" placeholder="a@yahoo.com,b@yahoo.com" title="Please enter To email"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="cc" class="col-lg-3 control-label">CC</label>
                        <div class="col-lg-9">
                           <input type="text" id="cc" name="cc" class="form-control" placeholder="a@yahoo.com,b@yahoo.com" title="Please enter CC"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="bcc" class="col-lg-3 control-label">BCC</label>
                        <div class="col-lg-9">
                           <input type="text" id="bcc" name="bcc" class="form-control" placeholder="a@yahoo.com,b@yahoo.com" title="Please enter BCC"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="message" class="col-lg-3 control-label">Message <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <textarea id="message" name="message" class="form-control isRequired" placeholder="Message" title="Please enter message"></textarea>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="sample" class="col-lg-3 control-label">Choose Sample(s) <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <div style="width:100%;margin:0 auto;clear:both;">
                            <a href="#" id="select-all-sample" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-sample" style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id="sample" name="sample[]" multiple="multiple" class="search isRequired" title="Please select sample(s)">
                                <?php
                                foreach($result as $sample){
                                  ?>
                                  <option value="<?php echo $sample['vl_sample_id'];?>"><?php  echo ucwords($sample['sample_code'])." - ".ucwords($sample['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="otherConfig.php" class="btn btn-default"> Cancel</a>
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
  <script src="assets/js/jquery.multi-select.js"></script>
  <script src="assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript">
  $(document).ready(function() {
      $('.search').multiSelect({
       selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
       selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
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
      
      $('#select-all-sample').click(function(){
       $('#sample').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-sample').click(function(){
       $('#sample').multiSelect('deselect_all');
       return false;
     });
   });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'mailForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('mailForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
