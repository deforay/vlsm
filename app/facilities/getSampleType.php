<?php
$vlDb = new \Vlsm\Models\Vl($db);
$eidDb = new \Vlsm\Models\Eid($db);
$covid19Db = new \Vlsm\Models\Covid19($db);
$hepatitisDb = new \Vlsm\Models\Hepatitis($db);
$tbDb = new \Vlsm\Models\Tb($db);
/* Selected Sample Types from Facility Edit */
$selectedSamplesTypes = array();
if (!empty($_POST['facilityId'])) {
    $db = $db->where('facility_id', base64_decode($_POST['facilityId']));
    $facility = $db->getOne('facility_details', array('facility_attributes'));
    $selectedSamplesTypes = json_decode($facility['facility_attributes'], true);
}
$sampleType = array();
if ($_POST['testType'] != "") {
    foreach ($_POST['testType'] as $test) {
        if ($test == 'vl') {
            $sampleType['vl'] = $vlDb->getVlSampleTypes();
        }
        if ($test == 'eid') {
            $sampleType['eid'] = $eidDb->getEidSampleTypes();
        }
        if ($test == 'covid19') {
            $sampleType['covid19'] = $covid19Db->getCovid19SampleTypes();
        }
        if ($test == 'hepatitis') {
            $sampleType['hepatitis'] = $hepatitisDb->getHepatitisSampleTypes();
        }
        if ($test == 'tb') {
            $sampleType['tb'] = $tbDb->getTbSampleTypes();
        }
        $selectedType[$test] = explode(",", $selectedSamplesTypes['sampleType'][$test]);
    }
}
if (isset($sampleType) && count($sampleType) > 0) { ?>
    <hr>
    <table class="col-lg-12 table table-bordered" style=" width: 80%; ">
        <thead>
            <tr>
                <th style="text-align:center;"><?php echo _("Test Category"); ?></th>
                <th style="text-align:center;"><?php echo _("Sample Type"); ?></th>
            </tr>
        </thead>
        <tbody id="sampleTypeTable">
            <?php foreach ($_POST['testType'] as $test) { ?>
                <tr>
                    <td style="text-align:center;"><?php echo strtoupper($test); ?></td>
                    <td>
                        <select name="sampleType[<?php echo $test; ?>][]" id="sampleType<?php echo $test; ?>" title="Please select the sample type for <?php echo $test; ?>" multiple>
                            <?php foreach ($sampleType[$test] as $id => $type) { ?>
                                <option value="<?php echo $id; ?>" <?php echo (in_array($id, $selectedType[$test])) ? "selected='selected'" : ""; ?>><?php echo $type; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function() {
        <?php foreach ($_POST['testType'] as $test) { ?>
            $("#sampleType<?php echo $test; ?>").multipleSelect({
                placeholder: 'Select <?php echo strtoupper($test); ?> Sample Type',
                width: '100%'
            });
        <?php } ?>
    });
</script>