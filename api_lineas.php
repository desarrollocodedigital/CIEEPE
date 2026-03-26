<?php
// api_lineas.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

require_once 'conexion.php';

try {
    $stmt = $pdo->query("SELECT id, titulo, descripcion, icono, color FROM lineas_investigacion ORDER BY id ASC");
    $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapear los nombres de columnas a las propiedades que espera el JS en index.html
    $response = array_map(function($l) {
        $color = $l['color'] ?: 'blue'; // Prevención por si está vacío
        
        return [
            'id' => $l['id'],
            'title' => $l['titulo'],
            'description' => $l['descripcion'],
            'icon' => $l['icono'] ?: 'book', // Default if empty
            'color' => "bg-{$color}-50",
            'iconColor' => "text-{$color}-600",
            'link' => "detalle_linea.php?id=" . $l['id']
        ];
    }, $lineas);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
