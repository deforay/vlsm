<?php
include('header.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Users</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Users</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title">Add User</h3>

        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="userName" class="col-sm-2 control-label">User Name</label>

                  <div class="col-sm-5">
                    <input type="text" class="form-control" id="userName" placeholder="User Name">
                  </div>
                </div>
                <div class="form-group">
                  <label for="password" class="col-sm-2 control-label">Password</label>
                  <div class="col-sm-5">
                    <input type="password" class="form-control" id="password" placeholder="Password">
                  </div>
                </div>
               
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a href="users.php" class="btn btn-default"> Cancel</a>
                <button type="submit" class="btn btn-primary ">Submit</button>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->
    
     
     

    </section>
    <!-- /.content -->
  </div>
 <?php
 include('footer.php');
 ?>
