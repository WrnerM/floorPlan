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

require('../../config/db-connect.php');
require('../model/Clients.php');


$client = new Client();
$client->db = $pdoConn;
$client->site = $_GET['site'];

$response = $client->getClient();


echo json_encode($response, JSON_PRETTY_PRINT);
