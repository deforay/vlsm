<?php

use App\Registries\AppRegistry;
use App\Services\TbService;
use App\Services\VlService;
use App\Services\EidService;
use App\Services\Covid19Service;
use App\Services\DatabaseService;
use App\Services\HepatitisService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = $request->getParsedBody();


/* Selected Sample Types from Facility Edit */

$selectedSamplesTypes = [];
if (!empty($_POST['facilityId'])) {
    $db->where('facility_id', base64_decode((string) $_POST['facilityId']));
    $facility = $db->getOne('facility_details', array('facility_attributes'));
    $selectedSamplesTypes = json_decode((string) $facility['facility_attributes'], true);
}
$sampleType = [];
if ($_POST['testType'] != "") {
    foreach ($_POST['testType'] as $test) {
        if ($test == 'vl') {
            /** @var VlService $vlService */
            $vlService = ContainerRegistry::get(VlService::class);
            $sampleType['vl'] = $vlService->getVlSampleTypes();
        }
        if ($test == 'eid') {
            /** @var EidService $eidService */
            $eidService = ContainerRegistry::get(EidService::class);
            $sampleType['eid'] = $eidService->getEidSampleTypes();
        }
        if ($test == 'covid19') {
            /** @var Covid19Service $covid19Service */
            $covid19Service = ContainerRegistry::get(Covid19Service::class);
            $sampleType['covid19'] = $covid19Service->getCovid19SampleTypes();
        }
        if ($test == 'hepatitis') {
            /** @var HepatitisService $hepatitisService */
            $hepatitisService = ContainerRegistry::get(HepatitisService::class);
            $sampleType['hepatitis'] = $hepatitisService->getHepatitisSampleTypes();
        }
        if ($test == 'tb') {
            /** @var TbService $tbService */
            $tbService = ContainerRegistry::get(TbService::class);
            $sampleType['tb'] = $tbService->getTbSampleTypes();
        }
        $selectedType[$test] = explode(",", (string) $selectedSamplesTypes['sampleType'][$test]);
    }
}
if (!empty($sampleType)) { ?>
    <hr>
    <table aria-describedby="table" class="col-lg-12 table table-bordered" style=" width: 80%;">
        <thead>
            <tr>
                <th style="text-align:center;"><?php echo _translate("Test Category"); ?></th>
                <th style="text-align:center;"><?php echo _translate("Sample Type"); ?></th>
            </tr>
        </thead>
        <tbody id="sampleTypeTable">
            <?php foreach ($_POST['testType'] as $test) { ?>
                <tr>
                    <td style="text-align:center;"><?= strtoupper(htmlspecialchars((string) $test)); ?></td>
                    <td>
                        <select name="sampleType[<?php echo $test; ?>][]" id="sampleType<?php echo $test; ?>" title="Please select the sample type for <?= htmlspecialchars((string) $test); ?>" multiple>
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
            $("#sampleType<?= htmlspecialchars((string) $test); ?>").multipleSelect({
                placeholder: 'Select <?= strtoupper(htmlspecialchars((string) $test)); ?> Sample Type',
                width: '100%'
            });
        <?php } ?>
    });
</script>
