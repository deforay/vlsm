<?php
include('./includes/MysqliDb.php');
$fName = $_POST['fName'];
if($fName==''){
    $query="SELECT * FROM vl_request_form where batch_id is NULL OR batch_id=''";
}else{
$query="SELECT * FROM vl_request_form where facility_id='".$fName."' AND batch_id is NULL OR batch_id=''";
}
$result = $db->rawQuery($query);
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