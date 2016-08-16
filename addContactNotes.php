<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
include('General.php');
$id=base64_decode($_GET['id']);
$contactInfo="SELECT * from contact_notes_details where treament_contact_id=$id";
$contact=$db->query($contactInfo);
$general=new Deforay_Commons_General();
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Add Contact Notes</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Contact Notes</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post'  name='contactNotes' id='contactNotes' autocomplete="off" action="addContactNotesHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="col-lg-4 control-label">Contact Notes<span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <textarea class="form-control isRequired" name="notes" id="notes" title="Please enter contact notes" placeholder="Enter Contact Notes"></textarea>
                        <input type="hidden" name="treamentId" id="treamentId" value="<?php echo $id;?>"/>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="highViralLoad.php" class="btn btn-default"> Cancel</a>
              </div>
            </form>
            
            <!--histroy of contact notes-->
            
                  <div class="row">
                    <div class="col-xs-12">
                      <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <h2>Contact Notes History</h2>
                          <table id="contactNotesDetails" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                              <th>Contact Notes</th>
                              <th>Added On</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(count($contact)>0){
                                    foreach($contact as $notes){
                                        $date = explode(" ",$notes['added_on']);
                                        $humanDate = $general->humanDateFormat($date[0]);
                                        ?>
                                        <tr>
                                        <td><?php echo $notes['contact_notes'];?></td>
                                        <td><?php echo $humanDate." ".$date[1];?></td>
                                        </tr>
                                        <?php
                                    }
                                }else{
                                ?>
                                <tr>
                                    <td colspan="2" class="dataTables_empty">Loading data from server</td>
                                </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </div>
                        <!-- /.box-body -->
                      </div>
                      <!-- /.box -->
                    </div>
                    <!-- /.col -->
                  </div>

            
        </div>
      </div>
    </section>
</div>
 <script type="text/javascript">
var oTable = null;
  $(document).ready(function() {
    <?php if(count($contact)>0){ ?>
     oTable = $('#contactNotesDetails').dataTable({"aaSorting": [[ 1, "desc" ]]});
     <?php } ?>
  });
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'contactNotes'
    });
    
    if(flag){
      document.getElementById('contactNotes').submit();
    }
  }
  
</script>
<?php
include('footer.php');
?>