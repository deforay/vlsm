<?php
include_once('../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
$general=new General($db);

$lName = $_POST['lName'];
$pName = $_POST['pName'];
$dName = $_POST['dName'];
$fName = $_POST['fName'];
$iName = $_POST['iName'];

//global config
$configQuery="SELECT value FROM global_config WHERE name ='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];

$query="SELECT vl.remote_sample_code,vl.vl_sample_id,vl.facility_id FROM vl_request_form as vl WHERE (vl.result is NULL or vl.result = '') AND vlsm_country_id = $country";

if(trim($lName)!=''){
    $query = $query." AND vl.lab_id='".$lName."'";
}if($_POST['fName']!=''){
    $query = $query." AND vl.facility_id='".$fName."'";
}if($_POST['iName']!=''){
    $query = $query." AND vl.implementing_partner='".$iName."'";
}

//$query = $query." ORDER BY f.facility_name ASC";
$query = $query." ORDER BY vl.request_created_datetime ASC";
$result = $db->rawQuery($query);
?>
<div class="col-md-8">
<div class="form-group">
   <div class="col-md-12">
      <div class="col-md-12">
         <div style="width:60%;margin:0 auto;clear:both;">
          <a href="#" id="select-all-samplecode" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
          </div><br/><br/>
         <select id="sampleCode" name="sampleCode[]" multiple="multiple" class="search">
            <?php
            foreach($result as $sample){
              ?>
              <option value="<?php echo $sample['vl_sample_id'];?>"><?php  echo ucwords($sample['remote_sample_code']);?></option>
              <?php
             }
            ?>
         </select>
      </div>
   </div>
</div>
</div>
<script>
    $(document).ready(function() {
      $('.search').multiSelect({
        selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
        selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
        selectableFooter: "<div style='background-color: #367FA9;color: white;padding:5px;text-align: center;' class='custom-header' id='unselectableCount'>Available samples(<?php echo count($result);?>)</div>",
        selectionFooter: "<div style='background-color: #367FA9;color: white;padding:5px;text-align: center;' class='custom-header' id='selectableCount'>Selected samples(0)</div>",
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
            if(this.qs2.cache().matchedResultsCount == 0){
                $("#sampleSubmit").attr("disabled",true);
                $("#sampleSubmit").css("pointer-events","none");
            }else{
                $("#sampleSubmit").attr("disabled",false);
                $("#sampleSubmit").css("pointer-events","auto");
            }
	      this.qs1.cache();
	      this.qs2.cache();
        $("#unselectableCount").html("Available samples("+this.qs1.cache().matchedResultsCount+")");
        $("#selectableCount").html("Selected samples("+this.qs2.cache().matchedResultsCount+")");
       },
       afterDeselect: function(){
         //button disabled/enabled
            if(this.qs2.cache().matchedResultsCount == 0){
                $("#sampleSubmit").attr("disabled",true);
                $("#sampleSubmit").css("pointer-events","none");
            }else{
                $("#sampleSubmit").attr("disabled",false);
                $("#sampleSubmit").css("pointer-events","auto");
            }
            this.qs1.cache();
            this.qs2.cache();
    $("#unselectableCount").html("Available samples("+this.qs1.cache().matchedResultsCount+")");
        $("#selectableCount").html("Selected samples("+this.qs2.cache().matchedResultsCount+")");
       }
      });
      $('#select-all-samplecode').click(function(){
        $('#sampleCode').multiSelect('select_all');
        return false;
      });
      $('#deselect-all-samplecode').click(function(){
        $('#sampleCode').multiSelect('deselect_all');
        $("#sampleSubmit").attr("disabled",true);
        $("#sampleSubmit").css("pointer-events","none");
        return false;
      });
   });
</script>