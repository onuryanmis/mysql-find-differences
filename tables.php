<?php
session_start();
if (!isset($_SESSION['db1Data']) || !isset($_SESSION['db2Data'])) {
  header('Location:index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </head>
  <body>
    <div class="container" style="margin-top:30px;">
      <code>Red colored under First Database Tables means those tables are not exist in Second Database.</code>
      <br>
      <code>Red colored under Second Database Tables means those tables are not exist in First Database.</code>
      <br>
      <code>Blue colored means that table is exist in both of the Databases but this table's columns are different.</code>
      <br>
      <code>Black colored means that table is exist in both of the Databases and there is no difference between them.</code>
      <hr>
      <div class="col-md-6" style="display:inline-block;">
        <h5><?php echo $_SESSION['db1Data']['dbname']; ?> Database Tables</h5>
        <select onchange="$.findTableColumns(this.value);" id="first" multiple>

        </select>
      </div>
      <div class="col-md-6" style="float:right;">
        <h5><?php echo $_SESSION['db2Data']['dbname']; ?> Database Tables</h5>
        <select onchange="$.findTableColumns(this.value);" id="second" multiple>

        </select>
      </div>
      <div class="col-md-12 transfer-btn">
        <button onclick="$.transferTable(1);" type="button" class="btn btn-danger"><i class="fa fa-arrow-right"></i></button>
        <button onclick="$.transferTable(2);" type="button" class="btn btn-danger"><i class="fa fa-arrow-left"></i></button>
      </div>
      <hr>
      <div class="col-md-6" style="display:inline-block;">
        <h5 id="tableNameFirst"></h5>
        <select id="firstColumn" multiple>

        </select>
      </div>
      <div class="col-md-6" style="float:right;">
        <h5 id="tableNameSecond"></h5>
        <select id="secondColumn" multiple>

        </select>
      </div>
      <div class="col-md-12 transfer-btn">
        <button onclick="$.transferColumn(1);" type="button" class="btn btn-danger"><i class="fa fa-arrow-right"></i></button>
        <button onclick="$.transferColumn(2);" type="button" class="btn btn-danger"><i class="fa fa-arrow-left"></i></button>
      </div>
    </div>
    <script>
      $(function(){
        let T='';
        /**
        * We find all of our columns with given table and list them with given html
        */
        $.findTableColumns = (table) => {
          T = table;
          $.post('ajax.php',{'table':table,'opr':'tableColumns'},(response)=>{
            $('#tableNameFirst').text('First Database '+table+' table data');
            $('#tableNameSecond').text('Second Database '+table+' table data');
            $('#firstColumn').html(response.reply);
            $('#secondColumn').html(response.reply2);
          },'json');
        }
        /**
        * We send our column which we wanted to modify or add
        */
        $.transferColumn = (type) => {
          if (confirm('Are you sure you want to modify selected columns?')) {
            let columns = '';
            if (type==1) {
              columns = $('#firstColumn').val();
            }else{
              columns = $('#secondColumn').val();
            }
            $.post('ajax.php',{'table':T,'columns':columns,'opr':'transferCols','type':type},(response)=>{
              if (response.status == 1) {
                $.findTableColumns(T);
                swal('Success','Selected columns are modified successfully','success');
              }else{
                swal('Error','Error while modifying selected columns','warning');
              }
            },'json');
          }
        }
        /**
        * We find all of our tables and list them with given html
        */
        $.findTables = () => {
          $.post('ajax.php',{'opr':'getTables'},(response)=>{
            $('#first').html('');
            $('#second').html('');
            if (response.status == 1) {
              $('#first').html(response.reply2);
              $('#second').html(response.reply1);
            }else{
              swal('Error',response.reply,'warning');
            }
          },'json');
        }
        $.findTables();
        /**
        * We send our table which we wanted to add our other db
        */
        $.transferTable = (type) => {
          if (confirm('Are you sure from this operation?')) {
            let tables = '';
            if (type == 1) {
              tables = $('#first').val();
            }else{
              tables = $('#second').val();
            }
            $.post('ajax.php',{'tables':tables,'opr':'transfer','type':type},(response)=>{
              if (response.status == 1) {
                 $.findTables();
                 swal('Success','Selected tables added to other database successfully','success');
              }else{
                swal('Error','Error while transfer the tables to other database','warning');
              }
            },'json');
          }
        }
      });
    </script>
  </body>
</html>
