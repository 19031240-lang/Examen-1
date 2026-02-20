<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\QrController;

header("Content-Type: application/json");

$controller = new QrController();
$controller->handleRequest();