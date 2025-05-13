
<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Clients.php');


$client = new Client();
$client->db = $pdoConn;
$client->location_ident = $_GET['location_ident'];

$response = $client->getClient();


echo json_encode($response, JSON_PRETTY_PRINT);
