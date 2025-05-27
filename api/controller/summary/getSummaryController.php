<?php

// Allow requests from any origin (for development only)
header("Access-Control-Allow-Origin: *");

// Optional: Allow specific headers and methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Summary.php');


$summary = new Summary();
$summary->db = $pdoConn;
// $summary->site = isset($_GET['site']) ? $_GET['site'] : null;
$summary->dateValid = isset($_GET['dateValid']) ? $_GET['dateValid'] : null;

$data = $summary->getSummary();
$output = [];

if (isset($data['error']) == false) {
  if (count($data) > 0) {
    $output = array(
      'total' => [],
      'details' => []
    );
    foreach ($data as $row) {
      foreach ($row as $key => $value) {
        if ($key === 'siteid') continue;
        if (!isset($output['total'][$key])) {
          $output['total'][$key] = 0;
        }
        if($key == "Shared" || $key == "SharedSeatsNoPC"){
          $value *= 2;
        }
        $output['total'][$key] += (int) $value;
      }
      $row['Shared'] *= 2;
      $row['SharedSeatsNoPC'] *= 2;
      $output['details'][] = $row;
    }
  }

  $response = $output;
} else {
  $response = $data;
}

echo json_encode($response, JSON_PRETTY_PRINT);
