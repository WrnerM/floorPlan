<?php

// Allow requests from any origin (for development only)
header("Access-Control-Allow-Origin: *");

// Optional: Allow specific headers and methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");



session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Floors.php');

// Get the JSON input from the request body
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
  echo json_encode([
    'success' => 0,
    'error' => 'Invalid JSON input'
  ]);
  exit;
}

$floorModel = new Floor();
$floorModel->db = $pdoConn;

$response = $floorModel->addFloor($data);

// Output the response as JSON
echo json_encode($response);
