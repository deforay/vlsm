<?php
include'common/connection.php';
$dept="";
$inquery="SELECT * FROM dept_dt";
$xquery=mysql_query($inquery);
//while($row=mysql_fetch_array($xquery))
//{
//$dept.='<option value="'.$row['dept_id'].'">'.$row['dept_name'].'</option>';
//}
?>
<html>
    <head>
        <title>Job Pinner</title>
        <style>
            .label
            {
                font-family: arial;
                font-size: 13px;
                color:#000000;
            }
            .content
            {
                font-family: arial;
                font-size: 15px;
                color:#000000;   
            }
            .add
            {
                font-family: arial;
                font-size: 13px;
                padding:5px;
                background-color:green;
                color:white;
                font-weight:bold;
            }
            .remove
            {
                font-family: arial;
                font-size: 13px;
                padding:5px;
                background-color:red;
                color:white;
                font-weight:bold;
            }
            .text_box
            {
                width:300px;
                height:22px;
                border:1px solid #989898;
            }
        </style>
    </head>
    <body>
        <form name="anr" method="post" action="checkAnr.php" autocomplete="off">
        <table align="center" cellspacing="3" cellpadding="2" width="700">
        <thead>
            <tr>
             <th align="center" valign="middle" class="label" colspan="2">Department</th>
            </tr>
            <tr>
             <th align="center" valign="middle" class="content" colspan="2">
                <select name="dept" id="dept">
                    <option value=""> -- Select -- </option>
                   <?php while($row=mysql_fetch_array($xquery))
                     {?>
                     <option value="<?php echo $row['dept_id'];?>"><?php echo $row['dept_name']; ?></option>
                   <?php }
                    ?>
                </select>
            </th>
            </tr>
            <tr>
                <th align="center" valign="middle" class="label">Link Name</th>
                <th align="center" valign="middle"  class="label">Add&nbsp&nbsp&nbsp&nbspRemove</th>
            </tr>
        </thead>
        <tbody id="heart_row">
          <tr>
            <td align="center" valign="middle"><input type="text" name="link_name[]" id="link_name1" class="text_box"/></td>
            <td align="center" valign="middle"><a  href="javascript:void(0);" style="text-decoration:none;" onclick="Addrow();" class="add">Add</a>&nbsp&nbsp<a href="javascript:void(0);" class="remove" style="text-decoration:none;" onclick="Removerow(this.parentNode.parentNode);">Remove</a></td>
          </tr>
        </tbody>
        <tr>
            <td align="center" valign="middle" colspan="2"><input type="submit" name="Submit" value="Submit"/></td>
        </tr>
        </table>
        </form>
    </body>
</html>
<script src="js/jquery-1.10.2.js"></script>
<script>
   var rowId=2;
   function Addrow()
   {
    r1=document.getElementById('heart_row').rows.length;
    //alert(r1);
    var a=document.getElementById('heart_row').insertRow(r1);
    var c=a.insertCell(0);
    var d=a.insertCell(1);
    c.setAttribute("align","center");
    d.setAttribute("align","center");
    c.innerHTML='<input type="text" name="link_name[]" id="link_name'+rowId+'" class="text_box"/>';
    d.innerHTML='<a  href="javascript:void(0);" style="text-decoration:none;" onclick="Addrow();" class="add">Add</a>&nbsp&nbsp<a href="javascript:void(0);" class="remove" style="text-decoration:none;" onclick="Removerow(this.parentNode.parentNode);">Remove</a>';
    rowId++;
   }
   function Removerow(dt)
   {
    //alert(i);
    $(dt).fadeOut('slow', function() {
    dt.parentNode.removeChild(dt);
    r1=document.getElementById('heart_row').rows.length;
    if (r1==0)
    {
       Addrow(); 
    } });
   }
</script>