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
require('../../model/SummaryFloor.php');


$summaryFloor = new SummaryFloor();
$summaryFloor->db = $pdoConn;

if (isset($_GET['site'])) {
  $summaryFloor->site = $_GET['site'];
  $summaryFloor->dateValid = isset($_GET['dateValid']) ? $_GET['dateValid'] : null;
  $data = $summaryFloor->getSummaryFloor();
  $output = [];
  if (isset($data['error']) == false) {
    if (count($data) > 0) {
      $output = array(
        'overall_total' => [],
        'floors' => []
      );

      foreach ($data as $row) {
        $name = $row['name'];

        if (!isset($output['floors'][$name])) {
          $output['floors'][$name] = [
            'name' => $name,
            'total' => [],
            'details' => []
          ];
        }

        foreach ($row as $key => $value) {
          if ($key === 'name' || $key === 'project') continue;

          if($key == "Shared" || $key == "SharedSeatsNoPC"){
            $value *= 2;
          }

          if (!isset($output['floors'][$name]['total'][$key])) {
            $output['floors'][$name]['total'][$key] = 0;
          }

          $output['floors'][$name]['total'][$key] += (int) $value;

          if (!isset($output['overall_total'][$key])) {
            $output['overall_total'][$key] = 0;
          }

          $output['overall_total'][$key] += (int) $value;
        }

        $row['Shared'] *= 2;
        $row['SharedSeatsNoPC'] *= 2;
        $output['floors'][$name]['details'][] = $row;
      }
    }
  }
  $response = $output;
} else {
  $response = array(
    "data" => [],
    "message" => ["No sites entered"]
  );
}

echo json_encode($response, JSON_PRETTY_PRINT);
