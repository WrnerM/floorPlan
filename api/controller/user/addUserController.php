<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Users.php');

$input = json_decode(file_get_contents("php://input"), true);

$required = ['bms_ident', 'full_name', 'nt', 'user_email', 'user_role'];
foreach ($required as $field) {
  if (empty($input[$field])) {
    echo json_encode([
      'success' => 0,
      'error' => "Missing required field: $field"
    ]);
    exit;
  }
}

$user = new Users();
$user->db = $pdoConn;

echo json_encode($user->addUser($input));
