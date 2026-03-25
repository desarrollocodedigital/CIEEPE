<?php
// api_proyectos_recientes.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

require_once 'conexion.php';

try {
    // Obtener los 3 proyectos más recientes (basado en ID descendente)
    $stmt = $pdo->query("SELECT id, titulo, descripcion_corta AS resumen, imagen_portada, categoria FROM proyectos ORDER BY id DESC LIMIT 3");
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
