<?php

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');
ini_set('memory_limit', -1);

require('../../config/db-connect.php');
require('../model/Floors.php');
