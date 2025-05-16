<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Login.php');

$login = new Login;
$login->db = $pdoConn;

$login->userNT = $_GET['userNT'];
$login->password = $_GET['password'];

$response = $login->login();

echo json_encode($response, JSON_PRETTY_PRINT);


?>