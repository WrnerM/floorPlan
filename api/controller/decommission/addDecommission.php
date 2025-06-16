<?php
// Allow requests from any origin (for development only)
header("Access-Control-Allow-Origin: *");

// Optional: Allow specific headers and methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Decommission.php');

$data = json_decode(file_get_contents("php://input"), true);

// Check if data is valid and is an array
if (!is_array($data) || empty($data)) {
  echo json_encode([
    'success' => 0,
    'message' => 'Invalid or empty payload. Expected a JSON array of records.'
  ]);
  exit;
}

$decommission = new Decommission();
$decommission->db = $pdoConn;

$response = $decommission->addDecommission($data);
echo json_encode($response, JSON_PRETTY_PRINT);
