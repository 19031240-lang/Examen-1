<?php

require_once "controllers/PasswordController.php";

$controller = new PasswordController();

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if ($method === 'GET' && strpos($uri, '/api/password') !== false) {
    $controller->generatePassword();
}

elseif ($method === 'POST' && strpos($uri, '/api/passwords') !== false) {
    $controller->generateMultiplePasswords();
}

elseif ($method === 'POST' && strpos($uri, '/api/password/validate') !== false) {
    $controller->validatePassword();
}

else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}