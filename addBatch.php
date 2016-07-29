<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$query="SELECT * FROM vl_request_form where batch_id is NULL";
$result = $db->rawQuery($query);
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
<style>
  #ms-sampleCode{width: 110%;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Create Batch</h1>
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
            <form class="form-horizontal" method='post'  name='addBatchForm' id='addBatchForm' autocomplete="off" action="addBatchCodeHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="batchCode" class="col-lg-4 control-label">Batch Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="Batch Code" title="Please enter batch code" />
                        </div>
                    </div>
                  </div>
                </div>
		
		<div class="row">
		  <div class="col-md-8">
                    <div class="form-group">
                        <div class="col-md-12">
			  <div class="col-md-12">
			     <div style="width:60%;margin:0 auto;clear:both;">
			      <a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
			      </div><br/><br/>
			    <select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
			    <?php
			    foreach($result as $sample){
			      ?>
			      <option value="<?php echo $sample['treament_id'];?>"><?php  echo ucwords($sample['sample_code']);?></option>
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
  
  <script src="assets/js/jquery.multi-select.js"></script>
  <script src="assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript">

  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addBatchForm'
    });
    
    if(flag){
      document.getElementById('addBatchForm').submit();
    }
  }
   //$("#auditRndNo").multiselect({height: 100,minWidth: 150});
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
   $('#select-all-samplecode').click(function(){
  $('#sampleCode').multiSelect('select_all');
  return false;
});
$('#deselect-all-samplecode').click(function(){
  $('#sampleCode').multiSelect('deselect_all');
  return false;
});
   });
  </script>
  
 <?php
 include('footer.php');
 ?>
