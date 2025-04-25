<?php
require('mssql-config.php');
date_default_timezone_set('Asia/Manila');

try {

  $pdoConn = new PDO('sqlsrv:Server=' . HOST . ';Database=' . DATABASE, USER, PASSWORD);
  $pdoConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException  $e) {
  echo "Connection failed: " . $e->getMessage();
}
