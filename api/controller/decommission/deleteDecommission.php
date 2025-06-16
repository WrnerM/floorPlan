<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require('../../../config/db-connect.php');
require('../../model/Decommission.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || empty($data['id'])) {
  echo json_encode([
    'success' => 0,
    'message' => 'Missing or invalid ID.'
  ]);
  exit;
}

$decommission = new Decommission();
$decommission->db = $pdoConn;

$response = $decommission->deleteDecommission($data['id']);
echo json_encode($response, JSON_PRETTY_PRINT);
