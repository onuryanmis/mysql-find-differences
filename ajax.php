<?php
session_start();
include 'db.php';
$dbScript = new DBScript();
/**
* DBConnection
* We get our host,user,pass,dbname post values and then we storage them in session and redirect to tables.php
*/
if ($_POST['opr'] == 'connect') {
  $host = isset($_POST['host_1']) ? trim($_POST['host_1']) : 'localhost';
  $dbuser = isset($_POST['dbuser_1']) ? trim($_POST['dbuser_1']) : '';
  $dbpass = isset($_POST['dbpass_1']) ? trim($_POST['dbpass_1']) : '';
  $dbname = isset($_POST['dbname_1']) ? trim($_POST['dbname_1']) : '';
  if (empty($dbuser) || empty($dbname)) {
    $json['status'] = 0;
    $json['reply'] = 'You must enter dbuser and dbname.';
    echo json_encode($json);
    exit;
  }
  $_SESSION['db1Data'] = [
    'host'=>$host,
    'dbuser'=>$dbuser,
    'dbpass'=>$dbpass,
    'dbname'=>$dbname
  ];
  $host = isset($_POST['host_2']) ? trim($_POST['host_2']) : 'localhost';
  $dbuser = isset($_POST['dbuser_2']) ? trim($_POST['dbuser_2']) : '';
  $dbpass = isset($_POST['dbpass_2']) ? trim($_POST['dbpass_2']) : '';
  $dbname = isset($_POST['dbname_2']) ? trim($_POST['dbname_2']) : '';
  if (empty($dbuser) || empty($dbname)) {
    $json['status'] = 0;
    $json['reply'] = 'You must enter dbuser and dbname.';
    echo json_encode($json);
    exit;
  }
  $_SESSION['db2Data'] = [
    'host'=>$host,
    'dbuser'=>$dbuser,
    'dbpass'=>$dbpass,
    'dbname'=>$dbname
  ];
  $connection = $dbScript->dbConnect($_SESSION['db1Data'],$_SESSION['db2Data']);
  if (!$connection) {
    $json['status'] = 0;
    $json['reply'] = 'Error while trying to connect to database';
    session_destroy();
    echo json_encode($json);
  }else{
    $json['status'] = 1;
    $json['reply'] = 'Connected successfully. Please wait redirecting...';
    echo json_encode($json);
  }
}
/**
* If DB Session ended, than send a message
*/
if (!isset($_SESSION['db1Data']) || !isset($_SESSION['db2Data'])) {
  $json['status'] = 0;
  $json['reply'] = 'Database connection timed out. Please connect again.';
  session_destroy();
  echo json_encode($json);
  exit;
}
/**
* get all tables one by one from both of the tables
* and then in differentTables function it returns an html string with different tables or different table columns
*/
if ($_POST['opr'] == 'getTables') {
  $connection = $dbScript->dbConnect($_SESSION['db1Data'],$_SESSION['db2Data']);
  if (!$connection) {
    $json['status'] = 0;
    $json['reply'] = 'Error while trying to connect to database';
    echo json_encode($json);
    exit;
  }
  $dbScript->findTables();
  $differences = $dbScript->differentTables();
  $json['status'] = 1;
  $json['reply1'] = $differences['notIn1'];
  $json['reply2'] = $differences['notIn2'];
  echo json_encode($json);
}
/**
* on click one of the tables, returns it's columns to modify them
*/
if ($_POST['opr'] == 'tableColumns') {
  $connection = $dbScript->dbConnect($_SESSION['db1Data'],$_SESSION['db2Data']);
  if (!$connection) {
    $json['status'] = 0;
    $json['reply'] = 'Error while trying to connect to database';
    echo json_encode($json);
    exit;
  }
  if (!isset($_POST['table'])) {
    $json['status'] = 0;
    $json['reply'] = 'You must select a table';
    echo json_encode($json);
    exit;
  }
  $data = $dbScript->differentColumns($_POST['table']);
  $json['reply'] = $data['html'];
  $json['reply2'] = $data['html2'];
  echo json_encode($json);
}
/**
* Transfer tables from db1 to db2 or db2 to db1
* Find all columns, and datatypes or other properties and make a sql query, then run query.
*/
if($_POST['opr'] == 'transfer'  && isset($_POST['tables'])){
  $dbScript->dbConnect($_SESSION['db1Data'],$_SESSION['db2Data']);
  $sql = '';
  foreach ($_POST['tables'] as $table) {
    $fields = $dbScript->dbColumns($table,$_POST['type']);
    $sql .= 'DROP TABLE IF EXISTS '.$table.'; ';
    $sql .= 'CREATE TABLE ' . $table . '(';
    foreach ($fields as $field) {
      $extra = !empty($field['Extra']) ? $field['Extra'] : '';
      $key = !empty($field['Key']) ? $field['Key'] : '';
      $key = $key == 'PRI' ? 'PRIMARY KEY' : ($key == 'UNI' ? 'UNIQUE KEY' : ($key == 'MUL' ? ',key({field})' : ''));
      $null = $field['Null'] == 'NO' ? 'NOT NULL' : 'NULL';
      $default = isset($field['Default']) ? 'DEFAULT '.$field['Default'] : '';
      $sql .= $field['Field'] . ' ' . $field['Type'] . ' ' . $null . ' ' . $default .' ' . $extra . ' ' . $key . ',';
      $sql = str_replace('{field}',$field['Field'],$sql);
    }
    $sql = substr($sql,0,-1);
    $sql .= ') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';
  }
  if ($dbScript->runQuery($sql,$_POST['type'])) {
    $json['status'] = 1;
  }else{
    $json['status'] = 0;
  }
  echo json_encode($json);
}
/**
* Transfer columns from table1 to table2 or table2 to table1
* Find all different columns and differences than make a sql query, then run query.
*/
if($_POST['opr'] == 'transferCols'  && isset($_POST['columns']) && isset($_POST['table'])){
  $dbScript->dbConnect($_SESSION['db1Data'],$_SESSION['db2Data']);
  $sql = 'ALTER TABLE ' . $_POST['table'];
  foreach ($_POST['columns'] as $col) {
    $fields = $dbScript->dbColumns($_POST['table'],$_POST['type'],$col);
    foreach ($fields as $field) {
      $extra = !empty($field['Extra']) ? $field['Extra'] : '';
      $key = !empty($field['Key']) ? $field['Key'] : '';
      $key = $key == 'PRI' ? 'PRIMARY KEY' : ($key == 'UNI' ? 'UNIQUE KEY' : ($key == 'MUL' ? ', ADD KEY({field})' : ''));
      $null = $field['Null'] == 'NO' ? 'NOT NULL' : 'NULL';
      $default = isset($field['Default']) ? 'DEFAULT '.$field['Default'] : '';
      if (!$dbScript->isColumnExist($_POST['table'],$col,$_POST['type'])) {
        $sql .= ' ADD COLUMN ' . $col . ' ' . $field['Type'] . ' ' . $null . ' ' . $default . ' ' . $extra . ' ' . $key;
      }else{
        $sql .= ' MODIFY COLUMN ' . $col . ' ' . $field['Type'] . ' ' . $null . ' ' . $default . ' ' . $extra . ' ' . $key;
      }
      $sql = str_replace('{field}',$field['Field'],$sql) . ',';
    }
  }
  $sql = substr($sql,0,-1);
  if ($dbScript->runQuery($sql,$_POST['type'])) {
    $json['status'] = 1;
  }else{
    $json['status'] = 0;
  }
  echo json_encode($json);
}
