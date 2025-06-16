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

require('../../config/db-connect.php');
require('../model/Decomission.php');

// ✅ Validate required parameters
if (!isset($_GET['floor']) || !isset($_GET['site'])) {
  echo json_encode([
    'success' => 0,
    'message' => 'Missing required parameters'
  ]);
  exit; // Stop script execution
}

// Assign parameters
$floor = $_GET['floor'];
$site = $_GET['site'];

$decomission = new Decomission();
$decomission->db = $pdoConn;
$decomission->floor = $floor;
$decomission->site = $site;

// Get data and return
$response = $decomission->getDecomission();
echo json_encode($response, JSON_PRETTY_PRINT);
