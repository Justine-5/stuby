<?php
  $server="localhost";
  $user="u722325359_stuby";
  $pass="StubyBuddy1";
  $dbname="u722325359_stuby";
  date_default_timezone_set('Asia/Manila');
  $conn = new mysqli($server,$user,$pass,$dbname);
  if($conn->connect_error){
    die('Connection Failed:'.$conn->connect_error);
  }
?>
