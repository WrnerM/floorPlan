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
      $tags = ['Dedicated', 'Shared', 'Blocked', 'Support', 'Non Prod', 'Unassigned'];

      $output = [
          "overall_total" => array_fill_keys(array_merge($tags, ["total_seats"]), 0),
          "floors" => []
      ];
      foreach ($data as $row) {
          $floor = $row['name'];       
          $project = $row['project'];  
          $tag = $row['tag'];        
          $count = (int)$row['count'];

          if (!in_array($tag, $tags)) continue;

          if (!isset($output['floors'][$floor])) {
              $output['floors'][$floor] = [
                  "name" => $floor,
                  "total" => array_fill_keys(array_merge($tags, ["total_seats"]), 0),
                  "details" => []
              ];
          }

          $floorRef = &$output['floors'][$floor];

          $detailIndex = null;
          foreach ($floorRef['details'] as $i => $detail) {
              if ($detail['project'] === $project) {
                  $detailIndex = $i;
                  break;
              }
          }

          if ($detailIndex === null) {
              $detail = array_fill_keys($tags, "0");
              $detail['name'] = $floor;
              $detail['project'] = $project;
              $detail[$tag] = (string)$count;
              $floorRef['details'][] = $detail;
          } else {
              $floorRef['details'][$detailIndex][$tag] = (string)$count;
          }
          $floorRef['total'][$tag] += $count;
          $output['overall_total'][$tag] += $count;
      }
      foreach ($output['floors'] as &$floor) {
          foreach ($floor['details'] as &$detail) {
              $detail['total_seats'] = (string)array_sum(array_map('intval', array_intersect_key($detail, array_flip($tags))));
          }
          $floor['total']['total_seats'] = array_sum(array_intersect_key($floor['total'], array_flip($tags)));
      }

      $output['overall_total']['total_seats'] = array_sum(array_intersect_key($output['overall_total'], array_flip($tags)));
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
