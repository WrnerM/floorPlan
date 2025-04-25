<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/SummaryFloor.php');


$summaryFloor = new SummaryFloor();
$summaryFloor->db = $pdoConn;
$summaryFloor->site = $_GET['site'];
$summaryFloor->dateValid = $_GET['dateValid'];


$response = $summaryFloor->getSummaryFloor();

echo json_encode($response, JSON_PRETTY_PRINT);
