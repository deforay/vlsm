<?php
session_start();
if(isset($_SESSION['userId'])){
    header("location:index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- iCheck -->



    <style>
        body{
            
            background: #F6F6F6;       
            background: #000;       
            
            background : url("assets/img/bg.jpg") center;
            background-size: cover;            
            background-repeat:no-repeat;    
        }
    </style> 
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script type="text/javascript" src="assets/js/jquery.min.2.0.2.js"></script>

</head>
<body class="">
    <div class="container-fluid">    
        <div id="loginbox" style="margin-top:15px;margin-bottom:70px;float:right;margin-right:-15px;" class="mainbox col-md-3 col-sm-8 ">                    
            <div class="panel panel-default" style="opacity: 0.9;">
                    <div class="panel-heading">
                        <div class="panel-title">Viral Load Sample Management</div>
                        
                    </div>     

                    <div style="padding-top:10px" class="panel-body" >

                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                            
                        <form id="loginForm" name="loginForm" class="form-horizontal" role="form" method="post" action="loginProcess.php" onsubmit="validateNow();return false;">
                                    
                            <div style="margin-bottom: 5px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="login-username" type="text" class="form-control isRequired" name="username" value="" placeholder="User Name" title="Please enter the user name">
                            </div>
                                
                            <div style="margin-bottom: 5px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="login-password" type="password" class="form-control isRequired" name="password" placeholder="Password" title="Please enter the password">
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <!-- Button -->
                                <div class="col-sm-12 controls">
                                    <button class="btn btn-lg btn-success btn-block" onclick="validateNow();return false;">Login</button>
                                </div>
                            </div>
                            </form>
                        </div>                     
                    </div>  
        </div>
    </div>
    <script src="assets/js/deforayValidation.js"></script>
    <script type="text/javascript">

    function validateNow(){
      flag = deforayValidator.init({
          formId: 'loginForm'
      });
      
      if(flag){
        document.getElementById('loginForm').submit();
      }
    }
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
    </script>
</body>
</html>
