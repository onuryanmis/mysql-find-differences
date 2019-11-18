<?php
session_start();
if (isset($_SESSION['db1Data']) || isset($_SESSION['db2Data'])) {
  header('Location:tables.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>DBScript</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </head>
  <body>
    <div class="container">
      <form id="dbForm">
        <div class="row">
          <div class="col-md-6 db-first">
            <h2>First Database</h2>
              <div class="form-group">
                <label>Host</label>
                <input name="host_1" type="text" class="form-control" placeholder="Enter host...">
              </div>
              <div class="form-group">
                <label>DB User</label>
                <input name="dbuser_1" type="text" class="form-control" placeholder="Enter DB User...">
              </div>
              <div class="form-group">
                <label>Password</label>
                <input name="dbpass_1" type="text" class="form-control" placeholder="Enter DB Password...">
              </div>
              <div class="form-group">
                <label>DB Name</label>
                <input name="dbname_1" type="text" class="form-control" placeholder="Enter DB Name...">
              </div>
          </div>
          <div class="col-md-6 db-second">
            <h2>Second Database</h2>
              <div class="form-group">
                <label>Host</label>
                <input name="host_2" type="text" class="form-control" placeholder="Enter host...">
              </div>
              <div class="form-group">
                <label>DB User</label>
                <input name="dbuser_2" type="text" class="form-control" placeholder="Enter DB User...">
              </div>
              <div class="form-group">
                <label>Password</label>
                <input name="dbpass_2" type="text" class="form-control" placeholder="Enter DB Password...">
              </div>
              <div class="form-group">
                <label>DB Name</label>
                <input name="dbname_2" type="text" class="form-control" placeholder="Enter DB Name...">
              </div>
          </div>
          <button type="button" onclick="$.connect2Db();" class="btn btn-success"><i class="fa fa-check"></i> Connect to Databases</button>
        </div>
      </form>
    </div>
    <script>
      $(function(){
        $.connect2Db = () => {
          $.post('ajax.php',$('#dbForm').serialize() + "&opr=connect",(response)=>{
            if (response.status == 1) {
              swal('Success',response.reply,'success');
              setTimeout(function(){
                window.location.href = 'tables.php';
              },1200);
            }else{
              swal('Error',response.reply,'warning');
            }
          },'json');
        }
      });
    </script>
  </body>
</html>
