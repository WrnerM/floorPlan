<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('content-type: application/json');
ini_set('memory_limit', -1);

require('../../../config/db-connect.php');
require('../../model/Users.php');

$user = new Users();
$user->db = $pdoConn;

$user->db = $pdoConn;
$response = $user->getUsers();

echo json_encode($response, JSON_PRETTY_PRINT);
