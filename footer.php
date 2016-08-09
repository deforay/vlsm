
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