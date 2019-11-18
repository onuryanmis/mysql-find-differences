<?php
/**
* Class DBScript
* @author Dogukan Akkaya
* @version 1.0
*/
Class DBScript{
  private $db1='';
  private $db2='';
  public $tablesDb1=[];
  public $tablesDb2=[];
  public $dbName1='';
  public $dbName2='';
  /**
  * We connect to both of our databases
  */
  public function dbConnect($data,$data2){
    $host1 = $data['host'];
    $user1 = $data['dbuser'];
    $pass1 = $data['dbpass'];
    $host2 = $data2['host'];
    $user2 = $data2['dbuser'];
    $pass2 = $data2['dbpass'];
    $this->dbName1 = isset($data['dbname']) ? $data['dbname'] : uniqid(true);
    $this->dbName2 = isset($data2['dbname']) ? $data2['dbname'] : uniqid(true);

    try {
         $this->db1 = new PDO("mysql:host=".$host1.";dbname=".$this->dbName1."", "".$user1."", "".$pass1."");
         $this->db2 = new PDO("mysql:host=".$host2.";dbname=".$this->dbName2."", "".$user2."", "".$pass2."");
         return true;
    } catch (PDOException $e){
         //print $e->getMessage();
         return false;
    }
  }
  /**
  * @param $data
  * @param $data2
  * We find all tables from db1 and db2 and store them in tablesDb1, tablesDb2 variables
  */
  public function findTables(){
    $query = $this->db1->prepare("SHOW TABLES FROM ".$this->dbName1."");
    $query->execute();
    $tables = $query->fetchAll(PDO::FETCH_NUM);
    foreach($tables as $table){
        $this->tablesDb1[] = $table[0];
    }

    // 2. Veritabanindaki tablolari aliyoruz.
    $query = $this->db2->prepare("SHOW TABLES FROM ".$this->dbName2."");
    $query->execute();
    $tables = $query->fetchAll(PDO::FETCH_NUM);

    foreach($tables as $table){
        $this->tablesDb2[] = $table[0];
    }
  }
  /**
  * We look every one of those tables, and we check if first db's table not in seconds,
  * if not we find all differentTables between Db1 and Db2 and than return a html string.
  */
  public function differentTables(){
    $existInTable2NotIn1='';
    $existInTable1NotIn2='';
    foreach ($this->tablesDb1 as $table) {
      if ($this->tableExist($table) != false) {
        $fields = $this->dbColumns($table,1);
        $fields2 = $this->dbColumns($table,2);
        $max = count($fields) > count($fields2) ? count($fields) : count($fields2);
        $different = false;
        for ($i=0; $i < $max; $i++) {
          $field = isset($fields[$i]['Field']) ? $fields[$i]['Field'] : '';
          $field2 = isset($fields2[$i]['Field']) ? $fields2[$i]['Field'] : '';
          if ($field != $field2) {
            $different = true;
          }elseif($fields[$i]['Type'] != $fields2[$i]['Type']){
            $different = true;
          }
          elseif($fields[$i]['Null'] != $fields2[$i]['Null']){
            $different = true;
          }
          elseif($fields[$i]['Key'] != $fields2[$i]['Key']){
            $different = true;
          }
          elseif($fields[$i]['Default'] != $fields2[$i]['Default']){
            $different = true;
          }
          elseif($fields[$i]['Extra'] != $fields2[$i]['Extra']){
            $different = true;
          }
        }
      }
      if (!in_array($table, $this->tablesDb2)){
        $existInTable1NotIn2 .= '<option style="color:red" value="'.$table.'">'.$table.'</option>';
      }elseif($different){
        $existInTable1NotIn2 .= '<option style="color:lightskyblue" value="'.$table.'">'.$table.'</option>';
      }
      else{
        $existInTable1NotIn2 .= '<option value="'.$table.'">'.$table.'</option>';
      }
    }
    foreach ($this->tablesDb2 as $table) {
      if ($this->tableExist($table) != false) {
        $fields = $this->dbColumns($table,1);
        $fields2 = $this->dbColumns($table,2);
        $max = count($fields) > count($fields2) ? count($fields) : count($fields2);
        $different = false;
        for ($i=0; $i < $max; $i++) {
          $field = isset($fields[$i]['Field']) ? $fields[$i]['Field'] : '';
          $field2 = isset($fields2[$i]['Field']) ? $fields2[$i]['Field'] : '';
          if ($field != $field2) {
            $different = true;
          }elseif($fields[$i]['Type'] != $fields2[$i]['Type']){
            $different = true;
          }
          elseif($fields[$i]['Null'] != $fields2[$i]['Null']){
            $different = true;
          }
          elseif($fields[$i]['Key'] != $fields2[$i]['Key']){
            $different = true;
          }
          elseif($fields[$i]['Default'] != $fields2[$i]['Default']){
            $different = true;
          }
          elseif($fields[$i]['Extra'] != $fields2[$i]['Extra']){
            $different = true;
          }
        }
      }
      if (!in_array($table, $this->tablesDb1)){
        $existInTable2NotIn1 .= '<option style="color:red" value="'.$table.'">'.$table.'</option>';
      }elseif($different){
        $existInTable2NotIn1 .= '<option style="color:lightskyblue" value="'.$table.'">'.$table.'</option>';
      }else{
        $existInTable2NotIn1 .= '<option value="'.$table.'">'.$table.'</option>';
      }
    }
    $data['notIn1'] = $existInTable2NotIn1;
    $data['notIn2'] = $existInTable1NotIn2;
    return $data;
  }
  /**
  * @param $table
  * We find every differentColumn in given table and returns a html string
  */
  public function differentColumns($table){
    $html = '';
    $html2 = '';
    $fields = $this->dbColumns($table,1);
    $fields2 = $this->dbColumns($table,2);
    $max = count($fields) > count($fields2) ? count($fields) : count($fields2);
    $different = false;
    if ($this->tableExist($table) == false) {
      exit;
    }
    for ($i=0; $i < $max; $i++) {
      $field = isset($fields[$i]['Field']) ? $fields[$i]['Field'] : '';
      $field2 = isset($fields2[$i]['Field']) ? $fields2[$i]['Field'] : '';
      $error = '';
      $error2 = '';
      if ($field != $field2) {
        $different = true;
        $error .= '<code>Field => '. $field .'</code>';
        $error2 .= '<code>Field => ' . $field2 . '</code>';
      }elseif($fields[$i]['Type'] != $fields2[$i]['Type']){
        $different = true;
        $error .= '<code>Type => '. $fields[$i]['Type'] .'</code>';
        $error2 .= '<code>Type => ' . $fields2[$i]['Type'] . '</code>';
      }
      elseif($fields[$i]['Null'] != $fields2[$i]['Null']){
        $different = true;
        $error .= '<code>NULL => '. $fields[$i]['Null'] .'</code>';
        $error2 .= '<code>NULL => ' . $fields2[$i]['Null'] . '</code> ';
      }
      elseif($fields[$i]['Key'] != $fields2[$i]['Key']){
        $different = true;
        $error .= '<code>Key => '. $fields[$i]['Key'] .'</code>';
        $error2 .= '<code>Key => ' . $fields2[$i]['Key'] . '</code>';
      }
      elseif($fields[$i]['Default'] != $fields2[$i]['Default']){
        $different = true;
        $error .= '<code>Default => '. $fields[$i]['Default'] .'</code>';
        $error2 .= '<code>Default => ' . $fields2[$i]['Default'] . '</code>';
      }
      elseif($fields[$i]['Extra'] != $fields2[$i]['Extra']){
        $different = true;
        $error .= '<code>Extra => '. $fields[$i]['Extra'] .'</code>';
        $error2 .= '<code>Extra => ' . $fields2[$i]['Extra'] . '</code>';
      }
      if (!$different) {
        $html .= '<option>'.$field.'</option>';
        $html2 .= '<option>'.$field2.'</option>';
      }else{
        $html .= '<option style="color:red;" value="'.$field.'">'.$field.' '.$error.'</option>';
        $html2 .= '<option style="color:red;" value="'.$field2.'">'.$field2.' '.$error2.'</option>';
      }
      $different = false;
    }
    $data['html'] = $html;
    $data['html2'] = $html2;
    return $data;
  }
  /**
  * @param $table
  * @param $type
  * @param $column=''
  * We return all the information of columns in the given table
  */
  public function dbColumns($table,$type,$column=''){
    $sql = $column == '' ? 'DESCRIBE '.$table : "SHOW COLUMNS FROM ".$table." LIKE '%".$column."'";
    if ($type == 1) {
      $des = $this->db1->prepare($sql);
    }else{
      $des = $this->db2->prepare($sql);
    }
    $des->execute();
    $table_fields = $des->fetchAll(PDO::FETCH_ASSOC);
    return $table_fields;
  }
  /**
  * @param $sql
  * @param $type
  * We run the given sql by type
  */
  public function runQuery($sql,$type){
    if ($type == 1) {
      $query = $this->db2->query($sql);
    }else{
      $query = $this->db1->query($sql);
    }
    return $query;
  }
  /**
  * @param $table
  * We check if given table exist in both database or not
  */
  public function tableExist($table){
    $exist = $this->db1->query('SELECT 1 FROM '.$table.' LIMIT 1');
    $exist2 = $this->db2->query('SELECT 1 FROM '.$table.' LIMIT 1');
    return ($exist && $exist2) ? true : false;
  }
  /**
  * @param $table
  * @param $col
  * @param $type
  * We check if our column is exist in the opposite of our db, if db1 we look at db2 and if db2 we look at db1
  */
  public function isColumnExist($table,$col,$type){
    if ($type == 1) {
      $des = $this->db2->prepare("SHOW COLUMNS FROM ".$table." LIKE '%".$col."'");
      $des->execute();
      $table_fields = $des->fetchAll(PDO::FETCH_ASSOC);
    }else{
      $des = $this->db1->prepare("SHOW COLUMNS FROM ".$table." LIKE '%".$col."'");
      $des->execute();
      $table_fields = $des->fetchAll(PDO::FETCH_ASSOC);
    }
    return count($table_fields) <= 0 ? false : true;
  }
}
