<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Floors.php');

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
  echo json_encode([
    'success' => 0,
    'error' => 'Invalid JSON input'
  ]);
  exit;
}

$floorModel = new Floor();
$floorModel->db = $pdoConn;

$response = $floorModel->editFloor($data);

echo json_encode($response);
