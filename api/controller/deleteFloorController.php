<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Floors.php');

$floor = new Floor();
$floor->db = $pdoConn;

if (!isset($_GET['FloorId']) || !is_numeric($_GET['FloorId'])) {
  echo json_encode(['success' => 0, 'error' => 'Missing or invalid FloorId']);
  exit;
}

$floor->floorId = $_GET['FloorId'];
$response = $floor->deleteFloor();

echo json_encode($response, JSON_PRETTY_PRINT);
