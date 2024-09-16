<?php

use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$keyFromGlobalConfig = $general->getGlobalConfig('key');

$uniqueId = null;
$decryption = CommonService::decryptViewQRCode($_GET['q']);
$data = explode('&&&', urldecode($decryption));
$uniqueId = $data[0] ?? null;

$invalidRequest = _translate("INVALID REQUEST");
if (empty($uniqueId)) {
    die("<br><br><br><br><br><br><h1 style='text-align:center;font-family:arial;font-size:1.3em;'>$invalidRequest</h1>");
}

$db->where("unique_id", $uniqueId);
$res = $db->getOne("form_covid19", "covid19_id");

if (empty($res)) {
    http_response_code(400);
    die("<br><br><br><br><br><br><h1 style='text-align:center;font-family:arial;font-size:1.3em;'>$invalidRequest</h1>");
}

$id = $res['covid19_id'];
?>
<style nonce="<?= $_SESSION['nonce']; ?>">
    #the-canvas {
        border: 1px solid black;
        direction: ltr;
        margin-left: 15%;
    }
</style>
<script nonce="<?= $_SESSION['nonce']; ?>" type="text/javascript" src="/assets/js/jquery.min.js"></script>
<script nonce="<?= $_SESSION['nonce']; ?>" src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js" integrity="sha512-dw+7hmxlGiOvY3mCnzrPT5yoUwN/MRjVgYV7HGXqsiXnZeqsw1H9n9lsnnPu4kL2nx2bnrjFcuWK+P3lshekwQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<canvas id="the-canvas"></canvas>

<script nonce="<?= $_SESSION['nonce']; ?>" type="text/javascript">
    $(document).ready(function() {
        convertSearchResultToPdf(<?php echo ($id); ?>);
    });

    function convertSearchResultToPdf(id) {

        <?php
        $path = '/covid-19/results/generate-result-pdf.php';
        ?>

        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: id,
                type: "qr",
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    alert('Unable to generate result PDF');
                } else {
                    var url = atob(data);
                    url = url.split('\\').pop().split('/').pop();
                    url = '/temporary/' + url;
                    // Loaded via <script> tag, create shortcut to access PDF.js exports.
                    var pdfjsLib = window['pdfjs-dist/build/pdf'];

                    // The workerSrc property shall be specified.
                    pdfjsLib.GlobalWorkerOptions.workerSrc = '//cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.worker.min.js';
                    // If absolute URL from the remote server is provided, configure the CORS
                    // header on that server.

                    // Asynchronous download of PDF
                    var loadingTask = pdfjsLib.getDocument(url);
                    loadingTask.promise.then(function(pdf) {

                        // Fetch the first page
                        var pageNumber = 1;
                        pdf.getPage(pageNumber).then(function(page) {

                            var scale = 1.5;
                            var viewport = page.getViewport({
                                scale: scale
                            });

                            // Prepare canvas using PDF page dimensions
                            var canvas = document.getElementById('the-canvas');
                            var context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            // Render PDF page into canvas context
                            var renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            var renderTask = page.render(renderContext);
                            renderTask.promise.then(function() {});
                        });
                    }, function(reason) {
                        // PDF loading error
                        console.error(reason);
                    });
                }
            });
    }
</script>
