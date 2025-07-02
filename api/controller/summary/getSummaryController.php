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
$summary->dateValid = isset($_GET['dateValid']) ? $_GET['dateValid'] : null;

$data = $summary->getSummary();
$output = [];

$validTags = ['Dedicated', 'Shared', 'Blocked', 'Support', 'Non Prod', 'Unassigned'];

$totals = [];
foreach ($validTags as $tag) {
    $formattedKey = $tag;
    $totals[$formattedKey] = 0;
}

if (isset($data['error']) == false) {
  if (count($data) > 0) {
    foreach ($data as $row) {
      $siteid = $row['siteid'];
      $tag = $row['tag'];
      $formattedKey = $tag;
      $count = (int)$row['count'];
  
      if (!isset($sites[$siteid])) {
          $sites[$siteid] = array_fill_keys(array_keys($totals), 0);
          $sites[$siteid]['siteid'] = $siteid;
      }
  
      $sites[$siteid][$formattedKey] += $count;
      $totals[$formattedKey] += $count;
    }
    
    $totals['total_seats'] = array_sum($totals);
    
    foreach ($sites as &$site) {
        $site['total_seats'] = array_sum(array_intersect_key($site, $totals));
    }

    $output = [
        'total' => $totals,
        'details' => array_values($sites)
    ];
  }

  $response = $output;
} else {
  $response = $data;
}

echo json_encode($response, JSON_PRETTY_PRINT);
