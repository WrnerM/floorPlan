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
require('../model/Decommission.php');

if (!isset($_GET['floor']) || !isset($_GET['site'])) {
  echo json_encode([
    'success' => 0,
    'message' => 'Missing required parameters'
  ]);
  exit;
}

$floor = $_GET['floor'];
$site = $_GET['site'];

$decommission = new Decommission();
$decommission->db = $pdoConn;
$decommission->floor = $floor;
$decommission->site = $site;

$response = $decommission->getDecommission();
echo json_encode($response, JSON_PRETTY_PRINT);
