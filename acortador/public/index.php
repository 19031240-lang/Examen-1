<?php
require_once '../config/database.php';


function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$codigo = $_GET['code'] ?? '';

if (empty($codigo)) {
    echo '<h1>Acortador de URLs</h1>';
    echo '<p>API funcionando correctamente</p>';
    echo '<p>Endpoints disponibles:</p>';
    echo '<ul>';
    echo '<li>POST <strong>/api/shorten</strong> - Acortar URL</li>';
    echo '<li>GET <strong>/api/stats/{codigo}</strong> - Ver estadísticas</li>';
    echo '<li>GET <strong>/public/{codigo}</strong> - Usar URL corta</li>';
    echo '</ul>';
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM urls WHERE codigo = ?");
$stmt->execute([$codigo]);
$url = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$url) {
    http_response_code(404);
    echo '<h1> Error 404</h1>';
    echo '<p>URL no encontrada</p>';
    exit;
}


if ($url['fecha_expiracion'] && strtotime($url['fecha_expiracion']) < time()) {
    http_response_code(410);
    echo '<h1>URL Expirada</h1>';
    echo '<p>Esta URL dejo de funcionar el ' . $url['fecha_expiracion'] . '</p>';
    exit;
}


if ($url['max_usos'] && $url['visitas'] >= $url['max_usos']) {
    http_response_code(410);
    echo '<h1>Limite Alcanzado</h1>';
    echo '<p>Esta URL alcanzó su límite de ' . $url['max_usos'] . ' usos</p>';
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO url_registro_accesos (url_id, ip_visitante, navegador) 
    VALUES (?, ?, ?)
");
$stmt->execute([
    $url['id'],
    obtenerIP(),
    $_SERVER['HTTP_USER_AGENT'] ?? ''
]);

$stmt = $pdo->prepare("UPDATE urls SET visitas = visitas + 1 WHERE id = ?");
$stmt->execute([$url['id']]);

header("Location: " . $url['url_original'], true, 302);
exit;
?>