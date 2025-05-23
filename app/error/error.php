<?php

use App\Services\CommonService;
use App\Registries\ContainerRegistry;

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$errorReason ??= _translate('Internal Server Error') . ' - ';
$errorMessage ??= _translate('Sorry, something went wrong. Please try again later.');
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['APP_LOCALE'] ?? 'en_US'; ?>">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title><?= _translate('ERROR'); ?> | <?= $general->isSTSInstance() ? 'STS' : 'LIS'; ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="viewport" content="width=1024">

  <?php if (!empty($_SESSION['instance']['type']) && $general->isSTSInstance()) { ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/vlsts-icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/vlsts-icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/vlsts-icons/favicon-16x16.png">
    <link rel="manifest" href="/assets/vlsts-icons/site.webmanifest">
  <?php } else { ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/vlsm-icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/vlsm-icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/vlsm-icons/favicon-16x16.png">
    <link rel="manifest" href="/assets/vlsm-icons/site.webmanifest">
  <?php } ?>


  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/font-awesome.min.css">
  <link rel="stylesheet" href="/assets/css/AdminLTE.min.css">

  <style>
    .content-wrapper small {
      font-size: 1.4em !important;
      color: #555;
    }

    .content-wrapper a {
      font-size: 0.9em !important;
      text-decoration: underline;
    }
  </style>

</head>

<body class="<?php echo $skin ?? ''; ?>" id="capture">
  <div class="wrapper">
    <div class="content-wrapper" style="margin:0;height:700px !important;padding:0 100px;">
      <section class="">
        <div class="row">
          <div class="col-xs-12" style="text-align: center;">
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <h2 style="color:red;text-align: center;font-size:6em;font-weight:500;">
              <span class="fa-solid fa-triangle-exclamation"></span>
              <?= _translate("An error occurred"); ?>

            </h2>
            <h3 style="color:#555;font-weight:bold;font-size:1.4em;">
              <?= _translate("Error Code") . " : " . $httpCode . " - " . $errorReason; ?>
            </h3>

            <h3 style="color:#555;font-weight:bold;font-size:1.4em;">
              <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <small>
              <?= _translate("Please contact the System Admin for further support."); ?>
            </small>
            <br>
            <br>
            <small>
              <a href="/">
                <?= _translate("Go to Dashboard"); ?>
              </a>
            </small>

          </div>
        </div>
      </section>
    </div>
  </div>

  <footer class="main-footer" style="margin:0;min-height:100px !important;text-align:center;">
    <small>
      <?= _translate("This project is supported by the U.S. President's Emergency Plan for AIDS Relief (PEPFAR) through the U.S.
		Centers for Disease Control and Prevention (CDC)."); ?>
    </small>
  </footer>
  </div>
</body>

</html>
