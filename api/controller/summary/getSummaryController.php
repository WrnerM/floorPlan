<?php

session_start();
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

if(isset($data['error']) == false){
    if(count($data) > 0){
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
                $output['total'][$key] += (int) $value;
            }
        
            $output['details'][] = $row;
        }
    }

    $response = $output;
    
}else{
    $response = $data;
}

echo json_encode($response, JSON_PRETTY_PRINT);
