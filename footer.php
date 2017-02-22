
  <footer class="main-footer">
    <a href="http://taskforce.org/">Funded by TaskForce</a>
  </footer>
</div>
<!-- ./wrapper -->


<!-- jQuery UI 1.11.4 -->
<!--<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script type="text/javascript" src="../assets/js/jquery-ui-timepicker-addon.js"></script>

<script src="../assets/js/select2.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../assets/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src=".././assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src=".././assets/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="../dist/js/app.min.js"></script>
<script src="../assets/js/deforayValidation.js"></script>
<script src="../assets/js/jquery.maskedinput.js"></script>
<script src="../assets/js/jquery.blockUI.js"></script>
<script src="../assets/js/moment.min.js"></script>

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
  }else if (splitsUrl=='roles.php' || splitsUrl=='editRole.php' || splitsUrl=='addRole.php') {
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
  }else if (splitsUrl=='otherConfig.php' || splitsUrl=='editOtherConfig.php' || splitsUrl=='editRequestEmailConfig.php' || splitsUrl=='editResultEmailConfig.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".otherConfigMenu").addClass('active');
  }else if (splitsUrl=='testRequestEmailConfig.php' || splitsUrl=='editTestRequestEmailConfig.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".requestEmailConfigMenu").addClass('active');
  }else if (splitsUrl=='testResultEmailConfig.php' || splitsUrl=='editTestResultEmailConfig.php') {
    $(".manage").addClass('active');
    $(".allMenu").removeClass('active');
    $(".resultEmailConfigMenu").addClass('active');
  }else if (splitsUrl=='vlRequest.php' || splitsUrl=='editVlRequest.php' || splitsUrl=='viewVlRequest.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlRequestMenu").addClass('active');
  }else if (splitsUrl=='addVlRequest.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".addVlRequestMenu").addClass('active');
  }else if (splitsUrl=='addVlRequestZm.php' || splitsUrl=='editVlRequestZm.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".addVlRequestZmMenu").addClass('active');
  }else if (splitsUrl=='batchcode.php' || splitsUrl=='addBatch.php' || splitsUrl=='editBatch.php' || splitsUrl=='addBatchControlsPosition.php' || splitsUrl=='editBatchControlsPosition.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".batchCodeMenu").addClass('active');
  }else if (splitsUrl=='vlRequestMail.php' || splitsUrl=='vlRequestMailConfirm.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlRequestMailMenu").addClass('active');
  }else if (splitsUrl=='vlResultMail.php' || splitsUrl=='vlResultMailConfirm.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlResultMailMenu").addClass('active');
  }else if (splitsUrl=='addImportResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importResultMenu").addClass('active');
  }else if (splitsUrl=='vlPrintResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlPrintResultMenu").addClass('active');
  }else if (splitsUrl=='vlTestResult.php' || splitsUrl=='updateVlTestResult.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlTestResultMenu").addClass('active');
  }else if (splitsUrl=='vlResultApproval.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlResultApprovalMenu").addClass('active');
  }else if (splitsUrl=='missingResult.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".missingResultMenu").addClass('active');
  }else if (splitsUrl=='vlResult.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlResultMenu").addClass('active');
  }else if (splitsUrl=='highViralLoad.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlHighMenu").addClass('active');
  }else if (splitsUrl=='patientList.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".patientList").addClass('active');
  }else if (splitsUrl=='addImportTestResult.php') {
    $(".request").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importTestResultMenu").addClass('active');
  }else if (splitsUrl=='addImportTestRequest.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importTestRequestMenu").addClass('active');
  }else if (splitsUrl=='addImportXmlTestRequest.php') {
    $(".test").addClass('active');
    $(".allMenu").removeClass('active');
    $(".importXmlTestRequestMenu").addClass('active');
  }else if (splitsUrl=='vlStatistics.php') {
    $(".program").addClass('active');
    $(".allMenu").removeClass('active');
    $(".vlStatistics").addClass('active');
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
  jQuery(".checkNum").keydown(function (e) {
	      // Allow: backspace, delete, tab, escape, enter and .
	      if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
		       // Allow: Ctrl+A
		      (e.keyCode == 65 && e.ctrlKey === true) || 
		       // Allow: home, end, left, right
		      (e.keyCode >= 35 && e.keyCode <= 39)) {
			       // let it happen, don't do anything
			       return;
	      }
	      // Ensure that it is a number and stop the keypress
	      if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		      e.preventDefault();
	      }
  });
</script>
</body>
</html>
