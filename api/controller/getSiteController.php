<?php


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

$result = [];


foreach ($response as $row) {
  $site = $row['site'];
  $floor = $row['floorName'];
  $date = $row['DateValid'];

  if (!isset($result[$site])) {
    $result[$site] = [
      'site' => $site,
      'floorName' => [],
      'dateValid' => []
    ];
  }

  if (!in_array($floor, $result[$site]['floorName'])) {
    $result[$site]['floorName'][] = $floor;
  }

  if (!in_array($date, $result[$site]['dateValid'])) {
    $result[$site]['dateValid'][] = $date;
  }

  // Always add the dateValid (even if floorName is repeated)
  //$result[$site]['dateValid'][] = $date;

  $output = array_values($result);
}

echo json_encode($output, JSON_PRETTY_PRINT);;
