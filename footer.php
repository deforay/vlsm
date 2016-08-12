
  <footer class="main-footer">
    <a href="http://taskforce.org/">Funded by TaskForce</a>
  </footer>
</div>
<!-- ./wrapper -->


<!-- jQuery UI 1.11.4 -->
<!--<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script type="text/javascript" src="assets/js/jquery-ui-timepicker-addon.js"></script>

	<script src="assets/js/select2.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="assets/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="./assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./assets/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<script src="assets/js/deforayValidation.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
  <?php
  if(isset($_SESSION['alertMsg']) && trim($_SESSION['alertMsg'])!=""){
  ?>
    alert('<?php echo $_SESSION['alertMsg']; ?>');
  <?php
  $_SESSION['alertMsg']='';
  unset($_SESSION['alertMsg']);
  }
  ?>
  });
  str=$(location).attr('pathname');
  splitsUrl=str.substr(str.lastIndexOf('/') + 1);
  if (splitsUrl=='users.php' || splitsUrl=='addUser.php' || splitsUrl=='editUser.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".userMenu").addClass('active');
  }else if (splitsUrl=='roles.php' || splitsUrl=='editRole.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".roleMenu").addClass('active');
  }else if (splitsUrl=='facilities.php' || splitsUrl=='addFacility.php' || splitsUrl=='editFacility.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".facilityMenu").addClass('active');
  }else if (splitsUrl=='globalConfig.php' || splitsUrl=='editGlobalConfig.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".globalConfigMenu").addClass('active');
  }else if (splitsUrl=='importConfig.php' || splitsUrl=='addImportConfig.php' || splitsUrl=='editImportConfig.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importConfigMenu").addClass('active');
  }else if (splitsUrl=='vlRequest.php' || splitsUrl=='editVlRequest.php' || splitsUrl=='viewVlRequest.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlRequestMenu").addClass('active');
  }else if (splitsUrl=='addVlRequest.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".addVlRequestMenu").addClass('active');
  }else if (splitsUrl=='batchcode.php' || splitsUrl=='addBatch.php' || splitsUrl=='editBatch.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".batchCodeMenu").addClass('active');
  }else if (splitsUrl=='addImportResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importResultMenu").addClass('active');
  }else if (splitsUrl=='vlPrintResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlPrintResultMenu").addClass('active');
  }else if (splitsUrl=='vlTestResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlTestResultMenu").addClass('active');
  }else if (splitsUrl=='missingResult.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".missingResultMenu").addClass('active');
  }else if (splitsUrl=='vlResult.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlResultMenu").addClass('active');
  }else{
    $(".allMenu").removeClass('active');
    $(".dashboardMenu").addClass('active');
  }
  
  function showModal(url, w, h) {
      showdefModal('dDiv', w, h);
      document.getElementById('dFrame').style.height = h + 'px';
      document.getElementById('dFrame').style.width = w + 'px';
      document.getElementById('dFrame').src = url;
  }
  function closeModal() {
      document.getElementById('dFrame').src = "";
      hidedefModal('dDiv');
  }
</script>
</body>
</html>