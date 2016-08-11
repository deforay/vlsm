<?php
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$fName = $_POST['fName'];
$sample = $_POST['sName'];

$start_date = '';
$end_date = '';
if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
   $s_c_date = explode(" ", $_POST['sampleCollectionDate']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $start_date = $general->dateFormat($s_c_date[0]);
   }
   if (isset($s_c_date[2]) && trim($s_c_date[2]) != "") {
     $end_date = $general->dateFormat($s_c_date[2]);
   }
}

if($fName=='' && $sample=='' && $_POST['sampleCollectionDate']==''){
    $query="SELECT sample_code,treament_id,facility_id FROM vl_request_form where batch_id is NULL OR batch_id=''";
}else{
if($_POST['sCode']!=''){
    $ids = implode(",",$_POST['sCode']);
    $query = "SELECT sample_code,treament_id,facility_id FROM vl_request_form where treament_id NOT IN (".$ids.") AND batch_id is NULL";
}else{
$query="SELECT sample_code,treament_id,facility_id FROM vl_request_form where batch_id is NULL";
}
if($fName!=''){
    $query = $query." AND facility_id='".$fName."'";
}
if($sample!=''){
    $query = $query." AND sample_id='".$sample."'";
}
if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
    if (trim($start_date) == trim($end_date)) {
        $query = $query.' AND DATE(sample_collection_date) = "'.$start_date.'"';
    }else{
       $query = $query.' AND DATE(sample_collection_date) >= "'.$start_date.'" AND DATE(sample_collection_date) <= "'.$end_date.'"';
    }
}
}

$result = $db->rawQuery($query);
$sResult = array();
if($_POST['sCode']!=''){
    $ids = implode(",",$_POST['sCode']);
    $sQuery="SELECT sample_code,treament_id,facility_id FROM vl_request_form where treament_id IN (".$ids.") ";
    $sResult = $db->rawQuery($sQuery);
}
$merge = array_merge($sResult, $result);
?>
<div class="col-md-8">
<div class="form-group">
    <div class="col-md-12">
      <div class="col-md-12">
         <div style="width:60%;margin:0 auto;clear:both;">
          <a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
          </div><br/><br/>
        <select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
        <?php
        foreach($merge as $sample){
            $selected = '';
            if (in_array($sample['treament_id'], $_POST['sCode'])){
              $selected = "selected=selected";
            }
          ?>
          <option value="<?php echo $sample['treament_id'];?>"<?php echo $selected;?>><?php  echo ucwords($sample['sample_code']);?></option>
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