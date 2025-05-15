<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Users.php');


if (!isset($_GET['bms_ident']) || empty($_GET['bms_ident'])) {
  echo json_encode([
    'success' => 0,
    'error' => 'Missing required parameter: bms_ident'
  ]);
  exit;
}

$bms_ident = $_GET['bms_ident'];

$user = new Users();
$user->db = $pdoConn;

echo json_encode($user->deleteUser($bms_ident));
