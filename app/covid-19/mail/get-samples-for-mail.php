<?php



$general = new \App\Models\General();
$configQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
$configResult = $db->rawQuery($configQuery);
$formId = 0;
if (isset($configResult[0]['value']) && trim($configResult[0]['value']) != '') {
  $formId = intval($configResult[0]['value']);
}
if (!is_array($_POST['facility']) || count($_POST['facility']) == 0) {
  $_POST['facility'] = array();
}
if (empty($_POST['batch']) || !is_array($_POST['batch']) || count($_POST['batch']) == 0) {
  $_POST['batch'] = array();
}
$facility = $_POST['facility'];
$sampleType = $_POST['sType'];
$gender = $_POST['gender'];
$state = $_POST['state'];
$district = $_POST['district'];
$batch = $_POST['batch'];
$mailSentStatus = $_POST['mailSentStatus'];
$type = $_POST['type'];
//print_r($_POST);die;
$start_date = '';
$end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
  $s_c_date = explode("to", $_POST['sampleCollectionDate']);
  if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
    $start_date = $general->isoDateFormat(trim($s_c_date[0]));
  }
  if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
    $end_date = $general->isoDateFormat(trim($s_c_date[1]));
  }
}

$query = "SELECT vl.sample_code,vl.covid19_id,vl.facility_id,f.facility_name,f.facility_code FROM form_covid19 as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where ((vl.result_status = 7 AND vl.result is NOT NULL AND vl.result !='') OR (vl.result_status = 4 AND (vl.result is NULL OR vl.result = '')))";
if (isset($facility) && count(array_filter($facility)) > 0) {
  $query = $query . " AND vl.facility_id IN (" . implode(',', $facility) . ")";
}
if (trim($sampleType) != '') {
  $query = $query . " AND vl.sample_type='" . $sampleType . "'";
}
if (trim($gender) != '') {
  $query = $query . " AND vl.patient_gender='" . $gender . "'";
}
if (trim($state) != '') {
  $query = $query . " AND f.facility_state LIKE '%" . $state . "%' ";
}
if (trim($district) != '') {
  $query = $query . " AND f.facility_district LIKE '%" . $district . "%' ";
}
if (isset($batch) && count(array_filter($batch)) > 0) {
  $query = $query . " AND vl.sample_batch_id IN (" . implode(',', $batch) . ")";
}
if (isset($_POST['status']) && trim($_POST['status']) != '') {
  $query = $query . " AND vl.result_status='" . $_POST['status'] . "'";
}
if (trim($mailSentStatus) != '') {
  if (trim($type) == 'request') {
    $query = $query . " AND vl.is_request_mail_sent='" . $mailSentStatus . "'";
  } elseif (trim($type) == 'result') {
    $query = $query . " AND vl.is_result_mail_sent='" . $mailSentStatus . "' AND vl.result IS NOT NULL AND vl.result!= ''";
  }
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
  if (trim($start_date) == trim($end_date)) {
    $query = $query . ' AND DATE(sample_collection_date) = "' . $start_date . '"';
  } else {
    $query = $query . ' AND DATE(sample_collection_date) >= "' . $start_date . '" AND DATE(sample_collection_date) <= "' . $end_date . '"';
  }
}
$query = $query . " ORDER BY f.facility_name ASC";
//echo $query;die;
$result = $db->rawQuery($query);
?>
<div class="col-md-9">
  <div class="form-group">
    <label for="sample" class="col-lg-3 control-label">Choose Sample(s) <span class="mandatory">*</span></label>
    <div class="col-lg-9">
      <div style="width:100%;margin:0 auto;clear:both;">
        <a href="#" id="select-all-sample" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<em class="fa-solid fa-chevron-right"></em></a> <a href="#" id="deselect-all-sample" style="float:right" class="btn btn-danger btn-xs"><em class="fa-solid fa-chevron-left"></em>&nbsp;Deselect All</a>
      </div><br /><br />
      <select id="sample" name="sample[]" multiple="multiple" class="search isRequired" title="Please select sample(s)">
        <?php
        foreach ($result as $sample) {
          if (trim($sample['sample_code']) != '') {
        ?>
            <option value="<?php echo $sample['covid19_id']; ?>"><?php echo ($sample['sample_code']); ?></option>
        <?php
          }
        }
        ?>
      </select>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $('.search').multiSelect({
      selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
      selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
      afterInit: function(ms) {
        var that = this,
          $selectableSearch = that.$selectableUl.prev(),
          $selectionSearch = that.$selectionUl.prev(),
          selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
          selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

        that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
          .on('keydown', function(e) {
            if (e.which === 40) {
              that.$selectableUl.focus();
              return false;
            }
          });

        that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
          .on('keydown', function(e) {
            if (e.which == 40) {
              that.$selectionUl.focus();
              return false;
            }
          });
      },
      afterSelect: function() {
        if (this.qs2.cache().matchedResultsCount > noOfAllowedSamples) {
          $("#errorMsg").html("<strong>You have selected " + this.qs2.cache().matchedResultsCount + " Samples out of the maximum allowed " + noOfAllowedSamples + " samples</strong>");
          $("#requestSubmit").attr("disabled", true);
          $("#requestSubmit").css("pointer-events", "none");
        }
        this.qs1.cache();
        this.qs2.cache();
      },
      afterDeselect: function() {
        if (this.qs2.cache().matchedResultsCount > noOfAllowedSamples) {
          $("#errorMsg").html("<strong>You have selected " + this.qs2.cache().matchedResultsCount + " Samples out of the maximum allowed " + noOfAllowedSamples + " samples</strong>");
          $("#requestSubmit").attr("disabled", true);
          $("#requestSubmit").css("pointer-events", "none");
        } else if (this.qs2.cache().matchedResultsCount <= noOfAllowedSamples) {
          $("#errorMsg").html("");
          $("#requestSubmit").attr("disabled", false);
          $("#requestSubmit").css("pointer-events", "auto");
        }
        this.qs1.cache();
        this.qs2.cache();
      }
    });
    $('#select-all-sample').click(function() {
      $('#sample').multiSelect('select_all');
      return false;
    });
    $('#deselect-all-sample').click(function() {
      $('#sample').multiSelect('deselect_all');
      return false;
    });
  });
</script>