<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
$otherConfigQuery ="SELECT * from other_config";
$otherConfigResult=$db->query($otherConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($otherConfigResult); $i++) {
    $arr[$otherConfigResult[$i]['name']] = $otherConfigResult[$i]['value'];
}
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-gears"> Edit Email/SMS Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage Email/SMS Config</li>
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
            <form class="form-horizontal" method='post' name='editOtherConfigForm' id='editOtherConfigForm' autocomplete="off" action="otherConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email" class="col-lg-3 control-label">Email </label>
                      <div class="col-lg-9">
                        <input type="text" class="form-control" id="email" name="email" placeholder="Email" title="Please enter email" value="<?php echo $arr['email']; ?>">
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="password" class="col-lg-3 control-label">Password </label>
                      <div class="col-lg-9">
                        <input type="text" class="form-control" id="password" name="password" placeholder="Password" title="Please enter password" value="<?php echo $arr['password']; ?>">
                      </div>
                    </div>
                   </div>
                </div>
                <!--<div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="col-lg-3 control-label">Choose VL Form Fields </label>
                        <div class="col-lg-9">
                           <div style="width:60%;margin:0 auto;clear:both;">
                            <a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
                                <option>Form Serial No</option>
                                <option>Facility Name</option>
                                <option>Province</option>
                                <option>District Name</option>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>-->
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
//  $(document).ready(function() {
//      $('.search').multiSelect({
//       selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
//       selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
//       afterInit: function(ms){
//	 var that = this,
//	     $selectableSearch = that.$selectableUl.prev(),
//	     $selectionSearch = that.$selectionUl.prev(),
//	     selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
//	     selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';
//     
//	 that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
//	 .on('keydown', function(e){
//	   if (e.which === 40){
//	     that.$selectableUl.focus();
//	     return false;
//	   }
//	 });
//     
//	 that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
//	 .on('keydown', function(e){
//	   if (e.which == 40){
//	     that.$selectionUl.focus();
//	     return false;
//	   }
//	 });
//       },
//       afterSelect: function(){
//	var maxNoOfSample = '<?php echo $configResult[0]['value']; ?>';
//	if(this.qs2.cache().matchedResultsCount == maxNoOfSample){
//	  alert("You have selected Maximum no. of sample "+this.qs2.cache().matchedResultsCount);
//	  $(".ms-selectable").css("pointer-events","none");
//	}
//	 this.qs1.cache();
//	 this.qs2.cache();
//       },
//       afterDeselect: function(){
//	 var maxNoOfSample = '<?php echo $configResult[0]['value']; ?>';
//	if(this.qs2.cache().matchedResultsCount < maxNoOfSample){
//	  $(".ms-selectable").css("pointer-events","auto");
//	}
//	 this.qs1.cache();
//	 this.qs2.cache();
//       }
//     });
//      
//      $('#select-all-samplecode').click(function(){
//       $('#sampleCode').multiSelect('select_all');
//       return false;
//     });
//     $('#deselect-all-samplecode').click(function(){
//       $('#sampleCode').multiSelect('deselect_all');
//       return false;
//     });
//   });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editOtherConfigForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('editOtherConfigForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
