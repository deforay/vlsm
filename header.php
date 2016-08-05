<?php
session_start();
date_default_timezone_set("Europe/London"); 
if(!isset($_SESSION['userId'])){
    header("location:login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>VL Lab Request</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui.1.11.0.css" />
  <link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui-timepicker-addon.css" />

  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/css/font-awesome.min.4.5.0.css">
  <!-- Ionicons -->
  <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">-->
  <!-- DataTables -->
  <link rel="stylesheet" href="./assets/plugins/datatables/dataTables.bootstrap.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <!-- iCheck -->
  
  <link href="assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" />
  
  <link href="assets/css/select2.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <link href="assets/css/deforayModal.css" rel="stylesheet" />
 
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- jQuery 2.2.3 -->

<script type="text/javascript" src="assets/js/jquery.min.2.0.2.js"></script>

 <!-- Latest compiled and minified JavaScript -->
    
    <script type="text/javascript" src="assets/js/jquery-ui.1.11.0.js"></script>
<script src="assets/js/deforayModal.js"></script>
  <!--<script type="text/javascript" src="assets/js/jquery-ui-sliderAccess.js"></script>-->

</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>VL</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>VL</b> LAB Request</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
         
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="assets/img/default-user.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $_SESSION['userName']; ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- Menu Footer-->
              <li class="user-footer">
                  <a href="logout.php" class="">Sign out</a>
              </li>
            </ul>
          </li>
         
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      

      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
      
        <li class="treeview">
          <a href="#">
            <i class="fa fa-gears"></i>
            <span>Admin</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="users.php"><i class="fa fa-circle-o"></i> Manage Users</a></li>
            <li><a href="facilities.php"><i class="fa fa-circle-o"></i> Manage Facilities</a></li>
            <!--<li><a href="globalConfig.php"><i class="fa fa-circle-o"></i> Manage Global Config</a></li>-->
          </ul>
        </li>
		
	<li class="treeview">
          <a href="#">
            <i class="fa fa-edit"></i>
            <span>Request Management</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="vlRequest.php"><i class="fa fa-circle-o"></i> View Test Request</a></li>
            <li><a href="addVlRequest.php"><i class="fa fa-circle-o"></i> Add New Request</a></li>
	    <li><a href="batchcode.php"><i class="fa fa-circle-o"></i> Create Batch</a></li>
          </ul>
        </li>
		
        
        <!---->
        
        
      </ul>
      
    </section>
    <!-- /.sidebar -->
  </aside>
  <!-- content-wrapper -->
  <div id="dDiv" class="dialog"> 
            <div style="text-align:center"><span onclick="closeModal();" style="float:right;clear:both;" class="closeModal"></span></div> 
            <iframe id="dFrame" src="" style="border:none;" scrolling="yes" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0">some problem</iframe> 
    </div>