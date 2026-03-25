<?php
// api_investigadores_inicio.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

require_once 'conexion.php';

try {
    // Obtener investigadores para el carrusel de inicio
    $stmt = $pdo->query("SELECT id, nombre, cargo_o_grado AS cargo, imagen_perfil FROM investigadores ORDER BY id ASC");
    $investigadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($investigadores, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
