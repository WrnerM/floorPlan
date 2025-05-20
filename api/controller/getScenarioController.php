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
require('../model/Scenarios.php');

$scenario = new Scenario();
$scenario->db = $pdoConn;
$scenario->site = $_GET['site'];
$response = $scenario->getScenario();

$output = [];
$currentFloorId = null;
$currentFloor = null;


foreach ($response as $row) {
  if ($row['FloorId'] !== $currentFloorId) {
    if ($currentFloor !== null) {
      $output[] = $currentFloor;
    }

    $currentFloorId = $row['FloorId'];
    $currentFloor = [
      "id" => $row['FloorId'],
      "siteId" => $row['SiteId'],
      "dateValid" => $row['DateValid'],
      "name" => $row['Name'],
      "comment" => $row['Comment'],
      "scenario" => $row['Scenario'],
      "position" => []
    ];
  }

  $currentFloor['position'][] = [
    "floorId" => $row['FloorId'],
    "row" => $row['Row'],
    "col" => $row['Col'],
    "tool" => $row['Tool'],
    "color" => $row['Color'],
    "tag" => $row['Tag'],
    "label" => $row['Label'],
    "assignId" => $row['AssignId'],
    "project" => $row['Project'],
    "shift" => $row['Shift']
  ];
}

// Add the last floor
if ($currentFloor !== null) {
  $output[] = $currentFloor;
}

// Output JSON
echo json_encode($output, JSON_PRETTY_PRINT);
