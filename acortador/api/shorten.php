<?php
require_once '../config/database.php';

function generarCodigo($longitud = 6) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $indice = rand(0, strlen($caracteres) - 1);
        $codigo .= $caracteres[$indice];
    }
    return $codigo;
}

function esUrlValida($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}


function respuestaJSON($datos, $codigoHTTP = 200) {
    http_response_code($codigoHTTP);
    header('Content-Type: application/json');
    echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON([
        'error' => 'Metodo no permitido. Usa POST.',
        'code' => 405
    ], 405);
}


$datos = json_decode(file_get_contents('php://input'), true);
$urlOriginal = $datos['url'] ?? '';
$fechaExpiracion = $datos['fecha_expiracion'] ?? null;
$maxUsos = $datos['max_usos'] ?? null;

if (!esUrlValida($urlOriginal)) {
    respuestaJSON([
        'error' => 'La URL no es válida',
        'code' => 400
    ], 400);
}

do {
    $codigo = generarCodigo(6);
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $existe = $stmt->fetch();
} while ($existe);

$stmt = $pdo->prepare("
    INSERT INTO urls (codigo, url_original, ip_creador, fecha_expiracion, max_usos) 
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $codigo,
    $urlOriginal,
    obtenerIP(),
    $fechaExpiracion,
    $maxUsos
]);

$urlCorta = 'http://localhost/acortador/public/' . $codigo;

respuestaJSON([
    'url_corta' => $urlCorta,
    'codigo' => $codigo,
    'mensaje' => 'URL acortada exitosamente',
    'fecha_expiracion' => $fechaExpiracion,
    'max_usos' => $maxUsos
], 201);
?>