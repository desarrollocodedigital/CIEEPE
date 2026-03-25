<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

try {
    // Obtener las 3 noticias más recientes
    $stmt = $pdo->query("SELECT id, titulo, descripcion_corta AS resumen, imagen_portada, fecha_publicacion FROM noticias ORDER BY id DESC LIMIT 3");
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($noticias, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
