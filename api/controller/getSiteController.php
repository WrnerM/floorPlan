<?php

// Allow requests from any origin (for development only)
header("Access-Control-Allow-Origin: *");

// Optional: Allow specific headers and methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


session_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Sites.php');

$site = new Site();
$site->db = $pdoConn;
$response = $site->getSite();

$output = [];
$currentLocationId = null;
$currentLocation = null;

foreach ($response as $row) {
  if ($row['short_name'] !== $currentLocationId) {
    if ($currentLocation !== null) {
      $output[] = $currentLocation;
    }

    $currentLocationId = $row['short_name'];
    $currentLocation = [
      "location_ident" => $row['location_ident'],
      "location_name" => $row['location_name'],
      "short_name" => $row['short_name'],
      "floors" => []
    ];
  }

  $currentLocation['floors'][] = [
    "floorID" => $row['floor_id'],
    "floorNumber" => $row['floor_number'],
    "comment" => $row['comment']
  ];
}

// Add the last floor
if ($currentLocation !== null) {
  $output[] = $currentLocation;
}

echo json_encode($output, JSON_PRETTY_PRINT);;
