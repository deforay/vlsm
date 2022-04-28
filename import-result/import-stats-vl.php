<?php

// imported in importedStatistics.php

$tsQuery = "SELECT COUNT(temp_sample_id) AS totalCount, SUM(CASE WHEN tsr.result = 'Target Not Detected' OR tsr.result = 'target not detected' THEN 1 ELSE 0 END) AS TargetNotDetected, SUM(CASE WHEN tsr.result > 1000 AND (tsr.result !='Target Not Detected' OR tsr.result != 'target not detected') THEN 1 ELSE 0 END) AS HighViralLoad, SUM(CASE WHEN tsr.result < 1000 AND (tsr.result !='Target Not Detected' OR tsr.result != 'target not detected') THEN 1 ELSE 0 END) AS LowViralLoad,SUM(CASE WHEN tsr.result = 'Invalid' OR tsr.result = 'invalid' THEN 1 ELSE 0 END) AS invalid FROM temp_sample_import as tsr $import_decided form_vl as vl ON vl.sample_code=tsr.sample_code WHERE  imported_by ='$importedBy' ";
$tsResult = $db->rawQuery($tsQuery);

//set print query
$hQuery = "SELECT hsr.sample_code FROM hold_sample_import as hsr $import_decided form_vl as vl ON vl.sample_code=hsr.sample_code";
$hResult = $db->rawQuery($hQuery);
$holdSample = array();
if ($hResult) {
    foreach ($hResult as $sample) {
        $holdSample[] = $sample['sample_code'];
    }
}
$saQuery = "SELECT tsr.sample_code FROM temp_sample_import as tsr $import_decided form_vl as vl ON vl.sample_code=tsr.sample_code WHERE  imported_by ='$importedBy' ";
$saResult = $db->rawQuery($saQuery);
$sampleCode = array();
foreach ($saResult as $sample) {
    if (!in_array($sample['sample_code'], $holdSample)) {
        $sampleCode[] = "'" . $sample['sample_code'] . "'";
    }
}
$sCode = implode(', ', $sampleCode);
$samplePrintQuery = "SELECT vl.*,
f.*,
imp.i_partner_name,
rst.*,
vltr.test_reason_name,
l.facility_name as labName,
u_d.user_name as reviewedBy,
a_u_d.user_name as approvedBy,
r_r_b.user_name as revised,
l.facility_logo as facilityLogo,
rsrr.rejection_reason_name 
FROM form_vl as vl 
LEFT JOIN r_vl_test_reasons as vltr ON vl.reason_for_vl_testing = vltr.test_reason_id 
LEFT JOIN facility_details as f ON vl.facility_id = f.facility_id 
LEFT JOIN r_vl_sample_type as rst ON rst.sample_id = vl.sample_type 
LEFT JOIN user_details as u_d ON u_d.user_id = vl.result_reviewed_by
LEFT JOIN user_details as a_u_d ON a_u_d.user_id = vl.result_approved_by
LEFT JOIN user_details as r_r_b ON r_r_b.user_id = vl.revised_by
LEFT JOIN facility_details as l ON l.facility_id = vl.lab_id 
LEFT JOIN r_implementation_partners as imp ON imp.i_partner_id = vl.implementing_partner
LEFT JOIN r_vl_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id = vl.reason_for_sample_rejection ";
$samplePrintQuery .= ' WHERE vl.sample_code IN ( ' . $sCode . ')'; // Append to condition
$_SESSION['vlRequestSearchResultQuery'] = $samplePrintQuery;


// We can clear the temp sample import table
// $db = $db->where('imported_by', $_SESSION['userId']);
// $db->delete('temp_sample_import');
unset($_SESSION['controllertrack']);

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Imported Results</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="vlRequestDataTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 13%;">No. of Results imported</th>
                                    <th style="width: 11%;">No. of High Viral Load results</th>
                                    <th style="width: 18%;">No. of Low Viral Load results</th>
                                    <th style="width: 18%;">No. of Target Not Detected</th>
                                    <th style="width: 18%;">No. of Invalid results</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo (isset($tsResult[0]['totalCount'])) ? $tsResult[0]['totalCount'] : 0; ?></td>
                                    <td><?php echo (isset($tsResult[0]['HighViralLoad'])) ? $tsResult[0]['HighViralLoad'] : 0; ?></td>
                                    <td><?php echo (isset($tsResult[0]['LowViralLoad'])) ? $tsResult[0]['LowViralLoad'] : 0; ?></td>
                                    <td><?php echo (isset($tsResult[0]['TargetNotDetected'])) ? $tsResult[0]['TargetNotDetected'] : 0; ?></td>
                                    <td><?php echo (isset($tsResult[0]['invalid'])) ? $tsResult[0]['invalid'] : 0; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:30px;width: 75%;">
                        <tr>
                            <td>
                                <?php
                                if (isset($tsResult[0]['totalCount']) && $tsResult[0]['totalCount'] > 0) { ?>
                                    <input type="button" onclick="convertSearchResultToPdf();return false;" value="Print all results" class="btn btn-success btn-sm">&nbsp;&nbsp;
                                    <a href="/vl/results/vlPrintResult.php" class="btn btn-success btn-sm">Continue without printing results</a>
                                <?php } else { ?>
                                    <a href="/vl/results/vlPrintResult.php" class="btn btn-success btn-sm">Continue </a>
                                <?php } ?>
                            </td>
                        </tr>

                    </table>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<script>
    function convertSearchResultToPdf() {
        $.blockUI();
        <?php
        $path = '';
        $path = '/vl/results/generate-result-pdf.php';
        ?>
        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: '',
                sampleCodes: '<?php echo $sCode; ?>'
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    $.unblockUI();
                    alert('Unable to generate download');
                } else {
                    $.unblockUI();
                    window.open('/download.php?f=' + data, '_blank');
                    // window.location.href = "/vl/results/vlPrintResult.php";
                }

            });
    }
</script>