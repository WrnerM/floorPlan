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

require('../../../config/db-connect.php');
require('../../model/SummaryAccount.php');

$summaryAccount = new SummaryAccount();
$summaryAccount->db = $pdoConn;
$summaryAccount->site = $_GET['site'];
$summaryAccount->dateValid = $_GET['dateValid'] ?? null;

$response = $summaryAccount->getSummaryAccount();

echo json_encode($response, JSON_PRETTY_PRINT);
