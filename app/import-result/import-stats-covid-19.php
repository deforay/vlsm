<?php

// imported in importedStatistics.php

use App\Registries\ContainerRegistry;
use App\Services\Covid19Service;


/** @var Covid19Service $covid19Service */
$covid19Service = ContainerRegistry::get(Covid19Service::class);
$covid19Results = $covid19Service->getCovid19Results();

$tsQuery = "SELECT COUNT(temp_sample_id) AS totalCount,
            SUM(CASE WHEN tsr.result = 'positive' THEN 1 ELSE 0 END) AS positive,
            SUM(CASE WHEN tsr.result = 'negative' THEN 1 ELSE 0 END) AS negative,
            SUM(CASE WHEN tsr.result = 'indeterminate' THEN 1 ELSE 0 END) AS indeterminate
            FROM temp_sample_import as tsr $joinTypeWithTestTable form_covid19 as vl ON vl.sample_code=tsr.sample_code
            WHERE  imported_by ='$importedBy' ";
$tsResult = $db->rawQuery($tsQuery);

//set print query
$hQuery = "SELECT hsr.sample_code FROM hold_sample_import as hsr $joinTypeWithTestTable form_covid19 as vl ON vl.sample_code=hsr.sample_code";
$hResult = $db->rawQuery($hQuery);
$holdSample = [];
if ($hResult) {
    foreach ($hResult as $sample) {
        $holdSample[] = $sample['sample_code'];
    }
}
$saQuery = "SELECT tsr.sample_code
            FROM temp_sample_import as tsr $joinTypeWithTestTable form_covid19 as vl ON vl.sample_code=tsr.sample_code
                WHERE  imported_by ='$importedBy' ";
$saResult = $db->rawQuery($saQuery);
$sampleCode = [];
foreach ($saResult as $sample) {
    if (!in_array($sample['sample_code'], $holdSample)) {
        $sampleCode[] = "'" . $sample['sample_code'] . "'";
    }
}
$sCode = implode(', ', $sampleCode);
$samplePrintQuery = "SELECT vl.*, b.*, ts.*, f.facility_name, l_f.facility_name as labName,
                        f.facility_code,
                        f.facility_state,
                        f.facility_district,
                        l.report_format as reportFormat,
                        l.facility_attributes,
                        u_d.user_name as reviewedBy,
                        a_u_d.user_name as approvedBy,
                        rs.rejection_reason_name
                        FROM form_covid19 as vl
                        LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
                        LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
                        INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status
                        LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
                        LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
                        LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
                        LEFT JOIN r_covid19_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
                        WHERE vl.sample_code IN ('$sCode')";

$_SESSION['covid19PrintSearchResultQuery'] = $samplePrintQuery;


// We can clear the temp sample import table
//$db->where('imported_by', $_SESSION['userId']);
//$db->delete('temp_sample_import');
// unset($_SESSION['controllertrack']);

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Imported Results</h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
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
                        <table aria-describedby="table" id="vlRequestDataTable" class="table table-bordered table-striped" aria-hidden="true">
                            <thead>
                                <tr>
                                    <th style="width: 13%;">No. of Results imported</th>
                                    <th style="width: 11%;">No. of
                                        <?php echo $covid19Results['positive']; ?>
                                    </th>
                                    <th style="width: 18%;">No. of
                                        <?php echo $covid19Results['negative']; ?>
                                    </th>
                                    <th style="width: 18%;">No. of
                                        <?php echo $covid19Results['indeterminate']; ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php echo (isset($tsResult[0]['totalCount'])) ? $tsResult[0]['totalCount'] : 0; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($tsResult[0]['positive'])) ? $tsResult[0]['positive'] : 0; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($tsResult[0]['negative'])) ? $tsResult[0]['negative'] : 0; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($tsResult[0]['indeterminate'])) ? $tsResult[0]['indeterminate'] : 0; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:30px;width: 75%;">
                        <tr>
                            <td>
                                <a href="/covid-19/results/covid-19-print-results.php" class="btn btn-success btn-sm">Continue to Print Results</a>
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
        $path = '/covid-19/results/generate-result-pdf.php';
        ?>
        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: ''
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    $.unblockUI();
                    alert("<?= _translate("Unable to generate download"); ?>");
                } else {
                    $.unblockUI();
                    window.open('/download.php?f=' + data, '_blank');
                    window.location.href = "/covid-19/results/covid-19-print-results.php";
                }

            });
    }
</script>
