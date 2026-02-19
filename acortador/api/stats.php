<?php
require_once '../config/database.php';

function respuestaJSON($datos, $codigoHTTP = 200) {
    http_response_code($codigoHTTP);
    header('Content-Type: application/json');
    echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$codigo = $_GET['code'] ?? '';

if (empty($codigo)) {
    respuestaJSON([
        'error' => 'Falta el codigo de la URL',
        'code' => 400
    ], 400);
}

$stmt = $pdo->prepare("SELECT * FROM urls WHERE codigo = ?");
$stmt->execute([$codigo]);
$url = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$url) {
    respuestaJSON([
        'error' => 'URL no encontrada',
        'code' => 404
    ], 404);
}

$stmt = $pdo->prepare("
    SELECT * FROM url_registro_accesos 
    WHERE url_id = ? 
    ORDER BY fecha_acceso DESC
");
$stmt->execute([$url['id']]);
$accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$visitasPorDia = [];
foreach ($accesos as $acceso) {
    $fecha = substr($acceso['fecha_acceso'], 0, 10);
    if (!isset($visitasPorDia[$fecha])) {
        $visitasPorDia[$fecha] = 0;
    }
    $visitasPorDia[$fecha]++;
}


$ultimosAccesos = array_slice($accesos, 0, 10);
$ultimosAccesosFormateados = [];

foreach ($ultimosAccesos as $acceso) {
    $ultimosAccesosFormateados[] = [
        'fecha' => $acceso['fecha_acceso'],
        'ip' => $acceso['ip_visitante'],
        'navegador' => $acceso['navegador']
    ];
}

respuestaJSON([
    'codigo' => $url['codigo'],
    'url_original' => $url['url_original'],
    'fecha_creacion' => $url['fecha_creacion'],
    'total_visitas' => $url['visitas'],
    'visitas_por_dia' => $visitasPorDia,
    'ultimos_accesos' => $ultimosAccesosFormateados,
    'fecha_expiracion' => $url['fecha_expiracion'],
    'usos_restantes' => $url['max_usos'] ? $url['max_usos'] - $url['visitas'] : 'ilimitado'
], 200);
?>